<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-webat this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Session
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: DbTable.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';

/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';

/**
 * @see Zend_Db_Table_Row_Abstract
 */
require_once 'Zend/Db/Table/Row/Abstract.php';

/**
 * @see Zend_Config
 */
require_once 'Zend/Config.php';

/**
 * Zend_Session_SaveHandler_DbTable
 *
 * @category   Zend
 * @package    Zend_Session
 * @subpackage SaveHandler
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Session_SaveHandler_DbTable
    extends Zend_Db_Table_Abstract
    implements Zend_Session_SaveHandler_Interface
{
    const PRIMARY_ASSIGNMENT                   = 'primaryAssignment';
    const PRIMARY_ASSIGNMENT_SESSION_SAVE_PATH = 'sessionSavePath';
    const PRIMARY_ASSIGNMENT_SESSION_NAME      = 'sessionName';
    const PRIMARY_ASSIGNMENT_SESSION_ID        = 'sessionId';

    const MODIFIED_COLUMN   = 'modifiedColumn';
    const LIFETIME_COLUMN   = 'lifetimeColumn';
    const DATA_COLUMN       = 'dataColumn';

    const LIFETIME          = 'lifetime';
    const OVERRIDE_LIFETIME = 'overrideLifetime';

    const PRIMARY_TYPE_NUM         = 'PRIMARY_TYPE_NUM';
    const PRIMARY_TYPE_PRIMARYNUM  = 'PRIMARY_TYPE_PRIMARYNUM';
    const PRIMARY_TYPE_ASSOC       = 'PRIMARY_TYPE_ASSOC';
    const PRIMARY_TYPE_WHERECLAUSE = 'PRIMARY_TYPE_WHERECLAUSE';

    /**
     * Session table primary key value assignment
     *
     * @var array
     */
    protected $_primaryAssignment = null;

    /**
     * Session table last modification time column
     *
     * @var string
     */
    protected $_modifiedColumn = null;

    /**
     * Session table lifetime column
     *
     * @var string
     */
    protected $_lifetimeColumn = null;

    /**
     * Session table data column
     *
     * @var string
     */
    protected $_dataColumn = null;

    /**
     * Session lifetime
     *
     * @var int
     */
    protected $_lifetime = false;

    /**
     * Whether or not the lifetime of an existing session should be overridden
     *
     * @var boolean
     */
    protected $_overrideLifetime = false;

    /**
     * Session save path
     *
     * @var string
     */
    protected $_sessionSavePath;

    /**
     * Session name
     *
     * @var string
     */
    protected $_sessionName;

    /**
     * Constructor
     *
     * $config is an instance of Zend_Config or an array of key/value pairs containing configuration options for
     * Zend_Session_SaveHandler_DbTable and Zend_Db_Table_Abstract. These are the configuration options for
     * Zend_Session_SaveHandler_DbTable:
     *
     * primaryAssignment => (string|array) Session table primary key value assignment
     *      (optional; default: 1 => sessionId) You have to assign a value to each primary key of your session table.
     *      The value of this configuration option is either a string if you have only one primary key or an array if
     *      you have multiple primary keys. The array consists of numeric keys starting at 1 and string values. There
     *      are some values which will be replaced by session information:
     *
     *      sessionId       => The id of the current session
     *      sessionName     => The name of the current session
     *      sessionSavePath => The save path of the current session
     *
     *      NOTE: One of your assignments MUST contain 'sessionId' as value!
     *
     * modifiedColumn    => (string) Session table last modification time column
     *
     * lifetimeColumn    => (string) Session table lifetime column
     *
     * dataColumn        => (string) Session table data column
     *
     * lifetime          => (integer) Session lifetime (optional; default: ini_get('session.gc_maxlifetime'))
     *
     * overrideLifetime  => (boolean) Whether or not the lifetime of an existing session should be overridden
     *      (optional; default: false)
     *
     * @param  Zend_Config|array $config      User-provided configuration
     * @return void
     * @throws Zend_Session_SaveHandler_Exception
     */
    public function __construct($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } else if (!is_array($config)) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';

            throw new Zend_Session_SaveHandler_Exception(
                '$config must be an instance of Zend_Config or array of key/value pairs containing '
              . 'configuration options for Zend_Session_SaveHandler_DbTable and Zend_Db_Table_Abstract.');
        }

        foreach ($config as $key => $value) {
            do {
                switch ($key) {
                    case self::PRIMARY_ASSIGNMENT:
                        $this->_primaryAssignment = $value;
                        break;
                    case self::MODIFIED_COLUMN:
                        $this->_modifiedColumn = (string) $value;
                        break;
                    case self::LIFETIME_COLUMN:
                        $this->_lifetimeColumn = (string) $value;
                        break;
                    case self::DATA_COLUMN:
                        $this->_dataColumn = (string) $value;
                        break;
                    case self::LIFETIME:
                        $this->setLifetime($value);
                        break;
                    case self::OVERRIDE_LIFETIME:
                        $this->setOverrideLifetime($value);
                        break;
                    default:
                        // unrecognized options passed to parent::__construct()
                        break 2;
                }
                unset($config[$key]);
            } while (false);
        }

        parent::__construct($config);
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct()
    {
        Zend_Session::writeClose();
    }

    /**
     * Set session lifetime and optional whether or not the lifetime of an existing session should be overridden
     *
     * $lifetime === false resets lifetime to session.gc_maxlifetime
     *
     * @param int $lifetime
     * @param boolean $overrideLifetime (optional)
     * @return Zend_Session_SaveHandler_DbTable
     */
    public function setLifetime($lifetime, $overrideLifetime = null)
    {
        if ($lifetime < 0) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';
            throw new Zend_Session_SaveHandler_Exception();
        } else if (empty($lifetime)) {
            $this->_lifetime = (int) ini_get('session.gc_maxlifetime');
        } else {
            $this->_lifetime = (int) $lifetime;
        }

        if ($overrideLifetime != null) {
            $this->setOverrideLifetime($overrideLifetime);
        }

        return $this;
    }

    /**
     * Retrieve session lifetime
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->_lifetime;
    }

    /**
     * Set whether or not the lifetime of an existing session should be overridden
     *
     * @param boolean $overrideLifetime
     * @return Zend_Session_SaveHandler_DbTable
     */
    public function setOverrideLifetime($overrideLifetime)
    {
        $this->_overrideLifetime = (boolean) $overrideLifetime;

        return $this;
    }

    /**
     * Retrieve whether or not the lifetime of an existing session should be overridden
     *
     * @return boolean
     */
    public function getOverrideLifetime()
    {
        return $this->_overrideLifetime;
    }

    /**
     * Open Session
     *
     * @param string $save_path
     * @param string $name
     * @return boolean
     */
    public function open($save_path, $name)
    {
        $this->_sessionSavePath = $save_path;
        $this->_sessionName     = $name;

        return true;
    }

    /**
     * Close session
     *
     * @return boolean
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $return = '';

        $rows = call_user_func_array(array(&$this, 'find'), $this->_getPrimary($id));

        if (count($rows)) {
            if ($this->_getExpirationTime($row = $rows->current()) > time()) {
                $return = $row->{$this->_dataColumn};
            } else {
                $this->destroy($id);
            }
        }

        return $return;
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return boolean
     */
    public function write($id, $data)
    {
        $return = false;

        $data = array($this->_modifiedColumn => time(),
                      $this->_dataColumn     => (string) $data);

        $rows = call_user_func_array(array(&$this, 'find'), $this->_getPrimary($id));

        if (count($rows)) {
            $data[$this->_lifetimeColumn] = $this->_getLifetime($rows->current());

            if ($this->update($data, $this->_getPrimary($id, self::PRIMARY_TYPE_WHERECLAUSE))) {
                $return = true;
            }
        } else {
            $data[$this->_lifetimeColumn] = $this->_lifetime;

            if ($this->insert(array_merge($this->_getPrimary($id, self::PRIMARY_TYPE_ASSOC), $data))) {
                $return = true;
            }
        }

        return $return;
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id)
    {
        $return = false;

        if ($this->delete($this->_getPrimary($id, self::PRIMARY_TYPE_WHERECLAUSE))) {
            $return = true;
        }

        return $return;
    }

    /**
     * Garbage Collection
     *
     * @param int $maxlifetime
     * @return true
     */
    public function gc($maxlifetime)
    {
        $this->delete($this->getAdapter()->quoteIdentifier($this->_modifiedColumn) . ' + '
                    . $this->getAdapter()->quoteIdentifier($this->_lifetimeColumn) . ' < '
                    . $this->getAdapter()->quote(time()));

        return true;
    }

    /**
     * Calls other protected methods for individual setup tasks and requirement checks
     *
     * @return void
     */
    protected function _setup()
    {
        parent::_setup();

        $this->_setupPrimaryAssignment();
        $this->setLifetime($this->_lifetime);

        $this->_checkRequiredColumns();
    }

    /**
     * Initialize table and schema names
     *
     * @return void
     * @throws Zend_Session_SaveHandler_Exception
     */
    protected function _setupTableName()
    {
        if (empty($this->_name) && basename(($this->_name = session_save_path())) != $this->_name) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';

            throw new Zend_Session_SaveHandler_Exception('session.save_path is a path and not a table name.');
        }

        if (strpos($this->_name, '.')) {
            list($this->_schema, $this->_name) = explode('.', $this->_name);
        }
    }

    /**
     * Initialize session table primary key value assignment
     *
     * @return void
     * @throws Zend_Session_SaveHandler_Exception
     */
    protected function _setupPrimaryAssignment()
    {
        if ($this->_primaryAssignment === null) {
            $this->_primaryAssignment = array(1 => self::PRIMARY_ASSIGNMENT_SESSION_ID);
        } else if (!is_array($this->_primaryAssignment)) {
            $this->_primaryAssignment = array(1 => (string) $this->_primaryAssignment);
        } else if (isset($this->_primaryAssignment[0])) {
            array_unshift($this->_primaryAssignment, null);

            unset($this->_primaryAssignment[0]);
        }

        if (count($this->_primaryAssignment) !== count($this->_primary)) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';

            throw new Zend_Session_SaveHandler_Exception(
                "Value for configuration option '" . self::PRIMARY_ASSIGNMENT . "' must have an assignment "
              . "for each session table primary key.");
        } else if (!in_array(self::PRIMARY_ASSIGNMENT_SESSION_ID, $this->_primaryAssignment)) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';

            throw new Zend_Session_SaveHandler_Exception(
                "Value for configuration option '" . self::PRIMARY_ASSIGNMENT . "' must have an assignment "
              . "for the session id ('" . self::PRIMARY_ASSIGNMENT_SESSION_ID . "').");
        }
    }

    /**
     * Check for required session table columns
     *
     * @return void
     * @throws Zend_Session_SaveHandler_Exception
     */
    protected function _checkRequiredColumns()
    {
        if ($this->_modifiedColumn === null) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';

            throw new Zend_Session_SaveHandler_Exception(
                "Configuration must define '" . self::MODIFIED_COLUMN . "' which names the "
              . "session table last modification time column.");
        } else if ($this->_lifetimeColumn === null) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';

            throw new Zend_Session_SaveHandler_Exception(
                "Configuration must define '" . self::LIFETIME_COLUMN . "' which names the "
              . "session table lifetime column.");
        } else if ($this->_dataColumn === null) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';

            throw new Zend_Session_SaveHandler_Exception(
                "Configuration must define '" . self::DATA_COLUMN . "' which names the "
              . "session table data column.");
        }
    }

    /**
     * Retrieve session table primary key values
     *
     * @param string $id
     * @param string $type (optional; default: self::PRIMARY_TYPE_NUM)
     * @return array
     */
    protected function _getPrimary($id, $type = null)
    {
        $this->_setupPrimaryKey();

        if ($type === null) {
            $type = self::PRIMARY_TYPE_NUM;
        }

        $primaryArray = array();

        foreach ($this->_primary as $index => $primary) {
            switch ($this->_primaryAssignment[$index]) {
                case self::PRIMARY_ASSIGNMENT_SESSION_SAVE_PATH:
                    $value = $this->_sessionSavePath;
                    break;
                case self::PRIMARY_ASSIGNMENT_SESSION_NAME:
                    $value = $this->_sessionName;
                    break;
                case self::PRIMARY_ASSIGNMENT_SESSION_ID:
                    $value = (string) $id;
                    break;
                default:
                    $value = (string) $this->_primaryAssignment[$index];
                    break;
            }

            switch ((string) $type) {
                case self::PRIMARY_TYPE_PRIMARYNUM:
                    $primaryArray[$index] = $value;
                    break;
                case self::PRIMARY_TYPE_ASSOC:
                    $primaryArray[$primary] = $value;
                    break;
                case self::PRIMARY_TYPE_WHERECLAUSE:
                    $primaryArray[] = $this->getAdapter()->quoteIdentifier($primary) . ' = '
                                    . $this->getAdapter()->quote($value);
                    break;
                case self::PRIMARY_TYPE_NUM:
                default:
                    $primaryArray[] = $value;
                    break;
            }
        }

        return $primaryArray;
    }

    /**
     * Retrieve session lifetime considering Zend_Session_SaveHandler_DbTable::OVERRIDE_LIFETIME
     *
     * @param Zend_Db_Table_Row_Abstract $row
     * @return int
     */
    protected function _getLifetime(Zend_Db_Table_Row_Abstract $row)
    {
        $return = $this->_lifetime;

        if (!$this->_overrideLifetime) {
            $return = (int) $row->{$this->_lifetimeColumn};
        }

        return $return;
    }

    /**
     * Retrieve session expiration time
     *
     * @param Zend_Db_Table_Row_Abstract $row
     * @return int
     */
    protected function _getExpirationTime(Zend_Db_Table_Row_Abstract $row)
    {
        return (int) $row->{$this->_modifiedColumn} + $this->_getLifetime($row);
    }
}
