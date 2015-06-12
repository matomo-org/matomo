<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

use Piwik\Plugin\Manager as PluginManager;
use Piwik\Piwik;
use Piwik\Application\Environment;

/**
 * Sets the test environment.
 */
class TestingEnvironmentVariables
{
    private $behaviorOverrideProperties = array();

    public function __construct()
    {
        $overridePath = PIWIK_INCLUDE_PATH . '/tmp/testingPathOverride.json';
        if (file_exists($overridePath)) {
            $this->behaviorOverrideProperties = json_decode(file_get_contents($overridePath), true);
        }
    }

    public function __get($key)
    {
        return isset($this->behaviorOverrideProperties[$key]) ? $this->behaviorOverrideProperties[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->behaviorOverrideProperties[$key] = $value;
    }

    public function __isset($name)
    {
        return isset($this->behaviorOverrideProperties[$name]);
    }

    public function save()
    {
        @mkdir(PIWIK_INCLUDE_PATH . '/tmp');

        $overridePath = PIWIK_INCLUDE_PATH . '/tmp/testingPathOverride.json';
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
        $pluginManager = new PluginManager(new \Piwik\Application\Kernel\PluginList($settings));

        $disabledPlugins = $pluginManager->getCorePluginsDisabledByDefault();
        $disabledPlugins[] = 'LoginHttpAuth';
        $disabledPlugins[] = 'ExampleVisualization';

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
}