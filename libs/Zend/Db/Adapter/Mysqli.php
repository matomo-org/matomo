<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Mysqli.php 16203 2009-06-21 18:56:17Z thomas $
 */


/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';

/**
 * @see Zend_Db_Profiler
 */
require_once 'Zend/Db/Profiler.php';

/**
 * @see Zend_Db_Select
 */
require_once 'Zend/Db/Select.php';

/**
 * @see Zend_Db_Statement_Mysqli
 */
require_once 'Zend/Db/Statement/Mysqli.php';


/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Mysqli extends Zend_Db_Adapter_Abstract
{

    /**
     * Keys are UPPERCASE SQL datatypes or the constants
     * Zend_Db::INT_TYPE, Zend_Db::BIGINT_TYPE, or Zend_Db::FLOAT_TYPE.
     *
     * Values are:
     * 0 = 32-bit integer
     * 1 = 64-bit integer
     * 2 = float or decimal
     *
     * @var array Associative array of datatypes to values 0, 1, or 2.
     */
    protected $_numericDataTypes = array(
        Zend_Db::INT_TYPE    => Zend_Db::INT_TYPE,
        Zend_Db::BIGINT_TYPE => Zend_Db::BIGINT_TYPE,
        Zend_Db::FLOAT_TYPE  => Zend_Db::FLOAT_TYPE,
        'INT'                => Zend_Db::INT_TYPE,
        'INTEGER'            => Zend_Db::INT_TYPE,
        'MEDIUMINT'          => Zend_Db::INT_TYPE,
        'SMALLINT'           => Zend_Db::INT_TYPE,
        'TINYINT'            => Zend_Db::INT_TYPE,
        'BIGINT'             => Zend_Db::BIGINT_TYPE,
        'SERIAL'             => Zend_Db::BIGINT_TYPE,
        'DEC'                => Zend_Db::FLOAT_TYPE,
        'DECIMAL'            => Zend_Db::FLOAT_TYPE,
        'DOUBLE'             => Zend_Db::FLOAT_TYPE,
        'DOUBLE PRECISION'   => Zend_Db::FLOAT_TYPE,
        'FIXED'              => Zend_Db::FLOAT_TYPE,
        'FLOAT'              => Zend_Db::FLOAT_TYPE
    );

    /**
     * @var Zend_Db_Statement_Mysqli
     */
    protected $_stmt = null;

    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_defaultStmtClass = 'Zend_Db_Statement_Mysqli';

    /**
     * Quote a raw string.
     *
     * @param mixed $value Raw string
     *
     * @return string           Quoted string
     */
    protected function _quote($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        $this->_connect();
        return "'" . $this->_connection->real_escape_string($value) . "'";
    }

    /**
     * Returns the symbol the adapter uses for delimiting identifiers.
     *
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        return "`";
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        $result = array();
        // Use mysqli extension API, because SHOW doesn't work
        // well as a prepared statement on MySQL 4.1.
        $sql = 'SHOW TABLES';
        if ($queryResult = $this->getConnection()->query($sql)) {
            while ($row = $queryResult->fetch_row()) {
                $result[] = $row[0];
            }
            $queryResult->close();
        } else {
            /**
             * @see Zend_Db_Adapter_Mysqli_Exception
             */
            require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
            throw new Zend_Db_Adapter_Mysqli_Exception($this->getConnection()->error);
        }
        return $result;
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        /**
         * @todo  use INFORMATION_SCHEMA someday when
         * MySQL's implementation isn't too slow.
         */

        if ($schemaName) {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier("$schemaName.$tableName", true);
        } else {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier($tableName, true);
        }

        /**
         * Use mysqli extension API, because DESCRIBE doesn't work
         * well as a prepared statement on MySQL 4.1.
         */
        if ($queryResult = $this->getConnection()->query($sql)) {
            while ($row = $queryResult->fetch_assoc()) {
                $result[] = $row;
            }
            $queryResult->close();
        } else {
            /**
             * @see Zend_Db_Adapter_Mysqli_Exception
             */
            require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
            throw new Zend_Db_Adapter_Mysqli_Exception($this->getConnection()->error);
        }

        $desc = array();

        $row_defaults = array(
            'Length'          => null,
            'Scale'           => null,
            'Precision'       => null,
            'Unsigned'        => null,
            'Primary'         => false,
            'PrimaryPosition' => null,
            'Identity'        => false
        );
        $i = 1;
        $p = 1;
        foreach ($result as $key => $row) {
            $row = array_merge($row_defaults, $row);
            if (preg_match('/unsigned/', $row['Type'])) {
                $row['Unsigned'] = true;
            }
            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = $matches[1];
                $row['Length'] = $matches[2];
            } else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'decimal';
                $row['Precision'] = $matches[1];
                $row['Scale'] = $matches[2];
            } else if (preg_match('/^float\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'float';
                $row['Precision'] = $matches[1];
                $row['Scale'] = $matches[2];
            } else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = $matches[1];
                /**
                 * The optional argument of a MySQL int type is not precision
                 * or length; it is only a hint for display width.
                 */
            }
            if (strtoupper($row['Key']) == 'PRI') {
                $row['Primary'] = true;
                $row['PrimaryPosition'] = $p;
                if ($row['Extra'] == 'auto_increment') {
                    $row['Identity'] = true;
                } else {
                    $row['Identity'] = false;
                }
                ++$p;
            }
            $desc[$this->foldCase($row['Field'])] = array(
                'SCHEMA_NAME'      => null, // @todo
                'TABLE_NAME'       => $this->foldCase($tableName),
                'COLUMN_NAME'      => $this->foldCase($row['Field']),
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'        => $row['Type'],
                'DEFAULT'          => $row['Default'],
                'NULLABLE'         => (bool) ($row['Null'] == 'YES'),
                'LENGTH'           => $row['Length'],
                'SCALE'            => $row['Scale'],
                'PRECISION'        => $row['Precision'],
                'UNSIGNED'         => $row['Unsigned'],
                'PRIMARY'          => $row['Primary'],
                'PRIMARY_POSITION' => $row['PrimaryPosition'],
                'IDENTITY'         => $row['Identity']
            );
            ++$i;
        }
        return $desc;
    }

    /**
     * Creates a connection to the database.
     *
     * @return void
     * @throws Zend_Db_Adapter_Mysqli_Exception
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }

        if (!extension_loaded('mysqli')) {
            /**
             * @see Zend_Db_Adapter_Mysqli_Exception
             */
            require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
            throw new Zend_Db_Adapter_Mysqli_Exception('The Mysqli extension is required for this adapter but the extension is not loaded');
        }

        if (isset($this->_config['port'])) {
            $port = (integer) $this->_config['port'];
        } else {
            $port = null;
        }

        $this->_connection = mysqli_init();

        if(!empty($this->_config['driver_options'])) {
            foreach($this->_config['driver_options'] as $option=>$value) {
                if(is_string($option)) {
                    // Suppress warnings here
                    // Ignore it if it's not a valid constant
                    $option = @constant(strtoupper($option));
                    if($option === null)
                        continue;
                }
                mysqli_options($this->_connection, $option, $value);
            }
        }

        // Suppress connection warnings here.
        // Throw an exception instead.
        $_isConnected = @mysqli_real_connect(
            $this->_connection,
            $this->_config['host'],
            $this->_config['username'],
            $this->_config['password'],
            $this->_config['dbname'],
            $port
        );

        if ($_isConnected === false || mysqli_connect_errno()) {

            $this->closeConnection();
            /**
             * @see Zend_Db_Adapter_Mysqli_Exception
             */
            require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
            throw new Zend_Db_Adapter_Mysqli_Exception(mysqli_connect_error());
        }

        if (!empty($this->_config['charset'])) {
            mysqli_set_charset($this->_connection, $this->_config['charset']);
        }
    }

    /**
     * Test if a connection is active
     *
     * @return boolean
     */
    public function isConnected()
    {
        return ((bool) ($this->_connection instanceof mysqli));
    }

    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection()
    {
        if ($this->isConnected()) {
            $this->_connection->close();
        }
        $this->_connection = null;
    }

    /**
     * Prepare a statement and return a PDOStatement-like object.
     *
     * @param  string  $sql  SQL query
     * @return Zend_Db_Statement_Mysqli
     */
    public function prepare($sql)
    {
        $this->_connect();
        if ($this->_stmt) {
            $this->_stmt->close();
        }
        $stmtClass = $this->_defaultStmtClass;
        if (!class_exists($stmtClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($stmtClass);
        }
        $stmt = new $stmtClass($this, $sql);
        if ($stmt === false) {
            return false;
        }
        $stmt->setFetchMode($this->_fetchMode);
        $this->_stmt = $stmt;
        return $stmt;
    }

    /**
     * Gets the last ID generated automatically by an IDENTITY/AUTOINCREMENT column.
     *
     * As a convention, on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2), this method forms the name of a sequence
     * from the arguments and returns the last id generated by that sequence.
     * On RDBMS brands that support IDENTITY/AUTOINCREMENT columns, this method
     * returns the last value generated for such a column, and the table name
     * argument is disregarded.
     *
     * MySQL does not support sequences, so $tableName and $primaryKey are ignored.
     *
     * @param string $tableName   OPTIONAL Name of table.
     * @param string $primaryKey  OPTIONAL Name of primary key column.
     * @return string
     * @todo Return value should be int?
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        $mysqli = $this->_connection;
        return (string) $mysqli->insert_id;
    }

    /**
     * Begin a transaction.
     *
     * @return void
     */
    protected function _beginTransaction()
    {
        $this->_connect();
        $this->_connection->autocommit(false);
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    protected function _commit()
    {
        $this->_connect();
        $this->_connection->commit();
        $this->_connection->autocommit(true);
    }

    /**
     * Roll-back a transaction.
     *
     * @return void
     */
    protected function _rollBack()
    {
        $this->_connect();
        $this->_connection->rollback();
        $this->_connection->autocommit(true);
    }

    /**
     * Set the fetch mode.
     *
     * @param int $mode
     * @return void
     * @throws Zend_Db_Adapter_Mysqli_Exception
     */
    public function setFetchMode($mode)
    {
        switch ($mode) {
            case Zend_Db::FETCH_LAZY:
            case Zend_Db::FETCH_ASSOC:
            case Zend_Db::FETCH_NUM:
            case Zend_Db::FETCH_BOTH:
            case Zend_Db::FETCH_NAMED:
            case Zend_Db::FETCH_OBJ:
                $this->_fetchMode = $mode;
                break;
            case Zend_Db::FETCH_BOUND: // bound to PHP variable
                /**
                 * @see Zend_Db_Adapter_Mysqli_Exception
                 */
                require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
                throw new Zend_Db_Adapter_Mysqli_Exception('FETCH_BOUND is not supported yet');
                break;
            default:
                /**
                 * @see Zend_Db_Adapter_Mysqli_Exception
                 */
                require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
                throw new Zend_Db_Adapter_Mysqli_Exception("Invalid fetch mode '$mode' specified");
        }
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param int $count
     * @param int $offset OPTIONAL
     * @return string
     */
    public function limit($sql, $count, $offset = 0)
    {
        $count = intval($count);
        if ($count <= 0) {
            /**
             * @see Zend_Db_Adapter_Mysqli_Exception
             */
            require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
            throw new Zend_Db_Adapter_Mysqli_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            /**
             * @see Zend_Db_Adapter_Mysqli_Exception
             */
            require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
            throw new Zend_Db_Adapter_Mysqli_Exception("LIMIT argument offset=$offset is not valid");
        }

        $sql .= " LIMIT $count";
        if ($offset > 0) {
            $sql .= " OFFSET $offset";
        }

        return $sql;
    }

    /**
     * Check if the adapter supports real SQL parameters.
     *
     * @param string $type 'positional' or 'named'
     * @return bool
     */
    public function supportsParameters($type)
    {
        switch ($type) {
            case 'positional':
                return true;
            case 'named':
            default:
                return false;
        }
    }

    /**
     * Retrieve server version in PHP style
     *
     *@return string
     */
    public function getServerVersion()
    {
        $this->_connect();
        $version = $this->_connection->server_version;
        $major = (int) ($version / 10000);
        $minor = (int) ($version % 10000 / 100);
        $revision = (int) ($version % 100);
        return $major . '.' . $minor . '.' . $revision;
    }
}
