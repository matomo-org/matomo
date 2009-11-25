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
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Db2.php 18951 2009-11-12 16:26:19Z alexander $
 */

/**
 * @see Zend_Db_Statement
 */
require_once 'Zend/Db/Statement.php';

/**
 * Extends for DB2 native adapter.
 *
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Statement_Db2 extends Zend_Db_Statement
{

    /**
     * Column names.
     */
    protected $_keys;

    /**
     * Fetched result values.
     */
    protected $_values;

    /**
     * Prepare a statement handle.
     *
     * @param string $sql
     * @return void
     * @throws Zend_Db_Statement_Db2_Exception
     */
    public function _prepare($sql)
    {
        $connection = $this->_adapter->getConnection();

        // db2_prepare on i5 emits errors, these need to be
        // suppressed so that proper exceptions can be thrown
        $this->_stmt = @db2_prepare($connection, $sql);

        if (!$this->_stmt) {
            /**
             * @see Zend_Db_Statement_Db2_Exception
             */
            require_once 'Zend/Db/Statement/Db2/Exception.php';
            throw new Zend_Db_Statement_Db2_Exception(
                db2_stmt_errormsg(),
                db2_stmt_error()
            );
        }
    }

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $variable  Reference to PHP variable containing the value.
     * @param mixed $type      OPTIONAL Datatype of SQL parameter.
     * @param mixed $length    OPTIONAL Length of SQL parameter.
     * @param mixed $options   OPTIONAL Other options.
     * @return bool
     * @throws Zend_Db_Statement_Db2_Exception
     */
    public function _bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        if ($type === null) {
            $type = DB2_PARAM_IN;
        }

        if (isset($options['data-type'])) {
            $datatype = $options['data-type'];
        } else {
            $datatype = DB2_CHAR;
        }

        if (!db2_bind_param($this->_stmt, $position, "variable", $type, $datatype)) {
            /**
             * @see Zend_Db_Statement_Db2_Exception
             */
            require_once 'Zend/Db/Statement/Db2/Exception.php';
            throw new Zend_Db_Statement_Db2_Exception(
                db2_stmt_errormsg(),
                db2_stmt_error()
            );
        }

        return true;
    }

    /**
     * Closes the cursor, allowing the statement to be executed again.
     *
     * @return bool
     */
    public function closeCursor()
    {
        if (!$this->_stmt) {
            return false;
        }
        db2_free_stmt($this->_stmt);
        $this->_stmt = false;
        return true;
    }


    /**
     * Returns the number of columns in the result set.
     * Returns null if the statement has no result set metadata.
     *
     * @return int The number of columns.
     */
    public function columnCount()
    {
        if (!$this->_stmt) {
            return false;
        }
        return db2_num_fields($this->_stmt);
    }

    /**
     * Retrieves the error code, if any, associated with the last operation on
     * the statement handle.
     *
     * @return string error code.
     */
    public function errorCode()
    {
        if (!$this->_stmt) {
            return false;
        }

        $error = db2_stmt_error();
        if ($error === '') {
            return false;
        }

        return $error;
    }

    /**
     * Retrieves an array of error information, if any, associated with the
     * last operation on the statement handle.
     *
     * @return array
     */
    public function errorInfo()
    {
        $error = $this->errorCode();
        if ($error === false){
            return false;
        }

        /*
         * Return three-valued array like PDO.  But DB2 does not distinguish
         * between SQLCODE and native RDBMS error code, so repeat the SQLCODE.
         */
        return array(
            $error,
            $error,
            db2_stmt_errormsg()
        );
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Db2_Exception
     */
    public function _execute(array $params = null)
    {
        if (!$this->_stmt) {
            return false;
        }

        $retval = true;
        if ($params !== null) {
            $retval = @db2_execute($this->_stmt, $params);
        } else {
            $retval = @db2_execute($this->_stmt);
        }

        if ($retval === false) {
            /**
             * @see Zend_Db_Statement_Db2_Exception
             */
            require_once 'Zend/Db/Statement/Db2/Exception.php';
            throw new Zend_Db_Statement_Db2_Exception(
                db2_stmt_errormsg(),
                db2_stmt_error());
        }

        $this->_keys = array();
        if ($field_num = $this->columnCount()) {
            for ($i = 0; $i < $field_num; $i++) {
                $name = db2_field_name($this->_stmt, $i);
                $this->_keys[] = $name;
            }
        }

        $this->_values = array();
        if ($this->_keys) {
            $this->_values = array_fill(0, count($this->_keys), null);
        }

        return $retval;
    }

    /**
     * Fetches a row from the result set.
     *
     * @param int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param int $cursor OPTIONAL Absolute, relative, or other.
     * @param int $offset OPTIONAL Number for absolute or relative cursors.
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Db2_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if (!$this->_stmt) {
            return false;
        }

        if ($style === null) {
            $style = $this->_fetchMode;
        }

        switch ($style) {
            case Zend_Db::FETCH_NUM :
                $row = db2_fetch_array($this->_stmt);
                break;
            case Zend_Db::FETCH_ASSOC :
                $row = db2_fetch_assoc($this->_stmt);
                break;
            case Zend_Db::FETCH_BOTH :
                $row = db2_fetch_both($this->_stmt);
                break;
            case Zend_Db::FETCH_OBJ :
                $row = db2_fetch_object($this->_stmt);
                break;
            case Zend_Db::FETCH_BOUND:
                $row = db2_fetch_both($this->_stmt);
                if ($row !== false) {
                    return $this->_fetchBound($row);
                }
                break;
            default:
                /**
                 * @see Zend_Db_Statement_Db2_Exception
                 */
                require_once 'Zend/Db/Statement/Db2/Exception.php';
                throw new Zend_Db_Statement_Db2_Exception("Invalid fetch mode '$style' specified");
                break;
        }

        return $row;
    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class  OPTIONAL Name of the class to create.
     * @param array  $config OPTIONAL Constructor arguments for the class.
     * @return mixed One object instance of the specified class.
     */
    public function fetchObject($class = 'stdClass', array $config = array())
    {
        $obj = $this->fetch(Zend_Db::FETCH_OBJ);
        return $obj;
    }

    /**
     * Retrieves the next rowset (result set) for a SQL statement that has
     * multiple result sets.  An example is a stored procedure that returns
     * the results of multiple queries.
     *
     * @return bool
     * @throws Zend_Db_Statement_Db2_Exception
     */
    public function nextRowset()
    {
        /**
         * @see Zend_Db_Statement_Db2_Exception
         */
        require_once 'Zend/Db/Statement/Db2/Exception.php';
        throw new Zend_Db_Statement_Db2_Exception(__FUNCTION__ . '() is not implemented');
    }

    /**
     * Returns the number of rows affected by the execution of the
     * last INSERT, DELETE, or UPDATE statement executed by this
     * statement object.
     *
     * @return int     The number of rows affected.
     */
    public function rowCount()
    {
        if (!$this->_stmt) {
            return false;
        }

        $num = @db2_num_rows($this->_stmt);

        if ($num === false) {
            return 0;
        }

        return $num;
    }

     /**
     * Returns an array containing all of the result set rows.
     *
     * @param int $style OPTIONAL Fetch mode.
     * @param int $col   OPTIONAL Column number, if fetch mode is by column.
     * @return array Collection of rows, each in a format by the fetch mode.
     *
     * Behaves like parent, but if limit()
     * is used, the final result removes the extra column
     * 'zend_db_rownum'
     */
    public function fetchAll($style = null, $col = null)
    {
        $data = parent::fetchAll($style, $col);
        $results = array();
        $remove = $this->_adapter->foldCase('ZEND_DB_ROWNUM');

        foreach ($data as $row) {
            if (is_array($row) && array_key_exists($remove, $row)) {
                unset($row[$remove]);
            }
            $results[] = $row;
        }
        return $results;
    }
}
