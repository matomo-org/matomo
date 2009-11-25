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
 * @version    $Id: Db2.php 18951 2009-11-12 16:26:19Z alexander $
 *
 */

/**
 * @see Zend_Db
 */
require_once 'Zend/Db.php';

/**
 * @see Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';

/**
 * @see Zend_Db_Statement_Db2
 */
require_once 'Zend/Db/Statement/Db2.php';


/**
 * @package    Zend_Db
 * @copyright  Copyright (c) 2005-2009 Zend Technologies Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Db_Adapter_Db2 extends Zend_Db_Adapter_Abstract
{
    /**
     * User-provided configuration.
     *
     * Basic keys are:
     *
     * username   => (string)  Connect to the database as this username.
     * password   => (string)  Password associated with the username.
     * host       => (string)  What host to connect to (default 127.0.0.1)
     * dbname     => (string)  The name of the database to user
     * protocol   => (string)  Protocol to use, defaults to "TCPIP"
     * port       => (integer) Port number to use for TCP/IP if protocol is "TCPIP"
     * persistent => (boolean) Set TRUE to use a persistent connection (db2_pconnect)
     * os         => (string)  This should be set to 'i5' if the db is on an os400/i5
     * schema     => (string)  The default schema the connection should use
     *
     * @var array
     */
    protected $_config = array(
        'dbname'       => null,
        'username'     => null,
        'password'     => null,
        'host'         => 'localhost',
        'port'         => '50000',
        'protocol'     => 'TCPIP',
        'persistent'   => false,
        'os'           => null,
        'schema'       => null
    );

    /**
     * Execution mode
     *
     * @var int execution flag (DB2_AUTOCOMMIT_ON or DB2_AUTOCOMMIT_OFF)
     */
    protected $_execute_mode = DB2_AUTOCOMMIT_ON;

    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_defaultStmtClass = 'Zend_Db_Statement_Db2';
    protected $_isI5 = false;

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
        'INTEGER'            => Zend_Db::INT_TYPE,
        'SMALLINT'           => Zend_Db::INT_TYPE,
        'BIGINT'             => Zend_Db::BIGINT_TYPE,
        'DECIMAL'            => Zend_Db::FLOAT_TYPE,
        'NUMERIC'            => Zend_Db::FLOAT_TYPE
    );

    /**
     * Creates a connection resource.
     *
     * @return void
     */
    protected function _connect()
    {
        if (is_resource($this->_connection)) {
            // connection already exists
            return;
        }

        if (!extension_loaded('ibm_db2')) {
            /**
             * @see Zend_Db_Adapter_Db2_Exception
             */
            require_once 'Zend/Db/Adapter/Db2/Exception.php';
            throw new Zend_Db_Adapter_Db2_Exception('The IBM DB2 extension is required for this adapter but the extension is not loaded');
        }

        $this->_determineI5();
        if ($this->_config['persistent']) {
            // use persistent connection
            $conn_func_name = 'db2_pconnect';
        } else {
            // use "normal" connection
            $conn_func_name = 'db2_connect';
        }

        if (!isset($this->_config['driver_options']['autocommit'])) {
            // set execution mode
            $this->_config['driver_options']['autocommit'] = &$this->_execute_mode;
        }

        if (isset($this->_config['options'][Zend_Db::CASE_FOLDING])) {
            $caseAttrMap = array(
                Zend_Db::CASE_NATURAL => DB2_CASE_NATURAL,
                Zend_Db::CASE_UPPER   => DB2_CASE_UPPER,
                Zend_Db::CASE_LOWER   => DB2_CASE_LOWER
            );
            $this->_config['driver_options']['DB2_ATTR_CASE'] = $caseAttrMap[$this->_config['options'][Zend_Db::CASE_FOLDING]];
        }

        if ($this->_config['host'] !== 'localhost' && !$this->_isI5) {
            // if the host isn't localhost, use extended connection params
            $dbname = 'DRIVER={IBM DB2 ODBC DRIVER}' .
                     ';DATABASE=' . $this->_config['dbname'] .
                     ';HOSTNAME=' . $this->_config['host'] .
                     ';PORT='     . $this->_config['port'] .
                     ';PROTOCOL=' . $this->_config['protocol'] .
                     ';UID='      . $this->_config['username'] .
                     ';PWD='      . $this->_config['password'] .';';
            $this->_connection = $conn_func_name(
                $dbname,
                null,
                null,
                $this->_config['driver_options']
            );
        } else {
            // host is localhost, so use standard connection params
            $this->_connection = $conn_func_name(
                $this->_config['dbname'],
                $this->_config['username'],
                $this->_config['password'],
                $this->_config['driver_options']
            );
        }

        // check the connection
        if (!$this->_connection) {
            /**
             * @see Zend_Db_Adapter_Db2_Exception
             */
            require_once 'Zend/Db/Adapter/Db2/Exception.php';
            throw new Zend_Db_Adapter_Db2_Exception(db2_conn_errormsg(), db2_conn_error());
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
                     && get_resource_type($this->_connection) == 'DB2 Connection'));
    }

    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection()
    {
        if ($this->isConnected()) {
            db2_close($this->_connection);
        }
        $this->_connection = null;
    }

    /**
     * Returns an SQL statement for preparation.
     *
     * @param string $sql The SQL statement with placeholders.
     * @return Zend_Db_Statement_Db2
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
        $stmt->setFetchMode($this->_fetchMode);
        return $stmt;
    }

    /**
     * Gets the execution mode
     *
     * @return int the execution mode (DB2_AUTOCOMMIT_ON or DB2_AUTOCOMMIT_OFF)
     */
    public function _getExecuteMode()
    {
        return $this->_execute_mode;
    }

    /**
     * @param integer $mode
     * @return void
     */
    public function _setExecuteMode($mode)
    {
        switch ($mode) {
            case DB2_AUTOCOMMIT_OFF:
            case DB2_AUTOCOMMIT_ON:
                $this->_execute_mode = $mode;
                db2_autocommit($this->_connection, $mode);
                break;
            default:
                /**
                 * @see Zend_Db_Adapter_Db2_Exception
                 */
                require_once 'Zend/Db/Adapter/Db2/Exception.php';
                throw new Zend_Db_Adapter_Db2_Exception("execution mode not supported");
                break;
        }
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
        /**
         * Use db2_escape_string() if it is present in the IBM DB2 extension.
         * But some supported versions of PHP do not include this function,
         * so fall back to default quoting in the parent class.
         */
        if (function_exists('db2_escape_string')) {
            return "'" . db2_escape_string($value) . "'";
        }
        return parent::_quote($value);
    }

    /**
     * @return string
     */
    public function getQuoteIdentifierSymbol()
    {
        $this->_connect();
        $info = db2_server_info($this->_connection);
        if ($info) {
            $identQuote = $info->IDENTIFIER_QUOTE_CHAR;
        } else {
            // db2_server_info() does not return result on some i5 OS version
            if ($this->_isI5) {
                $identQuote ="'";
            }
        }
        return $identQuote;
    }

    /**
     * Returns a list of the tables in the database.
     * @param string $schema OPTIONAL
     * @return array
     */
    public function listTables($schema = null)
    {
        $this->_connect();

        if ($schema === null && $this->_config['schema'] != null) {
            $schema = $this->_config['schema'];
        }

        $tables = array();

        if (!$this->_isI5) {
            if ($schema) {
                $stmt = db2_tables($this->_connection, null, $schema);
            } else {
                $stmt = db2_tables($this->_connection);
            }
            while ($row = db2_fetch_assoc($stmt)) {
                $tables[] = $row['TABLE_NAME'];
            }
        } else {
            $tables = $this->_i5listTables($schema);
        }

        return $tables;
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
     *                     DB2 not supports UNSIGNED integer.
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
        // Ensure the connection is made so that _isI5 is set
        $this->_connect();

        if ($schemaName === null && $this->_config['schema'] != null) {
            $schemaName = $this->_config['schema'];
        }

        if (!$this->_isI5) {

            $sql = "SELECT DISTINCT c.tabschema, c.tabname, c.colname, c.colno,
                c.typename, c.default, c.nulls, c.length, c.scale,
                c.identity, tc.type AS tabconsttype, k.colseq
                FROM syscat.columns c
                LEFT JOIN (syscat.keycoluse k JOIN syscat.tabconst tc
                ON (k.tabschema = tc.tabschema
                    AND k.tabname = tc.tabname
                    AND tc.type = 'P'))
                ON (c.tabschema = k.tabschema
                    AND c.tabname = k.tabname
                    AND c.colname = k.colname)
                WHERE "
                . $this->quoteInto('UPPER(c.tabname) = UPPER(?)', $tableName);

            if ($schemaName) {
               $sql .= $this->quoteInto(' AND UPPER(c.tabschema) = UPPER(?)', $schemaName);
            }

            $sql .= " ORDER BY c.colno";

        } else {

            // DB2 On I5 specific query
            $sql = "SELECT DISTINCT C.TABLE_SCHEMA, C.TABLE_NAME, C.COLUMN_NAME, C.ORDINAL_POSITION,
                C.DATA_TYPE, C.COLUMN_DEFAULT, C.NULLS ,C.LENGTH, C.SCALE, LEFT(C.IDENTITY,1),
                LEFT(tc.TYPE, 1) AS tabconsttype, k.COLSEQ
                FROM QSYS2.SYSCOLUMNS C
                LEFT JOIN (QSYS2.syskeycst k JOIN QSYS2.SYSCST tc
                    ON (k.TABLE_SCHEMA = tc.TABLE_SCHEMA
                      AND k.TABLE_NAME = tc.TABLE_NAME
                      AND LEFT(tc.type,1) = 'P'))
                    ON (C.TABLE_SCHEMA = k.TABLE_SCHEMA
                       AND C.TABLE_NAME = k.TABLE_NAME
                       AND C.COLUMN_NAME = k.COLUMN_NAME)
                WHERE "
                 . $this->quoteInto('UPPER(C.TABLE_NAME) = UPPER(?)', $tableName);

            if ($schemaName) {
                $sql .= $this->quoteInto(' AND UPPER(C.TABLE_SCHEMA) = UPPER(?)', $schemaName);
            }

            $sql .= " ORDER BY C.ORDINAL_POSITION FOR FETCH ONLY";
        }

        $desc = array();
        $stmt = $this->query($sql);

        /**
         * To avoid case issues, fetch using FETCH_NUM
         */
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        /**
         * The ordering of columns is defined by the query so we can map
         * to variables to improve readability
         */
        $tabschema      = 0;
        $tabname        = 1;
        $colname        = 2;
        $colno          = 3;
        $typename       = 4;
        $default        = 5;
        $nulls          = 6;
        $length         = 7;
        $scale          = 8;
        $identityCol    = 9;
        $tabconstType   = 10;
        $colseq         = 11;

        foreach ($result as $key => $row) {
            list ($primary, $primaryPosition, $identity) = array(false, null, false);
            if ($row[$tabconstType] == 'P') {
                $primary = true;
                $primaryPosition = $row[$colseq];
            }
            /**
             * In IBM DB2, an column can be IDENTITY
             * even if it is not part of the PRIMARY KEY.
             */
            if ($row[$identityCol] == 'Y') {
                $identity = true;
            }

            // only colname needs to be case adjusted
            $desc[$this->foldCase($row[$colname])] = array(
                'SCHEMA_NAME'      => $this->foldCase($row[$tabschema]),
                'TABLE_NAME'       => $this->foldCase($row[$tabname]),
                'COLUMN_NAME'      => $this->foldCase($row[$colname]),
                'COLUMN_POSITION'  => (!$this->_isI5) ? $row[$colno]+1 : $row[$colno],
                'DATA_TYPE'        => $row[$typename],
                'DEFAULT'          => $row[$default],
                'NULLABLE'         => (bool) ($row[$nulls] == 'Y'),
                'LENGTH'           => $row[$length],
                'SCALE'            => $row[$scale],
                'PRECISION'        => ($row[$typename] == 'DECIMAL' ? $row[$length] : 0),
                'UNSIGNED'         => false,
                'PRIMARY'          => $primary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity
            );
        }

        return $desc;
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

        if (!$this->_isI5) {
            $quotedSequenceName = $this->quoteIdentifier($sequenceName, true);
            $sql = 'SELECT PREVVAL FOR ' . $quotedSequenceName . ' AS VAL FROM SYSIBM.SYSDUMMY1';
        } else {
            $quotedSequenceName = $sequenceName;
            $sql = 'SELECT PREVVAL FOR ' . $this->quoteIdentifier($sequenceName, true) . ' AS VAL FROM QSYS2.QSQPTABL';
        }

        $value = $this->fetchOne($sql);
        return (string) $value;
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
        $sql = 'SELECT NEXTVAL FOR '.$this->quoteIdentifier($sequenceName, true).' AS VAL FROM SYSIBM.SYSDUMMY1';
        $value = $this->fetchOne($sql);
        return (string) $value;
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
     * The IDENTITY_VAL_LOCAL() function gives the last generated identity value
     * in the current process, even if it was for a GENERATED column.
     *
     * @param string $tableName OPTIONAL
     * @param string $primaryKey OPTIONAL
     * @param string $idType OPTIONAL used for i5 platform to define sequence/idenity unique value
     * @return string
     */

    public function lastInsertId($tableName = null, $primaryKey = null, $idType = null)
    {
        $this->_connect();

        if ($this->_isI5) {
            return (string) $this->_i5LastInsertId($tableName, $idType);
        }

        if ($tableName !== null) {
            $sequenceName = $tableName;
            if ($primaryKey) {
                $sequenceName .= "_$primaryKey";
            }
            $sequenceName .= '_seq';
            return $this->lastSequenceId($sequenceName);
        }

        $sql = 'SELECT IDENTITY_VAL_LOCAL() AS VAL FROM SYSIBM.SYSDUMMY1';
        $value = $this->fetchOne($sql);
        return (string) $value;
    }

    /**
     * Begin a transaction.
     *
     * @return void
     */
    protected function _beginTransaction()
    {
        $this->_setExecuteMode(DB2_AUTOCOMMIT_OFF);
    }

    /**
     * Commit a transaction.
     *
     * @return void
     */
    protected function _commit()
    {
        if (!db2_commit($this->_connection)) {
            /**
             * @see Zend_Db_Adapter_Db2_Exception
             */
            require_once 'Zend/Db/Adapter/Db2/Exception.php';
            throw new Zend_Db_Adapter_Db2_Exception(
                db2_conn_errormsg($this->_connection),
                db2_conn_error($this->_connection));
        }

        $this->_setExecuteMode(DB2_AUTOCOMMIT_ON);
    }

    /**
     * Rollback a transaction.
     *
     * @return void
     */
    protected function _rollBack()
    {
        if (!db2_rollback($this->_connection)) {
            /**
             * @see Zend_Db_Adapter_Db2_Exception
             */
            require_once 'Zend/Db/Adapter/Db2/Exception.php';
            throw new Zend_Db_Adapter_Db2_Exception(
                db2_conn_errormsg($this->_connection),
                db2_conn_error($this->_connection));
        }
        $this->_setExecuteMode(DB2_AUTOCOMMIT_ON);
    }

    /**
     * Set the fetch mode.
     *
     * @param integer $mode
     * @return void
     * @throws Zend_Db_Adapter_Db2_Exception
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
            case Zend_Db::FETCH_BOUND:   // bound to PHP variable
                /**
                 * @see Zend_Db_Adapter_Db2_Exception
                 */
                require_once 'Zend/Db/Adapter/Db2/Exception.php';
                throw new Zend_Db_Adapter_Db2_Exception('FETCH_BOUND is not supported yet');
                break;
            default:
                /**
                 * @see Zend_Db_Adapter_Db2_Exception
                 */
                require_once 'Zend/Db/Adapter/Db2/Exception.php';
                throw new Zend_Db_Adapter_Db2_Exception("Invalid fetch mode '$mode' specified");
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
     */
    public function limit($sql, $count, $offset = 0)
    {
        $count = intval($count);
        if ($count <= 0) {
            /**
             * @see Zend_Db_Adapter_Db2_Exception
             */
            require_once 'Zend/Db/Adapter/Db2/Exception.php';
            throw new Zend_Db_Adapter_Db2_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            /**
             * @see Zend_Db_Adapter_Db2_Exception
             */
            require_once 'Zend/Db/Adapter/Db2/Exception.php';
            throw new Zend_Db_Adapter_Db2_Exception("LIMIT argument offset=$offset is not valid");
        }

        if ($offset == 0) {
            $limit_sql = $sql . " FETCH FIRST $count ROWS ONLY";
            return $limit_sql;
        }

        /**
         * DB2 does not implement the LIMIT clause as some RDBMS do.
         * We have to simulate it with subqueries and ROWNUM.
         * Unfortunately because we use the column wildcard "*",
         * this puts an extra column into the query result set.
         */
        $limit_sql = "SELECT z2.*
            FROM (
                SELECT ROW_NUMBER() OVER() AS \"ZEND_DB_ROWNUM\", z1.*
                FROM (
                    " . $sql . "
                ) z1
            ) z2
            WHERE z2.zend_db_rownum BETWEEN " . ($offset+1) . " AND " . ($offset+$count);
        return $limit_sql;
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
        $server_info = db2_server_info($this->_connection);
        if ($server_info !== false) {
            $version = $server_info->DBMS_VER;
            if ($this->_isI5) {
                $version = (int) substr($version, 0, 2) . '.' . (int) substr($version, 2, 2) . '.' . (int) substr($version, 4);
            }
            return $version;
        } else {
            return null;
        }
    }

    /**
     * Return whether or not this is running on i5
     *
     * @return bool
     */
    public function isI5()
    {
        if ($this->_isI5 === null) {
            $this->_determineI5();
        }

        return (bool) $this->_isI5;
    }

    /**
     * Check the connection parameters according to verify
     * type of used OS
     *
     *  @return void
     */
    protected function _determineI5()
    {
        // first us the compiled flag.
        $this->_isI5 = (php_uname('s') == 'OS400') ? true : false;

        // if this is set, then us it
        if (isset($this->_config['os'])){
            if (strtolower($this->_config['os']) === 'i5') {
                $this->_isI5 = true;
            } else {
                // any other value passed in, its null
                $this->_isI5 = false;
            }
        }

    }

    /**
     * Db2 On I5 specific method
     *
     * Returns a list of the tables in the database .
     * Used only for DB2/400.
     *
     * @return array
     */
    protected function _i5listTables($schema = null)
    {
        //list of i5 libraries.
        $tables = array();
        if ($schema) {
            $tablesStatement = db2_tables($this->_connection, null, $schema);
            while ($rowTables = db2_fetch_assoc($tablesStatement) ) {
                if ($rowTables['TABLE_NAME'] !== null) {
                    $tables[] = $rowTables['TABLE_NAME'];
                }
            }
        } else {
            $schemaStatement = db2_tables($this->_connection);
            while ($schema = db2_fetch_assoc($schemaStatement)) {
                if ($schema['TABLE_SCHEM'] !== null) {
                    // list of the tables which belongs to the selected library
                    $tablesStatement = db2_tables($this->_connection, NULL, $schema['TABLE_SCHEM']);
                    if (is_resource($tablesStatement)) {
                        while ($rowTables = db2_fetch_assoc($tablesStatement) ) {
                            if ($rowTables['TABLE_NAME'] !== null) {
                                $tables[] = $rowTables['TABLE_NAME'];
                            }
                        }
                    }
                }
            }
        }

        return $tables;
    }

    protected function _i5LastInsertId($objectName = null, $idType = null)
    {

        if ($objectName === null) {
            $sql = 'SELECT IDENTITY_VAL_LOCAL() AS VAL FROM QSYS2.QSQPTABL';
            $value = $this->fetchOne($sql);
            return $value;
        }

        if (strtoupper($idType) === 'S'){
            //check i5_lib option
            $sequenceName = $objectName;
            return $this->lastSequenceId($sequenceName);
        }

            //returns last identity value for the specified table
        //if (strtoupper($idType) === 'I') {
        $tableName = $objectName;
        return $this->fetchOne('SELECT IDENTITY_VAL_LOCAL() from ' . $this->quoteIdentifier($tableName));
    }

}


