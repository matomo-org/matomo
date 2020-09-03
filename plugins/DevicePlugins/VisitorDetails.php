<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicePlugins;

use Piwik\Plugins\Live\VisitorDetailsAbstract;

require_once PIWIK_INCLUDE_PATH . '/plugins/DevicePlugins/functions.php';

class VisitorDetails extends VisitorDetailsAbstract
{
    const DELIMITER_PLUGIN_NAME = ", ";

    public function extendVisitorDetails(&$visitor)
    {
        $visitor['plugins']      = $this->getPlugins();
        $visitor['pluginsIcons'] = $this->getPluginIcons();
    }

    protected function getPlugins()
    {
        $plugins = array();
        $columns = DevicePlugins::getAllPluginColumns();

        foreach ($columns as $column) {
            $plugins[] = $column->getColumnName();
        }

        $pluginShortNames = array();

        foreach ($plugins as $plugin) {
            if (array_key_exists($plugin, $this->details) && $this->details[$plugin] == 1) {
                $pluginShortName    = substr($plugin, 7);
                $pluginShortNames[] = $pluginShortName;
            }
        }

        return implode(self::DELIMITER_PLUGIN_NAME, $pluginShortNames);
    }

    protected function getPluginIcons()
    {
        $pluginNames = $this->getPlugins();
        if (!empty($pluginNames)) {
            $pluginNames = explode(self::DELIMITER_PLUGIN_NAME, $pluginNames);
            $pluginIcons = array();

            foreach ($pluginNames as $plugin) {
                $pluginIcons[] = array("pluginIcon" => getPluginsLogo($plugin), "pluginName" => $plugin);
            }

            return $pluginIcons;
        }

        return null;
    }
}