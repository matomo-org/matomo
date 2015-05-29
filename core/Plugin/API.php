<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugin;

use Piwik\Container\StaticContainer;
use Psr\Log\LoggerInterface;

/**
 * The base class of all API singletons.
 *
 * Plugins that want to expose functionality through the Reporting API should create a class
 * that extends this one. Every public method in that class that is not annotated with **@ignore**
 * will be callable through Piwik's Web API.
 *
 * _Note: If your plugin calculates and stores reports, they should be made available through the API._
 *
 * ### Examples
 *
 * **Defining an API for a plugin**
 *
 *     class API extends \Piwik\Plugin\API
 *     {
 *         public function myMethod($idSite, $period, $date, $segment = false)
 *         {
 *             $dataTable = // ... get some data ...
 *             return $dataTable;
 *         }
 *     }
 *
 * **Linking to an API method**
 *
 *     <a href="?module=API&method=MyPlugin.myMethod&idSite=1&period=day&date=2013-10-23">Link</a>
 *
 * @api
 */
abstract class API
{
    private static $instances;

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
            $container = StaticContainer::getContainer();

            $refl = new \ReflectionClass($class);

            if (!$refl->getConstructor() || $refl->getConstructor()->isPublic()) {
                self::$instances[$class] = $container->get($class);
            } else {
                /** @var LoggerInterface $logger */
                $logger = $container->get('Psr\Log\LoggerInterface');

                // BC with API defining a protected constructor
                $logger->notice('The API class {class} defines a protected constructor which is deprecated, make the constructor public instead', array('class' => $class));
                self::$instances[$class] = new $class;
            }
        }

        return self::$instances[$class];
    }

    /**
     * Used in tests only
     * @ignore
     * @deprecated
     */
    public static function unsetInstance()
    {
        $class = get_called_class();
        unset(self::$instances[$class]);
    }

    /**
     * Sets the singleton instance. For testing purposes.
     * @ignore
     * @deprecated
     */
    public static function setSingletonInstance($instance)
    {
        $class = get_called_class();
        self::$instances[$class] = $instance;
    }
}
