<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

/**
 * The singleton base class restricts the instantiation of derived classes to one object only.
 *
 * All plugin APIs are singletons and thus extend this class.
 *
 * @api
 */
class Singleton
{
    protected static $instances;

    protected function __construct()
    {
    }

    final private function __clone()
    {
    }

    /**
     * Returns the singleton instance for the derived class. If the singleton instance
     * has not been created, this method will create it.
     *
     * @return static
     */
    public static function getInstance()
    {
        $class = get_called_class();

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new $class;
        }
        return self::$instances[$class];
    }

    /**
     * Used in tests only
     * @ignore
     */
    public static function unsetInstance()
    {
        $class = get_called_class();
        unset(self::$instances[$class]);
    }

    /**
     * Sets the singleton instance. For testing purposes.
     * @ignore
     */
    public static function setSingletonInstance($instance)
    {
        $class = get_called_class();
        self::$instances[$class] = $instance;
    }

    /**
     * @ignore
     */
    public static function clearAll()
    {
        self::$instances = array();
    }
}
