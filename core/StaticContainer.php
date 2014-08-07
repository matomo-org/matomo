<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use DI\Container;
use DI\ContainerBuilder;
use Interop\Container\ContainerInterface;

/**
 * This class provides a static access to the container.
 *
 * It shouldn't be abused, because:
 *  - it is global state (that class makes the container a global variable)
 *  - using the container directly is the "service locator" anti-pattern (which is not dependency injection)
 */
class StaticContainer
{
    /**
     * @var Container
     */
    private static $container;

    /**
     * @return ContainerInterface
     */
    public static function getContainer()
    {
        if (self::$container === null) {
            self::$container = self::createContainer();
        }

        return self::$container;
    }

    /**
     * @link http://php-di.org/doc/container-configuration.html
     */
    private static function createContainer()
    {
        $builder = new ContainerBuilder();

        // TODO
        // $builder->setDefinitionCache($cache);
        // $builder->writeProxiesToFile(true, PIWIK_USER_PATH . '/tmp/proxies');

        // Global config
        $builder->addDefinitions(PIWIK_USER_PATH . '/configs/global.php');

        // User config
        $builder->addDefinitions(PIWIK_USER_PATH . '/configs/config.php');

        return $builder->build();
    }
}
