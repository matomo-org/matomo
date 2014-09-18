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
            Log::debug("ComponentFactory::%s: empty plugin name or component simple name requested (%s, %s)",
                __FUNCTION__, $pluginName, $componentClassSimpleName);

            return null;
        }

        $plugin = self::getActivatedPlugin(__FUNCTION__, $pluginName);
        if (empty($plugin)) {
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

        Log::debug("ComponentFactory::%s: Could not find requested component (args = ['%s', '%s', '%s']).",
            __FUNCTION__, $pluginName, $componentClassSimpleName, $componentTypeClass);

        return null;
    }

    /**
     * Finds a component instance that satisfies a given predicate.
     *
     * @param string $componentTypeClass The fully qualified class name of the component type, eg,
     *                                   `"Piwik\Plugin\Report"`.
     * @param string $pluginName|false The name of the plugin the component is expected to belong to,
     *                                 eg, `'UserSettings'`.
     * @param callback $predicate
     * @return mixed The component that satisfies $predicate or null if not found.
     */
    public static function getComponentIf($componentTypeClass, $pluginName, $predicate)
    {
        $pluginManager = PluginManager::getInstance();

        // get components to search through
        $subnamespace = $componentTypeClass::COMPONENT_SUBNAMESPACE;
        if (empty($pluginName)) {
            $components = $pluginManager->findMultipleComponents($subnamespace, $componentTypeClass);
        } else {
            $plugin = self::getActivatedPlugin(__FUNCTION__, $pluginName);
            if (empty($plugin)) {
                return null;
            }

            $components = $plugin->findMultipleComponents($subnamespace, $componentTypeClass);
        }

        // find component that satisfieds predicate
        foreach ($components as $class) {
            $component = new $class();
            if ($predicate($component)) {
                return $component;
            }
        }

        Log::debug("ComponentFactory::%s: Could not find component that satisfies predicate (args = ['%s', '%s', '%s']).",
            __FUNCTION__, $componentTypeClass, $pluginName, get_class($predicate));

        return null;
    }

    /**
     * @param string $function
     * @param string $pluginName
     * @return null|\Piwik\Plugin
     */
    private static function getActivatedPlugin($function, $pluginName)
    {
        $pluginManager = PluginManager::getInstance();
        try {
            if (!$pluginManager->isPluginActivated($pluginName)) {
                Log::debug("ComponentFactory::%s: component for deactivated plugin ('%s') requested.",
                    $function, $pluginName);

                return null;
            }

            return $pluginManager->getLoadedPlugin($pluginName);
        } catch (Exception $e) {
            Log::debug($e);

            return null;
        }
    }
}