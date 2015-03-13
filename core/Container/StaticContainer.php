<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Container;

use DI\Container;

/**
 * This class provides a static access to the container.
 *
 * @deprecated This class is introduced only to keep BC with the current static architecture. It will be removed in 3.0.
 *     - it is global state (that class makes the container a global variable)
 *     - using the container directly is the "service locator" anti-pattern (which is not dependency injection)
 */
class StaticContainer
{
    /**
     * @var Container
     */
    private static $container;

    /**
     * Optional environment config to load.
     *
     * @var bool
     */
    private static $environment;

    /**
     * Definitions to register in the container.
     *
     * @var array
     */
    private static $definitions = array();

    /**
     * @return Container
     */
    public static function getContainer()
    {
        if (self::$container === null) {
            self::$container = self::createContainer();
        }

        return self::$container;
    }

    public static function clearContainer()
    {
        self::$container = null;
    }

    /**
     * Only use this in tests.
     *
     * @param Container $container
     */
    public static function set(Container $container)
    {
        self::$container = $container;
    }

    /**
     * @link http://php-di.org/doc/container-configuration.html
     */
    private static function createContainer()
    {
        $containerFactory = new ContainerFactory(self::$environment, self::$definitions);
        return $containerFactory->create();
    }

    /**
     * Set the application environment (cli, test, …) or null for the default one.
     *
     * @param string|null $environment
     */
    public static function setEnvironment($environment)
    {
        self::$environment = $environment;
    }

    public static function addDefinitions(array $definitions)
    {
        self::$definitions = $definitions;
    }

    /**
     * Proxy to Container::get()
     *
     * @param string $name Container entry name.
     * @return mixed
     * @throws \DI\NotFoundException
     */
    public static function get($name)
    {
        return self::getContainer()->get($name);
    }
}
