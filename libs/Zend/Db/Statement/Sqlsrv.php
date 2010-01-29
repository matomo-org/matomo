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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Sqlsrv.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Db_Statement
 */
require_once 'Zend/Db/Statement.php';

/**
 * Extends for Microsoft SQL Server Driver for PHP
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Statement_Sqlsrv extends Zend_Db_Statement
{

    /**
     * The connection_stmt object original string.
     */
    protected $_originalSQL;

    /**
     * Column names.
     */
    protected $_keys;

    /**
     * Query executed
     */
    protected $_executed = false;

    /**
     * Prepares statement handle
     *
     * @param string $sql
     * @return void
     * @throws Zend_Db_Statement_Sqlsrv_Exception
     */
    protected function _prepare($sql)
    {
        $connection = $this->_adapter->getConnection();

        $this->_stmt = sqlsrv_prepare($connection, $sql);

        if (!$this->_stmt) {
            require_once 'Zend/Db/Statement/Sqlsrv/Exception.php';
            throw new Zend_Db_Statement_Sqlsrv_Exception(sqlsrv_errors());
        }

        $this->_originalSQL = $sql;
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
     * @throws Zend_Db_Statement_Exception
     */
    protected function _bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        //Sql server doesn't support bind by name
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

        sqlsrv_free_stmt($this->_stmt);
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
        if ($this->_stmt && $this->_executed) {
            return sqlsrv_num_fields($this->_stmt);
        }

        return 0;
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

        $error = sqlsrv_errors();
        if (!$error) {
            return false;
        }

        return $error[0]['code'];
    }


    /**
     * Retrieves an array of error information, if any, associated with the
     * last operation on the statement handle.
     *
     * @return array
     */
    public function errorInfo()
    {
        if (!$this->_stmt) {
            return false;
        }

        $error = sqlsrv_errors();
        if (!$error) {
            return false;
        }

        return array(
            $error[0]['code'],
            $error[0]['message'],
        );
    }


    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function _execute(array $params = null)
    {
        $connection = $this->_adapter->getConnection();
        if (!$this->_stmt) {
            return false;
        }

        if ($params !== null) {
            if (!is_array($params)) {
                $params = array($params);
            }
            $error = false;

            // make all params passed by reference
            $params_ = array();
            $temp    = array();
            $i       = 1;
            foreach ($params as $param) {
                $temp[$i]  = $param;
                $params_[] = &$temp[$i];
                $i++;
            }
            $params = $params_;
        }

        $this->_stmt = sqlsrv_query($connection, $this->_originalSQL, $params);

        if (!$this->_stmt) {
            require_once 'Zend/Db/Statement/Sqlsrv/Exception.php';
            throw new Zend_Db_Statement_Sqlsrv_Exception(sqlsrv_errors());
        }

        $this->_executed = true;

        return (!$this->_stmt);
    }

    /**
     * Fetches a row from the result set.
     *
     * @param  int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param  int $cursor OPTIONAL Absolute, relative, or other.
     * @param  int $offset OPTIONAL Number for absolute or relative cursors.
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if (!$this->_stmt) {
            return false;
        }

        if (null === $style) {
            $style = $this->_fetchMode;
        }

        $values = sqlsrv_fetch_array($this->_stmt, SQLSRV_FETCH_ASSOC);

        if (!$values && (null !== $error = sqlsrv_errors())) {
            require_once 'Zend/Db/Statement/Sqlsrv/Exception.php';
            throw new Zend_Db_Statement_Sqlsrv_Exception($error);
        }

        if (null === $values) {
            return null;
        }

        if (!$this->_keys) {
            foreach ($values as $key => $value) {
                $this->_keys[] = $this->_adapter->foldCase($key);
            }
        }

        $values = array_values($values);

        $row = false;
        switch ($style) {
            case Zend_Db::FETCH_NUM:
                $row = $values;
                break;
            case Zend_Db::FETCH_ASSOC:
                $row = array_combine($this->_keys, $values);
                break;
            case Zend_Db::FETCH_BOTH:
                $assoc = array_combine($this->_keys, $values);
                $row   = array_merge($values, $assoc);
                break;
            case Zend_Db::FETCH_OBJ:
                $row = (object) array_combine($this->_keys, $values);
                break;
            case Zend_Db::FETCH_BOUND:
                $assoc = array_combine($this->_keys, $values);
                $row   = array_merge($values, $assoc);
                $row   = $this->_fetchBound($row);
                break;
            default:
                require_once 'Zend/Db/Statement/Sqlsrv/Exception.php';
                throw new Zend_Db_Statement_Sqlsrv_Exception("Invalid fetch mode '$style' specified");
                break;
        }

        return $row;
    }

    /**
     * Returns a single column from the next row of a result set.
     *
     * @param int $col OPTIONAL Position of the column to fetch.
     * @return string
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchColumn($col = 0)
    {
        if (!$this->_stmt) {
            return false;
        }

        if (!sqlsrv_fetch($this->_stmt)) {
            if (null !== $error = sqlsrv_errors()) {
                require_once 'Zend/Db/Statement/Sqlsrv/Exception.php';
                throw new Zend_Db_Statement_Sqlsrv_Exception($error);
            }

            // If no error, there is simply no record
            return false;
        }

        $data = sqlsrv_get_field($this->_stmt, $col); //0-based
        if ($data === false) {
            require_once 'Zend/Db/Statement/Sqlsrv/Exception.php';
            throw new Zend_Db_Statement_Sqlsrv_Exception(sqlsrv_errors());
        }

        return $data;
    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class  OPTIONAL Name of the class to create.
     * @param array  $config OPTIONAL Constructor arguments for the class.
     * @return mixed One object instance of the specified class.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchObject($class = 'stdClass', array $config = array())
    {
        if (!$this->_stmt) {
            return false;
        }

        $obj = sqlsrv_fetch_object($this->_stmt);

        if ($error = sqlsrv_errors()) {
            require_once 'Zend/Db/Statement/Sqlsrv/Exception.php';
            throw new Zend_Db_Statement_Sqlsrv_Exception($error);
        }

        /* @todo XXX handle parameters */

        if (null === $obj) {
            return false;
        }

        return $obj;
    }

    /**
     * Returns metadata for a column in a result set.
     *
     * @param int $column
     * @return mixed
     * @throws Zend_Db_Statement_Sqlsrv_Exception
     */
    public function getColumnMeta($column)
    {
        $fields = sqlsrv_field_metadata($this->_stmt);

        if (!$fields) {
            throw new Zend_Db_Statement_Sqlsrv_Exception('Column metadata can not be fetched');
        }

        if (!isset($fields[$column])) {
            throw new Zend_Db_Statement_Sqlsrv_Exception('Column index does not exist in statement');
        }

        return $fields[$column];
    }

    /**
     * Retrieves the next rowset (result set) for a SQL statement that has
     * multiple result sets.  An example is a stored procedure that returns
     * the results of multiple queries.
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function nextRowset()
    {
        if (sqlsrv_next_result($this->_stmt) === false) {
            require_once 'Zend/Db/Statement/Sqlsrv/Exception.php';
            throw new Zend_Db_Statement_Sqlsrv_Exception(sqlsrv_errors());
        }

        //else - moved to next (or there are no more rows)
    }

    /**
     * Returns the number of rows affected by the execution of the
     * last INSERT, DELETE, or UPDATE statement executed by this
     * statement object.
     *
     * @return int     The number of rows affected.
     * @throws Zend_Db_Statement_Exception
     */
    public function rowCount()
    {
        if (!$this->_stmt) {
            return false;
        }

        if (!$this->_executed) {
            return 0;
        }

        $num_rows = sqlsrv_rows_affected($this->_stmt);

        // Strict check is necessary; 0 is a valid return value
        if ($num_rows === false) {
            require_once 'Zend/Db/Statement/Sqlsrv/Exception.php';
            throw new Zend_Db_Statement_Sqlsrv_Exception(sqlsrv_errors());
        }

        return $num_rows;
    }
}
