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
use Piwik\Development;
use Piwik\Plugin\Manager;

/**
 * Creates a configured DI container.
 */
class ContainerFactory
{
    /**
     * Optional environment config to load.
     *
     * @var string|null
     */
    private $environment;

    /**
     * @var array
     */
    private $definitions;

    /**
     * @param string|null $environment Optional environment config to load.
     */
    public function __construct($environment = null, array $definitions = array())
    {
        $this->environment = $environment;
        $this->definitions = $definitions;
    }

    /**
     * @link http://php-di.org/doc/container-configuration.html
     * @throws \Exception
     * @return Container
     */
    public function create()
    {
        $builder = new ContainerBuilder();

        $builder->useAnnotations(false);
        $builder->setDefinitionCache(new ArrayCache());

        // INI config
        $builder->addDefinitions(new IniConfigDefinitionSource(Config::getInstance()));

        // Global config
        $builder->addDefinitions(PIWIK_USER_PATH . '/config/global.php');

        // Plugin configs
        $this->addPluginConfigs($builder);

        // Development config
        if (Development::isEnabled()) {
            $builder->addDefinitions(PIWIK_USER_PATH . '/config/environment/dev.php');
        }

        // User config
        if (file_exists(PIWIK_USER_PATH . '/config/config.php')) {
            $builder->addDefinitions(PIWIK_USER_PATH . '/config/config.php');
        }

        // Environment config
        $this->addEnvironmentConfig($builder);

        if (!empty($this->definitions)) {
            $builder->addDefinitions($this->definitions);
        }

        return $builder->build();
    }

    private function addEnvironmentConfig(ContainerBuilder $builder)
    {
        if (!$this->environment) {
            return;
        }

        $file = sprintf('%s/config/environment/%s.php', PIWIK_USER_PATH, $this->environment);

        if (file_exists($file)) {
            $builder->addDefinitions($file);
        }
    }

    private function addPluginConfigs(ContainerBuilder $builder)
    {
        $plugins = Manager::getInstance()->getActivatedPluginsFromConfig();

        foreach ($plugins as $plugin) {
            $baseDir = Manager::getPluginsDirectory() . $plugin;

            $file = $baseDir . '/config/config.php';
            if (file_exists($file)) {
                $builder->addDefinitions($file);
            }

            $environmentFile = $baseDir . '/config/' . $this->environment . '.php';
            if (file_exists($environmentFile)) {
                $builder->addDefinitions($environmentFile);
            }
        }
    }
}
