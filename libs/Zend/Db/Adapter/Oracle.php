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
 * @version    $Id: Oracle.php 19048 2009-11-19 18:15:05Z mikaelkael $
 */

/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';

/**
 * @see Zend_Db_Statement_Oracle
 */
require_once 'Zend/Db/Statement/Oracle.php';

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Oracle extends Zend_Db_Adapter_Abstract
{
    /**
     * User-provided configuration.
     *
     * Basic keys are:
     *
     * username => (string) Connect to the database as this username.
     * password => (string) Password associated with the username.
     * dbname   => Either the name of the local Oracle instance, or the
     *             name of the entry in tnsnames.ora to which you want to connect.
     * persistent => (boolean) Set TRUE to use a persistent connection
     * @var array
     */
    protected $_config = array(
        'dbname'       => null,
        'username'     => null,
        'password'     => null,
        'persistent'   => false
    );

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
        'BINARY_DOUBLE'      => Zend_Db::FLOAT_TYPE,
        'BINARY_FLOAT'       => Zend_Db::FLOAT_TYPE,
        'NUMBER'             => Zend_Db::FLOAT_TYPE,
    );

    /**
     * @var integer
     */
    protected $_execute_mode = null;

    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_defaultStmtClass = 'Zend_Db_Statement_Oracle';

    /**
     * Check if LOB field are returned as string
     * instead of OCI-Lob object
     *
     * @var boolean
     */
    protected $_lobAsString = null;

    /**
     * Creates a connection resource.
     *
     * @return void
     * @throws Zend_Db_Adapter_Oracle_Exception
     */
    protected function _connect()
    {
        if (is_resource($this->_connection)) {
            // connection already exists
            return;
        }

        if (!extension_loaded('oci8')) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Adapter/Oracle/Exception.php';
            throw new Zend_Db_Adapter_Oracle_Exception('The OCI8 extension is required for this adapter but the extension is not loaded');
        }

        $this->_setExecuteMode(OCI_COMMIT_ON_SUCCESS);

        $connectionFuncName = ($this->_config['persistent'] == true) ? 'oci_pconnect' : 'oci_connect';

        $this->_connection = @$connectionFuncName(
                $this->_config['username'],
                $this->_config['password'],
                $this->_config['dbname'],
                $this->_config['charset']);

        // check the connection
        if (!$this->_connection) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Adapter/Oracle/Exception.php';
            throw new Zend_Db_Adapter_Oracle_Exception(oci_error());
        }
    }

    /**
     * Test if a connection is active
     *
     * @return boolean
     */
    public function isConnected()
    {
        return ((bool) (is_resource($this->_connection)
                     && get_resource_type($this->_connection) == 'oci8 connection'));
    }

    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection()
    {
        if ($this->isConnected()) {
            oci_close($this->_connection);
        }
        $this->_connection = null;
    }

    /**
     * Activate/deactivate return of LOB as string
     *
     * @param string $lob_as_string
     * @return Zend_Db_Adapter_Oracle
     */
    public function setLobAsString($lobAsString)
    {
        $this->_lobAsString = (bool) $lobAsString;
        return $this;
    }

    /**
     * Return whether or not LOB are returned as string
     *
     * @return boolean
     */
    public function getLobAsString()
    {
        if ($this->_lobAsString === null) {
            // if never set by user, we use driver option if it exists otherwise false
            if (isset($this->_config['driver_options']) &&
                isset($this->_config['driver_options']['lob_as_string'])) {
                $this->_lobAsString = (bool) $this->_config['driver_options']['lob_as_string'];
            } else {
                $this->_lobAsString = false;
            }
        }
        return $this->_lobAsString;
    }

    /**
     * Returns an SQL statement for preparation.
     *
     * @param string $sql The SQL statement with placeholders.
     * @return Zend_Db_Statement_Oracle
     */
    public function prepare($sql)
    {
        $this->_connect();
        $stmtClass = $this->_defaultStmtClass;
        if (!class_exists($stmtClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($stmtClass);
        }
        $stmt = new $stmtClass($this, $sql);
        if ($stmt instanceof Zend_Db_Statement_Oracle) {
            $stmt->setLobAsString($this->getLobAsString());
        }
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
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        $value = str_replace("'", "''", $value);
        return "'" . addcslashes($value, "\000\n\r\\\032") . "'";
    }

    /**
     * Quote a table identifier and alias.
     *
     * @param string|array|Zend_Db_Expr $ident The identifier or expression.
     * @param string $alias An alias for the table.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    public function quoteTableAs($ident, $alias = null, $auto = false)
    {
        // Oracle doesn't allow the 'AS' keyword between the table identifier/expression and alias.
        return $this->_quoteIdentifierAs($ident, $alias, $auto, ' ');
    }

    /**
     * Return the most recent value from the specified sequence in the database.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return string
     */
    public function lastSequenceId($sequenceName)
    {
        $this->_connect();
        $sql = 'SELECT '.$this->quoteIdentifier($sequenceName, true).'.CURRVAL FROM dual';
        $value = $this->fetchOne($sql);
        return $value;
    }

    /**
     * Generate a new value from the specified sequence in the database, and return it.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return string
     */
    public function nextSequenceId($sequenceName)
    {
        $this->_connect();
        $sql = 'SELECT '.$this->quoteIdentifier($sequenceName, true).'.NEXTVAL FROM dual';
        $value = $this->fetchOne($sql);
        return $value;
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
     * Oracle does not support IDENTITY columns, so if the sequence is not
     * specified, this method returns null.
     *
     * @param string $tableName   OPTIONAL Name of table.
     * @param string $primaryKey  OPTIONAL Name of primary key column.
     * @return string
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        if ($tableName !== null) {
            $sequenceName = $tableName;
            if ($primaryKey) {
                $sequenceName .= "_$primaryKey";
            }
            $sequenceName .= '_seq';
            return $this->lastSequenceId($sequenceName);
        }

        // No support for IDENTITY columns; return null
        return null;
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        $this->_connect();
        $data = $this->fetchCol('SELECT table_name FROM all_tables');
        return $data;
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
        $version = $this->getServerVersion();
        if (($version === null) || version_compare($version, '9.0.0', '>=')) {
            $sql = "SELECT TC.TABLE_NAME, TC.OWNER, TC.COLUMN_NAME, TC.DATA_TYPE,
                    TC.DATA_DEFAULT, TC.NULLABLE, TC.COLUMN_ID, TC.DATA_LENGTH,
                    TC.DATA_SCALE, TC.DATA_PRECISION, C.CONSTRAINT_TYPE, CC.POSITION
                FROM ALL_TAB_COLUMNS TC
                LEFT JOIN (ALL_CONS_COLUMNS CC JOIN ALL_CONSTRAINTS C
                    ON (CC.CONSTRAINT_NAME = C.CONSTRAINT_NAME AND CC.TABLE_NAME = C.TABLE_NAME AND CC.OWNER = C.OWNER AND C.CONSTRAINT_TYPE = 'P'))
                  ON TC.TABLE_NAME = CC.TABLE_NAME AND TC.COLUMN_NAME = CC.COLUMN_NAME
                WHERE UPPER(TC.TABLE_NAME) = UPPER(:TBNAME)";
            $bind[':TBNAME'] = $tableName;
            if ($schemaName) {
                $sql .= ' AND UPPER(TC.OWNER) = UPPER(:SCNAME)';
                $bind[':SCNAME'] = $schemaName;
            }
            $sql .= ' ORDER BY TC.COLUMN_ID';
        } else {
            $subSql="SELECT AC.OWNER, AC.TABLE_NAME, ACC.COLUMN_NAME, AC.CONSTRAINT_TYPE, ACC.POSITION
                from ALL_CONSTRAINTS AC, ALL_CONS_COLUMNS ACC
                  WHERE ACC.CONSTRAINT_NAME = AC.CONSTRAINT_NAME
                    AND ACC.TABLE_NAME = AC.TABLE_NAME
                    AND ACC.OWNER = AC.OWNER
                    AND AC.CONSTRAINT_TYPE = 'P'
                    AND UPPER(AC.TABLE_NAME) = UPPER(:TBNAME)";
            $bind[':TBNAME'] = $tableName;
            if ($schemaName) {
                $subSql .= ' AND UPPER(ACC.OWNER) = UPPER(:SCNAME)';
                $bind[':SCNAME'] = $schemaName;
            }
            $sql="SELECT TC.TABLE_NAME, TC.OWNER, TC.COLUMN_NAME, TC.DATA_TYPE,
                    TC.DATA_DEFAULT, TC.NULLABLE, TC.COLUMN_ID, TC.DATA_LENGTH,
                    TC.DATA_SCALE, TC.DATA_PRECISION, CC.CONSTRAINT_TYPE, CC.POSITION
                FROM ALL_TAB_COLUMNS TC, ($subSql) CC
                WHERE UPPER(TC.TABLE_NAME) = UPPER(:TBNAME)
                  AND TC.OWNER = CC.OWNER(+) AND TC.TABLE_NAME = CC.TABLE_NAME(+) AND TC.COLUMN_NAME = CC.COLUMN_NAME(+)";
            if ($schemaName) {
                $sql .= ' AND UPPER(TC.OWNER) = UPPER(:SCNAME)';
            }
            $sql .= ' ORDER BY TC.COLUMN_ID';
        }

        $stmt = $this->query($sql, $bind);

        /**
         * Use FETCH_NUM so we are not dependent on the CASE attribute of the PDO connection
         */
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $table_name      = 0;
        $owner           = 1;
        $column_name     = 2;
        $data_type       = 3;
        $data_default    = 4;
        $nullable        = 5;
        $column_id       = 6;
        $data_length     = 7;
        $data_scale      = 8;
        $data_precision  = 9;
        $constraint_type = 10;
        $position        = 11;

        $desc = array();
        foreach ($result as $key => $row) {
            list ($primary, $primaryPosition, $identity) = array(false, null, false);
            if ($row[$constraint_type] == 'P') {
                $primary = true;
                $primaryPosition = $row[$position];
                /**
                 * Oracle does not support auto-increment keys.
                 */
                $identity = false;
            }
            $desc[$this->foldCase($row[$column_name])] = array(
                'SCHEMA_NAME'      => $this->foldCase($row[$owner]),
                'TABLE_NAME'       => $this->foldCase($row[$table_name]),
                'COLUMN_NAME'      => $this->foldCase($row[$column_name]),
                'COLUMN_POSITION'  => $row[$column_id],
                'DATA_TYPE'        => $row[$data_type],
                'DEFAULT'          => $row[$data_default],
                'NULLABLE'         => (bool) ($row[$nullable] == 'Y'),
                'LENGTH'           => $row[$data_length],
                'SCALE'            => $row[$data_scale],
                'PRECISION'        => $row[$data_precision],
                'UNSIGNED'         => null, // @todo
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity
            );
        }
        return $desc;
    }

    /**
     * Leave autocommit mode and begin a transaction.
     *
     * @return void
     */
    protected function _beginTransaction()
    {
        $this->_setExecuteMode(OCI_DEFAULT);
    }

    /**
     * Commit a transaction and return to autocommit mode.
     *
     * @return void
     * @throws Zend_Db_Adapter_Oracle_Exception
     */
    protected function _commit()
    {
        if (!oci_commit($this->_connection)) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Adapter/Oracle/Exception.php';
            throw new Zend_Db_Adapter_Oracle_Exception(oci_error($this->_connection));
        }
        $this->_setExecuteMode(OCI_COMMIT_ON_SUCCESS);
    }

    /**
     * Roll back a transaction and return to autocommit mode.
     *
     * @return void
     * @throws Zend_Db_Adapter_Oracle_Exception
     */
    protected function _rollBack()
    {
        if (!oci_rollback($this->_connection)) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Adapter/Oracle/Exception.php';
            throw new Zend_Db_Adapter_Oracle_Exception(oci_error($this->_connection));
        }
        $this->_setExecuteMode(OCI_COMMIT_ON_SUCCESS);
    }

    /**
     * Set the fetch mode.
     *
     * @todo Support FETCH_CLASS and FETCH_INTO.
     *
     * @param integer $mode A fetch mode.
     * @return void
     * @throws Zend_Db_Adapter_Oracle_Exception
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
                /**
                 * @see Zend_Db_Adapter_Oracle_Exception
                 */
                require_once 'Zend/Db/Adapter/Oracle/Exception.php';
                throw new Zend_Db_Adapter_Oracle_Exception('FETCH_BOUND is not supported yet');
                break;
            default:
                /**
                 * @see Zend_Db_Adapter_Oracle_Exception
                 */
                require_once 'Zend/Db/Adapter/Oracle/Exception.php';
                throw new Zend_Db_Adapter_Oracle_Exception("Invalid fetch mode '$mode' specified");
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
     * @throws Zend_Db_Adapter_Oracle_Exception
     */
    public function limit($sql, $count, $offset = 0)
    {
        $count = intval($count);
        if ($count <= 0) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Adapter/Oracle/Exception.php';
            throw new Zend_Db_Adapter_Oracle_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Adapter/Oracle/Exception.php';
            throw new Zend_Db_Adapter_Oracle_Exception("LIMIT argument offset=$offset is not valid");
        }

        /**
         * Oracle does not implement the LIMIT clause as some RDBMS do.
         * We have to simulate it with subqueries and ROWNUM.
         * Unfortunately because we use the column wildcard "*",
         * this puts an extra column into the query result set.
         */
        $limit_sql = "SELECT z2.*
            FROM (
                SELECT z1.*, ROWNUM AS \"zend_db_rownum\"
                FROM (
                    " . $sql . "
                ) z1
            ) z2
            WHERE z2.\"zend_db_rownum\" BETWEEN " . ($offset+1) . " AND " . ($offset+$count);
        return $limit_sql;
    }

    /**
     * @param integer $mode
     * @throws Zend_Db_Adapter_Oracle_Exception
     */
    private function _setExecuteMode($mode)
    {
        switch($mode) {
            case OCI_COMMIT_ON_SUCCESS:
            case OCI_DEFAULT:
            case OCI_DESCRIBE_ONLY:
                $this->_execute_mode = $mode;
                break;
            default:
                /**
                 * @see Zend_Db_Adapter_Oracle_Exception
                 */
                require_once 'Zend/Db/Adapter/Oracle/Exception.php';
                throw new Zend_Db_Adapter_Oracle_Exception("Invalid execution mode '$mode' specified");
                break;
        }
    }

    /**
     * @return int
     */
    public function _getExecuteMode()
    {
        return $this->_execute_mode;
    }

    /**
     * Inserts a table row with specified data.
     *
     * Oracle does not support anonymous ('?') binds.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind)
    {
        $i = 0;
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        foreach ($bind as $col => $val) {
            $cols[] = $this->quoteIdentifier($col, true);
            if ($val instanceof Zend_Db_Expr) {
                $vals[] = $val->__toString();
                unset($bind[$col]);
            } else {
                $vals[] = ':'.$col.$i;
                unset($bind[$col]);
                $bind[':'.$col.$i] = $val;
            }
            $i++;
        }

        // build the statement
        $sql = "INSERT INTO "
             . $this->quoteIdentifier($table, true)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')';

        // execute the statement and return the number of affected rows
        $stmt = $this->query($sql, $bind);
        $result = $stmt->rowCount();
        return $result;
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
            case 'named':
                return true;
            case 'positional':
            default:
                return false;
        }
    }

    /**
     * Retrieve server version in PHP style
     *
     * @return string
     */
    public function getServerVersion()
    {
        $this->_connect();
        $version = oci_server_version($this->_connection);
        if ($version !== false) {
            $matches = null;
            if (preg_match('/((?:[0-9]{1,2}\.){1,3}[0-9]{1,2})/', $version, $matches)) {
                return $matches[1];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
