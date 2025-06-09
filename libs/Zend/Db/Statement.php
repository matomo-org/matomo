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
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Statement.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Db
 */
// require_once 'Zend/Db.php';

/**
 * @see Zend_Db_Statement_Interface
 */
// require_once 'Zend/Db/Statement/Interface.php';

/**
 * Abstract class to emulate a PDOStatement for native database adapters.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Db_Statement implements Zend_Db_Statement_Interface
{

    /**
     * @var resource|object The driver level statement object/resource
     */
    protected $_stmt = null;

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * The current fetch mode.
     *
     * @var integer
     */
    protected $_fetchMode = Zend_Db::FETCH_ASSOC;

    /**
     * Attributes.
     *
     * @var array
     */
    protected $_attribute = array();

    /**
     * Column result bindings.
     *
     * @var array
     */
    protected $_bindColumn = array();

    /**
     * Query parameter bindings; covers bindParam() and bindValue().
     *
     * @var array
     */
    protected $_bindParam = array();

    /**
     * SQL string split into an array at placeholders.
     *
     * @var array
     */
    protected $_sqlSplit = array();

    /**
     * Parameter placeholders in the SQL string by position in the split array.
     *
     * @var array
     */
    protected $_sqlParam = array();

    /**
     * @var Zend_Db_Profiler_Query
     */
    protected $_queryId = null;

    /**
     * Constructor for a statement.
     *
     * @param Zend_Db_Adapter_Abstract $adapter
     * @param mixed $sql Either a string or Zend_Db_Select.
     */
    public function __construct($adapter, $sql)
    {
        $this->_adapter = $adapter;
        if ($sql instanceof Zend_Db_Select) {
            $sql = $sql->assemble();
        }
        $this->_parseParameters($sql);
        $this->_prepare($sql);

        $this->_queryId = $this->_adapter->getProfiler()->queryStart($sql);
    }

    /**
     * Internal method called by abstract statment constructor to setup
     * the driver level statement
     *
     * @return void
     */
    protected function _prepare($sql)
    {
        return;
    }

    /**
     * @param string $sql
     * @return void
     */
    protected function _parseParameters($sql)
    {
        $sql = $this->_stripQuoted($sql);

        // split into text and params
        $this->_sqlSplit = preg_split('/(\?|\:[a-zA-Z0-9_]+)/',
            $sql, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

        // map params
        $this->_sqlParam = array();
        foreach ($this->_sqlSplit as $key => $val) {
            if ($val == '?') {
                if ($this->_adapter->supportsParameters('positional') === false) {
                    /**
                     * @see Zend_Db_Statement_Exception
                     */
                    // require_once 'Zend/Db/Statement/Exception.php';
                    throw new Zend_Db_Statement_Exception("Invalid bind-variable position '$val'");
                }
            } else if ($val[0] == ':') {
                if ($this->_adapter->supportsParameters('named') === false) {
                    /**
                     * @see Zend_Db_Statement_Exception
                     */
                    // require_once 'Zend/Db/Statement/Exception.php';
                    throw new Zend_Db_Statement_Exception("Invalid bind-variable name '$val'");
                }
            }
            $this->_sqlParam[] = $val;
        }

        // set up for binding
        $this->_bindParam = array();
    }

    /**
     * Remove parts of a SQL string that contain quoted strings
     * of values or identifiers.
     *
     * @param string $sql
     * @return string
     */
    protected function _stripQuoted($sql)
    {
        // get the character for delimited id quotes,
        // this is usually " but in MySQL is `
        $d = $this->_adapter->quoteIdentifier('a');
        $d = $d[0];

        // get the value used as an escaped delimited id quote,
        // e.g. \" or "" or \`
        $de = $this->_adapter->quoteIdentifier($d);
        $de = substr($de, 1, 2);
        $de = str_replace('\\', '\\\\', $de);

        // get the character for value quoting
        // this should be '
        $q = $this->_adapter->quote('a');
        $q = $q[0];

        // get the value used as an escaped quote,
        // e.g. \' or ''
        $qe = $this->_adapter->quote($q);
        $qe = substr($qe, 1, 2);
        $qe = str_replace('\\', '\\\\', $qe);

        // get a version of the SQL statement with all quoted
        // values and delimited identifiers stripped out
        // remove "foo\"bar"
        $sql = preg_replace("/$q($qe|\\\\{2}|[^$q])*$q/", '', $sql);
        // remove 'foo\'bar'
        if (!empty($q)) {
            $sql = preg_replace("/$q($qe|[^$q])*$q/", '', $sql);
        }

        return $sql;
    }

    /**
     * Bind a column of the statement result set to a PHP variable.
     *
     * @param string $column Name the column in the result set, either by
     *                       position or by name.
     * @param mixed  $param  Reference to the PHP variable containing the value.
     * @param mixed  $type   OPTIONAL
     * @return bool
     */
    public function bindColumn($column, &$param, $type = null)
    {
        $this->_bindColumn[$column] =& $param;
        return true;
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
     */
    public function bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        if (!is_int($parameter) && !is_string($parameter)) {
            /**
             * @see Zend_Db_Statement_Exception
             */
            // require_once 'Zend/Db/Statement/Exception.php';
            throw new Zend_Db_Statement_Exception('Invalid bind-variable position');
        }

        $position = null;
        if (($intval = (int) $parameter) > 0 && $this->_adapter->supportsParameters('positional')) {
            if ($intval >= 1 || $intval <= count($this->_sqlParam)) {
                $position = $intval;
            }
        } else if ($this->_adapter->supportsParameters('named')) {
            if ($parameter[0] != ':') {
                $parameter = ':' . $parameter;
            }
            if (in_array($parameter, $this->_sqlParam) !== false) {
                $position = $parameter;
            }
        }

        if ($position === null) {
            /**
             * @see Zend_Db_Statement_Exception
             */
            // require_once 'Zend/Db/Statement/Exception.php';
            throw new Zend_Db_Statement_Exception("Invalid bind-variable position '$parameter'");
        }

        // Finally we are assured that $position is valid
        $this->_bindParam[$position] =& $variable;
        return $this->_bindParam($position, $variable, $type, $length, $options);
    }

    /**
     * Binds a value to a parameter.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $value     Scalar value to bind to the parameter.
     * @param mixed $type      OPTIONAL Datatype of the parameter.
     * @return bool
     */
    public function bindValue($parameter, $value, $type = null)
    {
        return $this->bindParam($parameter, $value, $type);
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     */
    public function execute(?array $params = null)
    {
        /*
         * Simple case - no query profiler to manage.
         */
        if ($this->_queryId === null) {
            return $this->_execute($params);
        }

        /*
         * Do the same thing, but with query profiler
         * management before and after the execute.
         */
        $prof = $this->_adapter->getProfiler();
        $qp = $prof->getQueryProfile($this->_queryId);
        if ($qp->hasEnded()) {
            $this->_queryId = $prof->queryClone($qp);
            $qp = $prof->getQueryProfile($this->_queryId);
        }
        if ($params !== null) {
            $qp->bindParams($params);
        } else {
            $qp->bindParams($this->_bindParam);
        }
        $qp->start($this->_queryId);

        $retval = $this->_execute($params);

        $prof->queryEnd($this->_queryId);

        return $retval;
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param int $style OPTIONAL Fetch mode.
     * @param int $col   OPTIONAL Column number, if fetch mode is by column.
     * @return array Collection of rows, each in a format by the fetch mode.
     */
    public function fetchAll($style = null, $col = null)
    {
        $data = array();
        if ($style === Zend_Db::FETCH_COLUMN && $col === null) {
            $col = 0;
        }
        if ($col === null) {
            while ($row = $this->fetch($style)) {
                $data[] = $row;
            }
        } else {
            while (false !== ($val = $this->fetchColumn($col))) {
                $data[] = $val;
            }
        }
        return $data;
    }

    /**
     * Returns a single column from the next row of a result set.
     *
     * @param int $col OPTIONAL Position of the column to fetch.
     * @return string One value from the next row of result set, or false.
     */
    public function fetchColumn($col = 0)
    {
        $data = array();
        $col = (int) $col;
        $row = $this->fetch(Zend_Db::FETCH_NUM);
        if (!is_array($row)) {
            return false;
        }
        return $row[$col];
    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class  OPTIONAL Name of the class to create.
     * @param array  $config OPTIONAL Constructor arguments for the class.
     * @return mixed One object instance of the specified class, or false.
     */
    public function fetchObject($class = 'stdClass', array $config = array())
    {
        $obj = new $class($config);
        $row = $this->fetch(Zend_Db::FETCH_ASSOC);
        if (!is_array($row)) {
            return false;
        }
        foreach ($row as $key => $val) {
            $obj->$key = $val;
        }
        return $obj;
    }

    /**
     * Retrieve a statement attribute.
     *
     * @param string $key Attribute name.
     * @return mixed      Attribute value.
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->_attribute)) {
            return $this->_attribute[$key];
        }
    }

    /**
     * Set a statement attribute.
     *
     * @param string $key Attribute name.
     * @param mixed  $val Attribute value.
     * @return bool
     */
    public function setAttribute($key, $val)
    {
        $this->_attribute[$key] = $val;
    }

    /**
     * Set the default fetch mode for this statement.
     *
     * @param int   $mode The fetch mode.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function setFetchMode($mode)
    {
        switch ($mode) {
            case Zend_Db::FETCH_NUM:
            case Zend_Db::FETCH_ASSOC:
            case Zend_Db::FETCH_BOTH:
            case Zend_Db::FETCH_OBJ:
                $this->_fetchMode = $mode;
                break;
            case Zend_Db::FETCH_BOUND:
            default:
                $this->closeCursor();
                /**
                 * @see Zend_Db_Statement_Exception
                 */
                // require_once 'Zend/Db/Statement/Exception.php';
                throw new Zend_Db_Statement_Exception('invalid fetch mode');
                break;
        }
    }

    /**
     * Helper function to map retrieved row
     * to bound column variables
     *
     * @param array $row
     * @return bool True
     */
    public function _fetchBound($row)
    {
        foreach ($row as $key => $value) {
            // bindColumn() takes 1-based integer positions
            // but fetch() returns 0-based integer indexes
            if (is_int($key)) {
                $key++;
            }
            // set results only to variables that were bound previously
            if (isset($this->_bindColumn[$key])) {
                $this->_bindColumn[$key] = $value;
            }
        }
        return true;
    }

    /**
     * Gets the Zend_Db_Adapter_Abstract for this
     * particular Zend_Db_Statement object.
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Gets the resource or object setup by the
     * _parse
     * @return unknown_type
     */
    public function getDriverStatement()
    {
        return $this->_stmt;
    }
}
