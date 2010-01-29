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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Sqlsrv.php 20630 2010-01-25 21:18:20Z ralph $
 */

/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';

/**
 * @see Zend_Db_Statement_Sqlsrv
 */
require_once 'Zend/Db/Statement/Sqlsrv.php';

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Sqlsrv extends Zend_Db_Adapter_Abstract
{
    /**
     * User-provided configuration.
     *
     * Basic keys are:
     *
     * username => (string) Connect to the database as this username.
     * password => (string) Password associated with the username.
     * dbname   => The name of the local SQL Server instance
     *
     * @var array
     */
    protected $_config = array(
        'dbname'       => null,
        'username'     => null,
        'password'     => null,
    );

    /**
     * Last insert id from INSERT query
     *
     * @var int
     */
    protected $_lastInsertId;

    /**
     * Query used to fetch last insert id
     *
     * @var string
     */
    protected $_lastInsertSQL = 'SELECT SCOPE_IDENTITY() as Current_Identity';

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
        'SMALLINT'           => Zend_Db::INT_TYPE,
        'TINYINT'            => Zend_Db::INT_TYPE,
        'BIGINT'             => Zend_Db::BIGINT_TYPE,
        'DECIMAL'            => Zend_Db::FLOAT_TYPE,
        'FLOAT'              => Zend_Db::FLOAT_TYPE,
        'MONEY'              => Zend_Db::FLOAT_TYPE,
        'NUMERIC'            => Zend_Db::FLOAT_TYPE,
        'REAL'               => Zend_Db::FLOAT_TYPE,
        'SMALLMONEY'         => Zend_Db::FLOAT_TYPE,
    );

    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_defaultStmtClass = 'Zend_Db_Statement_Sqlsrv';

    /**
     * Creates a connection resource.
     *
     * @return void
     * @throws Zend_Db_Adapter_Sqlsrv_Exception
     */
    protected function _connect()
    {
        if (is_resource($this->_connection)) {
            // connection already exists
            return;
        }

        if (!extension_loaded('sqlsrv')) {
            /**
             * @see Zend_Db_Adapter_Sqlsrv_Exception
             */
            require_once 'Zend/Db/Adapter/Sqlsrv/Exception.php';
            throw new Zend_Db_Adapter_Sqlsrv_Exception('The Sqlsrv extension is required for this adapter but the extension is not loaded');
        }

        $serverName = $this->_config['host'];
        if (isset($this->_config['port'])) {
            $port        = (integer) $this->_config['port'];
            $serverName .= ', ' . $port;
        }

        $connectionInfo = array(
            'Database' => $this->_config['dbname'],
        );

        if (isset($this->_config['username']) && isset($this->_config['password']))
        {
            $connectionInfo += array(
                'UID'      => $this->_config['username'],
                'PWD'      => $this->_config['password'],
            );
        }
        // else - windows authentication

        if (!empty($this->_config['driver_options'])) {
            foreach ($this->_config['driver_options'] as $option => $value) {
                // A value may be a constant.
                if (is_string($value)) {
                    $constantName = strtoupper($value);
                    if (defined($constantName)) {
                        $connectionInfo[$option] = constant($constantName);
                    } else {
                        $connectionInfo[$option] = $value;
                    }
                }
            }
        }

        $this->_connection = sqlsrv_connect($serverName, $connectionInfo);

        if (!$this->_connection) {
            /**
             * @see Zend_Db_Adapter_Sqlsrv_Exception
             */
            require_once 'Zend/Db/Adapter/Sqlsrv/Exception.php';
            throw new Zend_Db_Adapter_Sqlsrv_Exception(sqlsrv_errors());
        }
    }

    /**
     * Check for config options that are mandatory.
     * Throw exceptions if any are missing.
     *
     * @param array $config
     * @throws Zend_Db_Adapter_Exception
     */
    protected function _checkRequiredOptions(array $config)
    {
        // we need at least a dbname
        if (! array_key_exists('dbname', $config)) {
            /** @see Zend_Db_Adapter_Exception */
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'dbname' that names the database instance");
        }

        if (! array_key_exists('password', $config) && array_key_exists('username', $config)) {
            /**
             * @see Zend_Db_Adapter_Exception
             */
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'password' for login credentials.
                                                If Windows Authentication is desired, both keys 'username' and 'password' should be ommited from config.");
        }

        if (array_key_exists('password', $config) && !array_key_exists('username', $config)) {
            /**
             * @see Zend_Db_Adapter_Exception
             */
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'username' for login credentials.
                                                If Windows Authentication is desired, both keys 'username' and 'password' should be ommited from config.");
        }
    }

    /**
     * Set the transaction isoltion level.
     *
     * @param integer|null $level A fetch mode from SQLSRV_TXN_*.
     * @return true
     * @throws Zend_Db_Adapter_Sqlsrv_Exception
     */
    public function setTransactionIsolationLevel($level = null)
    {
        $this->_connect();
        $sql = null;

        // Default transaction level in sql server
        if ($level === null)
        {
            $level = SQLSRV_TXN_READ_COMMITTED;
        }

        switch ($level) {
            case SQLSRV_TXN_READ_UNCOMMITTED:
                $sql = "READ UNCOMMITTED";
                break;
            case SQLSRV_TXN_READ_COMMITTED:
                $sql = "READ COMMITTED";
                break;
            case SQLSRV_TXN_REPEATABLE_READ:
                $sql = "REPEATABLE READ";
                break;
            case SQLSRV_TXN_SNAPSHOT:
                $sql = "SNAPSHOT";
                break;
            case SQLSRV_TXN_SERIALIZABLE:
                $sql = "SERIALIZABLE";
                break;
            default:
                require_once 'Zend/Db/Adapter/Sqlsrv/Exception.php';
                throw new Zend_Db_Adapter_Sqlsrv_Exception("Invalid transaction isolation level mode '$level' specified");
        }

        if (!sqlsrv_query($this->_connection, "SET TRANSACTION ISOLATION LEVEL $sql;")) {
            require_once 'Zend/Db/Adapter/Sqlsrv/Exception.php';
            throw new Zend_Db_Adapter_Sqlsrv_Exception("Transaction cannot be changed to '$level'");
        }

        return true;
    }

    /**
     * Test if a connection is active
     *
     * @return boolean
     */
    public function isConnected()
    {
        return (is_resource($this->_connection)
                && (get_resource_type($this->_connection) == 'SQL Server Connection')
        );
    }

    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection()
    {
        if ($this->isConnected()) {
            sqlsrv_close($this->_connection);
        }
        $this->_connection = null;
    }

    /**
     * Returns an SQL statement for preparation.
     *
     * @param string $sql The SQL statement with placeholders.
     * @return Zend_Db_Statement_Sqlsrv
     */
    public function prepare($sql)
    {
        $this->_connect();
        $stmtClass = $this->_defaultStmtClass;

        if (!class_exists($stmtClass)) {
            /**
             * @see Zend_Loader
             */
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($stmtClass);
        }

        $stmt = new $stmtClass($this, $sql);
        $stmt->setFetchMode($this->_fetchMode);
        return $stmt;
    }

    /**
     * Quote a raw string.
     *
     * @param string $value     Raw string
     * @return string           Quoted string
     */
    protected function _quote($value)
    {
        if (is_int($value)) {
            return $value;
        } elseif (is_float($value)) {
            return sprintf('%F', $value);
        }

        return "'" . str_replace("'", "''", $value) . "'";
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
     * @param string $tableName   OPTIONAL Name of table.
     * @param string $primaryKey  OPTIONAL Name of primary key column.
     * @return string
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        if ($tableName) {
            $tableName = $this->quote($tableName);
            $sql       = 'SELECT IDENT_CURRENT (' . $tableName . ') as Current_Identity';
            return (string) $this->fetchOne($sql);
        }

        if ($this->_lastInsertId > 0) {
            return (string) $this->_lastInsertId;
        }

        $sql = $this->_lastInsertSQL;
        return (string) $this->fetchOne($sql);
    }

    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind)
    {
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col, true);
            if ($val instanceof Zend_Db_Expr) {
                $vals[] = $val->__toString();
                unset($bind[$col]);
            } else {
                $vals[] = '?';
            }
        }

        // build the statement
        $sql = "INSERT INTO "
             . $this->quoteIdentifier($table, true)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')'
             . ' ' . $this->_lastInsertSQL;

        // execute the statement and return the number of affected rows
        $stmt   = $this->query($sql, array_values($bind));
        $result = $stmt->rowCount();

        $stmt->nextRowset();

        $this->_lastInsertId = $stmt->fetchColumn();

        return $result;
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        $this->_connect();
        $sql = "SELECT name FROM sysobjects WHERE type = 'U' ORDER BY name";
        return $this->fetchCol($sql);
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
     * SCHEMA_NAME      => string; name of schema
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
     * @todo Discover integer unsigned property.
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        /**
         * Discover metadata information about this table.
         */
        $sql    = "exec sp_columns @table_name = " . $this->quoteIdentifier($tableName, true);
        $stmt   = $this->query($sql);
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $owner           = 1;
        $table_name      = 2;
        $column_name     = 3;
        $type_name       = 5;
        $precision       = 6;
        $length          = 7;
        $scale           = 8;
        $nullable        = 10;
        $column_def      = 12;
        $column_position = 16;

        /**
         * Discover primary key column(s) for this table.
         */
        $tableOwner = $result[0][$owner];
        $sql        = "exec sp_pkeys @table_owner = " . $tableOwner
                    . ", @table_name = " . $this->quoteIdentifier($tableName, true);
        $stmt       = $this->query($sql);

        $primaryKeysResult = $stmt->fetchAll(Zend_Db::FETCH_NUM);
        $primaryKeyColumn  = array();

        // Per http://msdn.microsoft.com/en-us/library/ms189813.aspx,
        // results from sp_keys stored procedure are:
        // 0=TABLE_QUALIFIER 1=TABLE_OWNER 2=TABLE_NAME 3=COLUMN_NAME 4=KEY_SEQ 5=PK_NAME

        $pkey_column_name = 3;
        $pkey_key_seq     = 4;
        foreach ($primaryKeysResult as $pkeysRow) {
            $primaryKeyColumn[$pkeysRow[$pkey_column_name]] = $pkeysRow[$pkey_key_seq];
        }

        $desc = array();
        $p    = 1;
        foreach ($result as $key => $row) {
            $identity = false;
            $words    = explode(' ', $row[$type_name], 2);
            if (isset($words[0])) {
                $type = $words[0];
                if (isset($words[1])) {
                    $identity = (bool) preg_match('/identity/', $words[1]);
                }
            }

            $isPrimary = array_key_exists($row[$column_name], $primaryKeyColumn);
            if ($isPrimary) {
                $primaryPosition = $primaryKeyColumn[$row[$column_name]];
            } else {
                $primaryPosition = null;
            }

            $desc[$this->foldCase($row[$column_name])] = array(
                'SCHEMA_NAME'      => null, // @todo
                'TABLE_NAME'       => $this->foldCase($row[$table_name]),
                'COLUMN_NAME'      => $this->foldCase($row[$column_name]),
                'COLUMN_POSITION'  => (int) $row[$column_position],
                'DATA_TYPE'        => $type,
                'DEFAULT'          => $row[$column_def],
                'NULLABLE'         => (bool) $row[$nullable],
                'LENGTH'           => $row[$length],
                'SCALE'            => $row[$scale],
                'PRECISION'        => $row[$precision],
                'UNSIGNED'         => null, // @todo
                'PRIMARY'          => $isPrimary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity,
            );
        }

        return $desc;
    }

    /**
     * Leave autocommit mode and begin a transaction.
     *
     * @return void
     * @throws Zend_Db_Adapter_Sqlsrv_Exception
     */
    protected function _beginTransaction()
    {
        if (!sqlsrv_begin_transaction($this->_connection)) {
            require_once 'Zend/Db/Adapter/Sqlsrv/Exception.php';
            throw new Zend_Db_Adapter_Sqlsrv_Exception(sqlsrv_errors());
        }
    }

    /**
     * Commit a transaction and return to autocommit mode.
     *
     * @return void
     * @throws Zend_Db_Adapter_Sqlsrv_Exception
     */
    protected function _commit()
    {
        if (!sqlsrv_commit($this->_connection)) {
            require_once 'Zend/Db/Adapter/Sqlsrv/Exception.php';
            throw new Zend_Db_Adapter_Sqlsrv_Exception(sqlsrv_errors());
        }
    }

    /**
     * Roll back a transaction and return to autocommit mode.
     *
     * @return void
     * @throws Zend_Db_Adapter_Sqlsrv_Exception
     */
    protected function _rollBack()
    {
        if (!sqlsrv_rollback($this->_connection)) {
            require_once 'Zend/Db/Adapter/Sqlsrv/Exception.php';
            throw new Zend_Db_Adapter_Sqlsrv_Exception(sqlsrv_errors());
        }
    }

    /**
     * Set the fetch mode.
     *
     * @todo Support FETCH_CLASS and FETCH_INTO.
     *
     * @param integer $mode A fetch mode.
     * @return void
     * @throws Zend_Db_Adapter_Sqlsrv_Exception
     */
    public function setFetchMode($mode)
    {
        switch ($mode) {
            case Zend_Db::FETCH_NUM:   // seq array
            case Zend_Db::FETCH_ASSOC: // assoc array
            case Zend_Db::FETCH_BOTH:  // seq+assoc array
            case Zend_Db::FETCH_OBJ:   // object
                $this->_fetchMode = $mode;
                break;
            case Zend_Db::FETCH_BOUND: // bound to PHP variable
                require_once 'Zend/Db/Adapter/Sqlsrv/Exception.php';
                throw new Zend_Db_Adapter_Sqlsrv_Exception('FETCH_BOUND is not supported yet');
                break;
            default:
                require_once 'Zend/Db/Adapter/Sqlsrv/Exception.php';
                throw new Zend_Db_Adapter_Sqlsrv_Exception("Invalid fetch mode '$mode' specified");
                break;
        }
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param integer $count
     * @param integer $offset OPTIONAL
     * @return string
     * @throws Zend_Db_Adapter_Sqlsrv_Exception
     */
     public function limit($sql, $count, $offset = 0)
     {
        $count = intval($count);
        if ($count <= 0) {
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            /** @see Zend_Db_Adapter_Exception */
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("LIMIT argument offset=$offset is not valid");
        }

        $orderby = stristr($sql, 'ORDER BY');
        if ($orderby !== false) {
            $sort  = (stripos($orderby, ' desc') !== false) ? 'desc' : 'asc';
            $order = str_ireplace('ORDER BY', '', $orderby);
            $order = trim(preg_replace('/\bASC\b|\bDESC\b/i', '', $order));
        }

        $sql = preg_replace('/^SELECT\s/i', 'SELECT TOP ' . ($count+$offset) . ' ', $sql);

        $sql = 'SELECT * FROM (SELECT TOP ' . $count . ' * FROM (' . $sql . ') AS inner_tbl';
        if ($orderby !== false) {
            $sql .= ' ORDER BY ' . $order . ' ';
            $sql .= (stripos($sort, 'asc') !== false) ? 'DESC' : 'ASC';
        }
        $sql .= ') AS outer_tbl';
        if ($orderby !== false) {
            $sql .= ' ORDER BY ' . $order . ' ' . $sort;
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
        if ($type == 'positional') {
            return true;
        }

        // if its 'named' or anything else
        return false;
    }

    /**
     * Retrieve server version in PHP style
     *
     * @return string
     */
    public function getServerVersion()
    {
        $this->_connect();
        $version = sqlsrv_client_info($this->_connection);

        if ($version !== false) {
            return $version['DriverVer'];
        }

        return null;
    }
}
