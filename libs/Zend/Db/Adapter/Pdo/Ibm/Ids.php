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
 * @version    $Id: Ids.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** @see Zend_Db_Adapter_Pdo_Ibm */
// require_once 'Zend/Db/Adapter/Pdo/Ibm.php';

/** @see Zend_Db_Statement_Pdo_Ibm */
// require_once 'Zend/Db/Statement/Pdo/Ibm.php';


/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Pdo_Ibm_Ids
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * Construct the data server class.
     *
     * It will be used to generate non-generic SQL
     * for a particular data server
     *
     * @param Zend_Db_Adapter_Abstract $adapter
     */
    public function __construct($adapter)
    {
        $this->_adapter = $adapter;
    }

    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        $sql = "SELECT tabname "
        . "FROM systables ";

        return $this->_adapter->fetchCol($sql);
    }

    /**
     * IDS catalog lookup for describe table
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        // this is still a work in progress

        $sql= "SELECT DISTINCT t.owner, t.tabname, c.colname, c.colno, c.coltype,
               d.default, c.collength, t.tabid
               FROM syscolumns c
               JOIN systables t ON c.tabid = t.tabid
               LEFT JOIN sysdefaults d ON c.tabid = d.tabid AND c.colno = d.colno
               WHERE "
                . $this->_adapter->quoteInto('UPPER(t.tabname) = UPPER(?)', $tableName);
        if ($schemaName) {
            $sql .= $this->_adapter->quoteInto(' AND UPPER(t.owner) = UPPER(?)', $schemaName);
        }
        $sql .= " ORDER BY c.colno";

        $desc = array();
        $stmt = $this->_adapter->query($sql);

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
        $length         = 6;
        $tabid          = 7;

        $primaryCols = null;

        foreach ($result as $key => $row) {
            $primary = false;
            $primaryPosition = null;

            if (!$primaryCols) {
                $primaryCols = $this->_getPrimaryInfo($row[$tabid]);
            }

            if (array_key_exists($row[$colno], $primaryCols)) {
                $primary = true;
                $primaryPosition = $primaryCols[$row[$colno]];
            }

            $identity = false;
            if ($row[$typename] == 6 + 256 ||
                $row[$typename] == 18 + 256) {
                $identity = true;
            }

            $desc[$this->_adapter->foldCase($row[$colname])] = array (
                'SCHEMA_NAME'       => $this->_adapter->foldCase($row[$tabschema]),
                'TABLE_NAME'        => $this->_adapter->foldCase($row[$tabname]),
                'COLUMN_NAME'       => $this->_adapter->foldCase($row[$colname]),
                'COLUMN_POSITION'   => $row[$colno],
                'DATA_TYPE'         => $this->_getDataType($row[$typename]),
                'DEFAULT'           => $row[$default],
                'NULLABLE'          => (bool) !($row[$typename] - 256 >= 0),
                'LENGTH'            => $row[$length],
                'SCALE'             => ($row[$typename] == 5 ? $row[$length]&255 : 0),
                'PRECISION'         => ($row[$typename] == 5 ? (int)($row[$length]/256) : 0),
                'UNSIGNED'          => false,
                'PRIMARY'           => $primary,
                'PRIMARY_POSITION'  => $primaryPosition,
                'IDENTITY'          => $identity
            );
        }

        return $desc;
    }

    /**
     * Map number representation of a data type
     * to a string
     *
     * @param int $typeNo
     * @return string
     */
    protected function _getDataType($typeNo)
    {
        $typemap = array(
            0       => "CHAR",
            1       => "SMALLINT",
            2       => "INTEGER",
            3       => "FLOAT",
            4       => "SMALLFLOAT",
            5       => "DECIMAL",
            6       => "SERIAL",
            7       => "DATE",
            8       => "MONEY",
            9       => "NULL",
            10      => "DATETIME",
            11      => "BYTE",
            12      => "TEXT",
            13      => "VARCHAR",
            14      => "INTERVAL",
            15      => "NCHAR",
            16      => "NVARCHAR",
            17      => "INT8",
            18      => "SERIAL8",
            19      => "SET",
            20      => "MULTISET",
            21      => "LIST",
            22      => "Unnamed ROW",
            40      => "Variable-length opaque type",
            4118    => "Named ROW"
        );

        if ($typeNo - 256 >= 0) {
            $typeNo = $typeNo - 256;
        }

        return $typemap[$typeNo];
    }

    /**
     * Helper method to retrieve primary key column
     * and column location
     *
     * @param int $tabid
     * @return array
     */
    protected function _getPrimaryInfo($tabid)
    {
        $sql = "SELECT i.part1, i.part2, i.part3, i.part4, i.part5, i.part6,
                i.part7, i.part8, i.part9, i.part10, i.part11, i.part12,
                i.part13, i.part14, i.part15, i.part16
                FROM sysindexes i
                JOIN sysconstraints c ON c.idxname = i.idxname
                WHERE i.tabid = " . $tabid . " AND c.constrtype = 'P'";

        $stmt = $this->_adapter->query($sql);
        $results = $stmt->fetchAll();

        $cols = array();

        // this should return only 1 row
        // unless there is no primary key,
        // in which case, the empty array is returned
        if ($results) {
            $row = $results[0];
        } else {
            return $cols;
        }

        $position = 0;
        foreach ($row as $key => $colno) {
            $position++;
            if ($colno == 0) {
                return $cols;
            } else {
                $cols[$colno] = $position;
            }
        }
    }

    /**
     * Adds an IDS-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param integer $count
     * @param integer $offset OPTIONAL
     * @throws Zend_Db_Adapter_Exception
     * @return string
     */
    public function limit($sql, $count, $offset = 0)
    {
        $count = intval($count);
        if ($count < 0) {
            /** @see Zend_Db_Adapter_Exception */
            // require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("LIMIT argument count=$count is not valid");
        } else if ($count == 0) {
              $limit_sql = str_ireplace("SELECT", "SELECT * FROM (SELECT", $sql);
              $limit_sql .= ") WHERE 0 = 1";
        } else {
            $offset = intval($offset);
            if ($offset < 0) {
                /** @see Zend_Db_Adapter_Exception */
                // require_once 'Zend/Db/Adapter/Exception.php';
                throw new Zend_Db_Adapter_Exception("LIMIT argument offset=$offset is not valid");
            }
            if ($offset == 0) {
                $limit_sql = str_ireplace("SELECT", "SELECT FIRST $count", $sql);
            } else {
                $limit_sql = str_ireplace("SELECT", "SELECT SKIP $offset LIMIT $count", $sql);
            }
        }
        return $limit_sql;
    }

    /**
     * IDS-specific last sequence id
     *
     * @param string $sequenceName
     * @return integer
     */
    public function lastSequenceId($sequenceName)
    {
        $sql = 'SELECT '.$this->_adapter->quoteIdentifier($sequenceName).'.CURRVAL FROM '
               .'systables WHERE tabid = 1';
        $value = $this->_adapter->fetchOne($sql);
        return $value;
    }

     /**
     * IDS-specific sequence id value
     *
     *  @param string $sequenceName
     *  @return integer
     */
    public function nextSequenceId($sequenceName)
    {
        $sql = 'SELECT '.$this->_adapter->quoteIdentifier($sequenceName).'.NEXTVAL FROM '
               .'systables WHERE tabid = 1';
        $value = $this->_adapter->fetchOne($sql);
        return $value;
    }
}
