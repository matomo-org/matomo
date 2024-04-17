<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

use Piwik\Plugin\Manager as PluginManager;

/**
 * Sets the test environment.
 *
 * For testing purposes, we don't want this class to reference PIWIK_INCLUDE_PATH or other constants.
 */
class TestingEnvironmentVariables
{
    private $behaviorOverrideProperties = array();

    public function __construct()
    {
        $this->reload();
    }

    public function &__get($key)
    {
        $result =& $this->behaviorOverrideProperties[$key];
        return $result;
    }

    public function __set($key, $value)
    {
        $this->behaviorOverrideProperties[$key] = $value;
    }

    public function __isset($name)
    {
        return isset($this->behaviorOverrideProperties[$name]);
    }

    public function getProperties()
    {
        return $this->behaviorOverrideProperties;
    }

    /**
     * Overrides a config entry.
     *
     * You can use this method either to set one specific config value `overrideConfig(group, name, value)`
     * or you can set a whole group of values `overrideConfig(group, valueObject)`.
     *
     * @param string $group  Eg 'General', 'log', or any other config group name
     * @param string|array $name  The name of the config within the group you want to overwrite. If you want to overwrite
     *                            the whole group just leave `$value` empty and instead provide an array of key/value pairs
     *                            here.
     * @param string|int|array|null $value  The value you want to set for the given config.
     * @throws \Exception if no name is set
     */
    public function overrideConfig($group, $name, $value = null)
    {
        if (empty($name) && !is_array($name)) {
            throw new \Exception('No name set that needs to be overwritten');
        }

        $config = $this->configOverride;

        if (empty($config)) {
            $config = array();
        }

        if (!isset($value) && is_array($name)) {
            $config[$group] = $name;
            $this->configOverride = $config;
            return;
        }

        if (!isset($config[$group])) {
            $config[$group] = array();
        }

        $config[$group][$name] = $value;
        $this->configOverride = $config;
    }

    public function removeOverriddenConfig($group, $name)
    {
        unset($this->configOverride[$group][$name]);
    }

    public function save()
    {
        $includePath = __DIR__ . '/../../..';

        if (!file_exists($includePath . '/tmp')) {
            mkdir($includePath . '/tmp');
        }

        $overridePath = $includePath . '/tmp/testingPathOverride.json';
        file_put_contents($overridePath, json_encode($this->behaviorOverrideProperties));
    }

    public function delete()
    {
        $this->behaviorOverrideProperties = array();
        $this->save();
    }

    public function getCoreAndSupportedPlugins()
    {
        $settings = new \Piwik\Application\Kernel\GlobalSettingsProvider();
        $pluginList = new \Piwik\Application\Kernel\PluginList($settings);
        $pluginManager = new PluginManager($pluginList);

        $disabledPlugins = $pluginList->getCorePluginsDisabledByDefault();
        $disabledPlugins[] = 'LoginLdap';
        $disabledPlugins[] = 'MarketingCampaignsReporting';
        $disabledPlugins[] = 'ExampleVisualization';
        $disabledPlugins[] = 'DeviceDetectorCache';
        $disabledPlugins[] = 'Provider';

        $disabledPlugins = array_diff($disabledPlugins, array(
            'DBStats', 'ExampleUI', 'ExampleCommand', 'ExampleSettingsPlugin'
        ));

        $plugins = array_filter($pluginManager->readPluginsDirectory(), function ($pluginName) use ($disabledPlugins, $pluginManager) {
            if (in_array($pluginName, $disabledPlugins)) {
                return false;
            }

            return $pluginManager->isPluginBundledWithCore($pluginName)
            || $pluginManager->isPluginOfficialAndNotBundledWithCore($pluginName);
        });

        sort($plugins);

        return $plugins;
    }

    public function reload()
    {
        $overridePath = __DIR__ . '/../../../tmp/testingPathOverride.json';
        if (file_exists($overridePath)) {
            $this->behaviorOverrideProperties = json_decode(file_get_contents($overridePath), true);
        }
    }
}
