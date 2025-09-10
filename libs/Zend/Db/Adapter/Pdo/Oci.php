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
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Oci.php 23775 2011-03-01 17:25:24Z ralph $
 */


/**
 * @see Zend_Db_Adapter_Pdo_Abstract
 */
// require_once 'Zend/Db/Adapter/Pdo/Abstract.php';


/**
 * Class for connecting to Oracle databases and performing common operations.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Pdo_Oci extends Zend_Db_Adapter_Pdo_Abstract
{

    /**
     * PDO type.
     *
     * @var string
     */
    protected $_pdoType = 'oci';

    /**
     * Default class name for a DB statement.
     *
     * @var string
     */
    protected $_defaultStmtClass = 'Zend_Db_Statement_Pdo_Oci';

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
        'NUMBER'             => Zend_Db::FLOAT_TYPE
    );

    /**
     * Creates a PDO DSN for the adapter from $this->_config settings.
     *
     * @return string
     */
    protected function _dsn()
    {
        // baseline of DSN parts
        $dsn = $this->_config;

        if (isset($dsn['host'])) {
            $tns = 'dbname=(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)' .
                   '(HOST=' . $dsn['host'] . ')';

            if (isset($dsn['port'])) {
                $tns .= '(PORT=' . $dsn['port'] . ')';
            } else {
                $tns .= '(PORT=1521)';
            }

            $tns .= '))(CONNECT_DATA=(SID=' . $dsn['dbname'] . ')))';
        } else {
            $tns = 'dbname=' . $dsn['dbname'];
        }

        if (isset($dsn['charset'])) {
            $tns .= ';charset=' . $dsn['charset'];
        }

        return $this->_pdoType . ':' . $tns;
    }

    /**
     * Quote a raw string.
     * Most PDO drivers have an implementation for the quote() method,
     * but the Oracle OCI driver must use the same implementation as the
     * Zend_Db_Adapter_Abstract class.
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
     * @return string The quoted identifier and alias.
     */
    public function quoteTableAs($ident, $alias = null, $auto = false)
    {
        // Oracle doesn't allow the 'AS' keyword between the table identifier/expression and alias.
        return $this->_quoteIdentifierAs($ident, $alias, $auto, ' ');
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
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
     * Return the most recent value from the specified sequence in the database.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return integer
     */
    public function lastSequenceId($sequenceName)
    {
        $this->_connect();
        $value = $this->fetchOne('SELECT '.$this->quoteIdentifier($sequenceName, true).'.CURRVAL FROM dual');
        return $value;
    }

    /**
     * Generate a new value from the specified sequence in the database, and return it.
     * This is supported only on RDBMS brands that support sequences
     * (e.g. Oracle, PostgreSQL, DB2).  Other RDBMS brands return null.
     *
     * @param string $sequenceName
     * @return integer
     */
    public function nextSequenceId($sequenceName)
    {
        $this->_connect();
        $value = $this->fetchOne('SELECT '.$this->quoteIdentifier($sequenceName, true).'.NEXTVAL FROM dual');
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
     * @throws Zend_Db_Adapter_Oracle_Exception
     */
    public function lastInsertId($tableName = null, $primaryKey = null)
    {
        if ($tableName !== null) {
            $sequenceName = $tableName;
            if ($primaryKey) {
                $sequenceName .= $this->foldCase("_$primaryKey");
            }
            $sequenceName .= $this->foldCase('_seq');
            return $this->lastSequenceId($sequenceName);
        }
        // No support for IDENTITY columns; return null
        return null;
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param integer $count
     * @param integer $offset
     * @throws Zend_Db_Adapter_Exception
     * @return string
     */
    public function limit($sql, $count, $offset = 0)
    {
        $count = intval($count);
        if ($count <= 0) {
            /** @see Zend_Db_Adapter_Exception */
            // require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = intval($offset);
        if ($offset < 0) {
            /** @see Zend_Db_Adapter_Exception */
            // require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("LIMIT argument offset=$offset is not valid");
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

}
