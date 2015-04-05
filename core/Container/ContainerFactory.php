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
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Application\Kernel\PluginList;
use Piwik\Config\IniFileChainFactory;
use Piwik\Development;
use Piwik\Plugin\Manager;

/**
 * Creates a configured DI container.
 */
class ContainerFactory
{
    /**
     * @var PluginList
     */
    private $pluginList;

    /**
     * @var GlobalSettingsProvider
     */
    private $settings;

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
     * @param PluginList $pluginList
     * @param GlobalSettingsProvider $settings
     * @param string|null $environment Optional environment config to load.
     * @param array $definitions
     */
    public function __construct(PluginList $pluginList, GlobalSettingsProvider $settings, $environment = null, array $definitions = array())
    {
        $this->pluginList = $pluginList;
        $this->settings = $settings;
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
        $builder->addDefinitions(new IniConfigDefinitionSource($this->settings));

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

        $container = $builder->build();
        $container->set('Piwik\Application\Kernel\PluginList', $this->pluginList);
        $container->set('Piwik\Application\Kernel\GlobalSettingsProvider', $this->settings);

        return $container;
    }

    private function addEnvironmentConfig(ContainerBuilder $builder)
    {
        if (!$this->environment) {
            return;
        }

        $file = sprintf('%s/config/environment/%s.php', PIWIK_USER_PATH, $this->environment);

        $builder->addDefinitions($file);
    }

    private function addPluginConfigs(ContainerBuilder $builder)
    {
        $plugins = $this->pluginList->getActivatedPlugins();

        foreach ($plugins as $plugin) {
            $file = Manager::getPluginsDirectory() . $plugin . '/config/config.php';

            if (! file_exists($file)) {
                continue;
            }

            $builder->addDefinitions($file);
        }
    }
}
