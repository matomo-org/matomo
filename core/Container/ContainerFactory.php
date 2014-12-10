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
 * Creates a configured DI container.
 */
class ContainerFactory
{
    /**
     * Optional environment config to load.
     *
     * @var bool
     */
    private $environment;

    /**
     * @param string|null $environment Optional environment config to load.
     */
    public function __construct($environment = null)
    {
        $this->environment = $environment;
    }

    /**
     * @link http://php-di.org/doc/container-configuration.html
     * @throws \Exception
     * @return Container
     */
    public function create()
    {
        if (!class_exists('DI\ContainerBuilder')) {
            throw new \Exception('DI\ContainerBuilder could not be found, maybe you are using Piwik from git and need to update Composer: php composer.phar update');
        }

        $builder = new ContainerBuilder();

        $builder->useAnnotations(false);
        $builder->setDefinitionCache(new ArrayCache());

        // INI config
        $builder->addDefinitions(new IniConfigDefinitionSource(Config::getInstance()));

        // Global config
        $builder->addDefinitions(PIWIK_USER_PATH . '/config/global.php');

        // User config
        if (file_exists(PIWIK_USER_PATH . '/config/config.php')) {
            $builder->addDefinitions(PIWIK_USER_PATH . '/config/config.php');
        }

        // Environment config
        $this->addEnvironmentConfig($builder);

        return $builder->build();
    }

    private function addEnvironmentConfig(ContainerBuilder $builder)
    {
        if (!$this->environment) {
            return;
        }

        $file = sprintf('%s/config/environment/%s.php', PIWIK_USER_PATH, $this->environment);

        $builder->addDefinitions($file);
    }
}
