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
 * @package    Zend_Registry
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Registry.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * Generic storage class helps to manage global data.
 *
 * @category   Zend
 * @package    Zend_Registry
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Registry extends ArrayObject
{
    /**
     * Class name of the singleton registry object.
     * @var string
     */
    private static $_registryClassName = 'Zend_Registry';

    /**
     * Registry object provides storage for shared objects.
     * @var Zend_Registry
     */
    private static $_registry = null;

    /**
     * Retrieves the default registry instance.
     *
     * @return Zend_Registry
     */
    public static function getInstance()
    {
        if (self::$_registry === null) {
            self::init();
        }

        return self::$_registry;
    }

    /**
     * Set the default registry instance to a specified instance.
     *
     * @param Zend_Registry $registry An object instance of type Zend_Registry,
     *   or a subclass.
     * @return void
     * @throws Zend_Exception if registry is already initialized.
     */
    public static function setInstance(Zend_Registry $registry)
    {
        if (self::$_registry !== null) {
            // require_once 'Zend/Exception.php';
            throw new Zend_Exception('Registry is already initialized');
        }

        self::setClassName(get_class($registry));
        self::$_registry = $registry;
    }

    /**
     * Initialize the default registry instance.
     *
     * @return void
     */
    protected static function init()
    {
        self::setInstance(new self::$_registryClassName());
    }

    /**
     * Set the class name to use for the default registry instance.
     * Does not affect the currently initialized instance, it only applies
     * for the next time you instantiate.
     *
     * @param string $registryClassName
     * @return void
     * @throws Zend_Exception if the registry is initialized or if the
     *   class name is not valid.
     */
    public static function setClassName($registryClassName = 'Zend_Registry')
    {
        if (self::$_registry !== null) {
            // require_once 'Zend/Exception.php';
            throw new Zend_Exception('Registry is already initialized');
        }

        if (!is_string($registryClassName)) {
            // require_once 'Zend/Exception.php';
            throw new Zend_Exception("Argument is not a class name");
        }

        /**
         * @see Zend_Loader
         */
        if (!class_exists($registryClassName)) {
            // require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($registryClassName);
        }

        self::$_registryClassName = $registryClassName;
    }

    /**
     * Unset the default registry instance.
     * Primarily used in tearDown() in unit tests.
     * @returns void
     */
    public static function _unsetInstance()
    {
        self::$_registry = null;
    }

    /**
     * getter method, basically same as offsetGet().
     *
     * This method can be called from an object of type Zend_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index - get the value associated with $index
     * @return mixed
     * @throws Zend_Exception if no entry is registerd for $index.
     */
    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            // require_once 'Zend/Exception.php';
//            debug_print_backtrace();
            throw new Zend_Exception("No entry is registered for key '$index'" );
        }

        return $instance->offsetGet($index);
    }

    /**
     * setter method, basically same as offsetSet().
     *
     * This method can be called from an object of type Zend_Registry, or it
     * can be called statically.  In the latter case, it uses the default
     * static instance stored in the class.
     *
     * @param string $index The location in the ArrayObject in which to store
     *   the value.
     * @param mixed $value The object to store in the ArrayObject.
     * @return void
     */
    public static function set($index, $value)
    {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    /**
     * Returns TRUE if the $index is a named value in the registry,
     * or FALSE if $index was not found in the registry.
     *
     * @param  string $index
     * @return boolean
     */
    public static function isRegistered($index)
    {
        if (self::$_registry === null) {
            return false;
        }
        return self::$_registry->offsetExists($index);
    }

    /**
     * Constructs a parent ArrayObject with default
     * ARRAY_AS_PROPS to allow acces as an object
     *
     * @param array $array data array
     * @param integer $flags ArrayObject flags
     */
    public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS)
    {
        parent::__construct($array, $flags);
    }

    /**
     * @param string $index
     * @returns mixed
     *
     * Workaround for http://bugs.php.net/bug.php?id=40442 (ZF-960).
     */
    public function offsetExists($index)
    {
        return array_key_exists($index, $this);
    }

}
