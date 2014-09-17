<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugin;

use Piwik\Log;
use Piwik\Plugin\Manager as PluginManager;
use Exception;

/**
 * Factory class with methods to find and instantiate Plugin components.
 */
class ComponentFactory
{
    /**
     * Create a component instance that exists within a specific plugin. Uses the component's
     * unqualified class name and expected base type.
     *
     * This method will only create a class if it is located within the component type's
     * associated subdirectory.
     *
     * @param string $pluginName The name of the plugin the component is expected to belong to,
     *                           eg, `'UserSettings'`.
     * @param string $componentClassSimpleName The component's class name w/o namespace, eg,
     *                                         `"GetKeywords"`.
     * @param string $componentTypeClass The fully qualified class name of the component type, eg,
     *                                   `"Piwik\Plugin\Report"`.
     * @return mixed|null A new instance of the desired component or null if not found. If the
     *                    plugin is not loaded or activated or the component is not located in
     *                    in the sub-namespace specified by `$componentTypeClass::COMPONENT_SUBNAMESPACE`,
     *                    this method will return null.
     */
    public static function factory($pluginName, $componentClassSimpleName, $componentTypeClass)
    {
        if (empty($pluginName) || empty($componentClassSimpleName)) {
            return null;
        }

        $pluginManager = PluginManager::getInstance();

        try {
            if (!$pluginManager->isPluginActivated($pluginName)) {
                return null;
            }

            $plugin = $pluginManager->getLoadedPlugin($pluginName);
        } catch (Exception $e) {
            Log::debug($e);

            return null;
        }

        $subnamespace = $componentTypeClass::COMPONENT_SUBNAMESPACE;
        $desiredComponentClass = 'Piwik\\Plugins\\' . $pluginName . '\\' . $subnamespace . '\\' . $componentClassSimpleName;

        $components = $plugin->findMultipleComponents($subnamespace, $componentTypeClass);
        foreach ($components as $class) {
            if ($class == $desiredComponentClass) {
                return new $class();
            }
        }
        return null;
    }
}