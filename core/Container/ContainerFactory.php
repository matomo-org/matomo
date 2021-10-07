<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Container;

use DI\Container;
use DI\ContainerBuilder;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Application\Kernel\PluginList;
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
     * Optional environment configs to load.
     *
     * @var string[]
     */
    private $environments;

    /**
     * @var array[]
     */
    private $definitions;

    /**
     * @param PluginList $pluginList
     * @param GlobalSettingsProvider $settings
     * @param string[] $environment Optional environment configs to load.
     * @param array[] $definitions
     */
    public function __construct(PluginList $pluginList, GlobalSettingsProvider $settings, array $environments = array(), array $definitions = array())
    {
        $this->pluginList = $pluginList;
        $this->settings = $settings;
        $this->environments = $environments;
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

        // INI config
        $builder->addDefinitions(new IniConfigDefinitionSource($this->settings));

        // Global config
        $builder->addDefinitions(PIWIK_DOCUMENT_ROOT . '/config/global.php');

        // Plugin configs
        $this->addPluginConfigs($builder);

        // Development config
        if ($this->isDevelopmentModeEnabled()) {
            $this->addEnvironmentConfig($builder, 'dev');
        }

        // Environment config
        foreach ($this->environments as $environment) {
            $this->addEnvironmentConfig($builder, $environment);
        }

        // User config
        if (file_exists(PIWIK_USER_PATH . '/config/config.php')
            && !in_array('test', $this->environments, true)) {
            $builder->addDefinitions(PIWIK_USER_PATH . '/config/config.php');
        }

        if (!empty($this->definitions)) {
            foreach ($this->definitions as $definitionArray) {
                $builder->addDefinitions($definitionArray);
            }
        }

        $container = $builder->build();
        $container->set('Piwik\Application\Kernel\PluginList', $this->pluginList);
        $container->set('Piwik\Application\Kernel\GlobalSettingsProvider', $this->settings);

        return $container;
    }

    private function addEnvironmentConfig(ContainerBuilder $builder, $environment)
    {
        if (!$environment) {
            return;
        }

        $file = sprintf('%s/config/environment/%s.php', PIWIK_USER_PATH, $environment);

        if (file_exists($file)) {
            $builder->addDefinitions($file);
        }

        // add plugin environment configs
        $plugins = $this->pluginList->getActivatedPlugins();

        if ($this->shouldSortPlugins()) {
            $plugins = $this->sortPlugins($plugins);
        }

        foreach ($plugins as $plugin) {
            $baseDir = Manager::getPluginDirectory($plugin);

            $environmentFile = $baseDir . '/config/' . $environment . '.php';
            if (file_exists($environmentFile)) {
                $builder->addDefinitions($environmentFile);
            }
        }
    }

    private function addPluginConfigs(ContainerBuilder $builder)
    {
        $plugins = $this->pluginList->getActivatedPlugins();

        if ($this->shouldSortPlugins()) {
            $plugins = $this->sortPlugins($plugins);
        }

        foreach ($plugins as $plugin) {
            $baseDir = Manager::getPluginDirectory($plugin);

            $file = $baseDir . '/config/config.php';
            if (file_exists($file)) {
                $builder->addDefinitions($file);
            }
        }
    }

    /**
     * This method is required for Matomo Cloud to allow for custom sorting of plugin order
     *
     * @return bool
     */
    private function shouldSortPlugins()
    {
        return isset($GLOBALS['MATOMO_SORT_PLUGINS']) && is_callable($GLOBALS['MATOMO_SORT_PLUGINS']);
    }

    /**
     * @param array $plugins
     * @return array
     */
    private function sortPlugins(array $plugins)
    {
        return call_user_func($GLOBALS['MATOMO_SORT_PLUGINS'], $plugins);
    }

    private function isDevelopmentModeEnabled()
    {
        $section = $this->settings->getSection('Development');
        return (bool) @$section['enabled']; // TODO: code redundancy w/ Development. hopefully ok for now.
    }
}
