<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Container;

use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Cache\ArrayCache;
use Piwik\Config;

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
     * @return Container
     */
    public static function getContainer()
    {
        if (self::$container === null) {
            self::$container = self::createContainer();
        }

        return self::$container;
    }

    public static function reset()
    {
        self::$container = null;
    }

    /**
     * @link http://php-di.org/doc/container-configuration.html
     */
    private static function createContainer()
    {
        if (!class_exists('DI\ContainerBuilder')) {
            throw new \Exception('DI\ContainerBuilder could not be found, maybe you are using Piwik from git and need to update Composer: php composer.phar update');
        }

        $builder = new ContainerBuilder();

        $builder->useAnnotations(false);

        // TODO set a better cache
        $builder->setDefinitionCache(new ArrayCache());

        // Old global INI config
        $builder->addDefinitions(new IniConfigDefinitionSource(Config::getInstance()));

        // Global config
        $builder->addDefinitions(PIWIK_USER_PATH . '/config/global.php');

        // User config
        if (file_exists(PIWIK_USER_PATH . '/config/config.php')) {
            $builder->addDefinitions(PIWIK_USER_PATH . '/config/config.php');
        }

        // Environment config
        if (self::$environment) {
            $builder->addDefinitions(sprintf(
                '%s/config/environment/%s.php',
                PIWIK_USER_PATH,
                self::$environment
            ));
        }

        return $builder->build();
    }

    public static function setEnvironment($environment)
    {
        self::$environment = $environment;
    }
}
