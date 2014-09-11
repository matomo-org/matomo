<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';

class Visitor
{
    const DELIMITER_PLUGIN_NAME = ", ";

    private $details = array();

    public function __construct($details)
    {
        $this->details = $details;
    }

    function getPlugins()
    {
        $plugins = array(
            'config_pdf',
            'config_flash',
            'config_java',
            'config_director',
            'config_quicktime',
            'config_realplayer',
            'config_windowsmedia',
            'config_gears',
            'config_silverlight',
        );
        $pluginShortNames = array();

        foreach ($plugins as $plugin) {
            if (array_key_exists($plugin, $this->details) && $this->details[$plugin] == 1) {
                $pluginShortName    = substr($plugin, 7);
                $pluginShortNames[] = $pluginShortName;
            }
        }

        return implode(self::DELIMITER_PLUGIN_NAME, $pluginShortNames);
    }

    function getPluginIcons()
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

    function getOperatingSystemCode()
    {
        return $this->details['config_os'];
    }

    function getOperatingSystem()
    {
        return getOSLabel($this->details['config_os']);
    }

    function getOperatingSystemShortName()
    {
        return getOSShortLabel($this->details['config_os']);
    }

    function getOperatingSystemIcon()
    {
        return getOSLogo($this->details['config_os']);
    }

    function getBrowserFamilyDescription()
    {
        return getBrowserTypeLabel($this->getBrowserFamily());
    }

    function getBrowserFamily()
    {
        return getBrowserFamily($this->details['config_browser_name']);
    }

    function getBrowserCode()
    {
        return $this->details['config_browser_name'];
    }

    function getBrowserVersion()
    {
        return $this->details['config_browser_version'];
    }

    function getBrowser()
    {
        return getBrowserLabel($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }

    function getBrowserIcon()
    {
        return getBrowsersLogo($this->details['config_browser_name'] . ";" . $this->details['config_browser_version']);
    }

    function getScreenType()
    {
        if (!array_key_exists('config_resolution', $this->details)) {
            return null;
        }

        return getScreenTypeFromResolution($this->details['config_resolution']);
    }

    function getResolution()
    {
        if (!array_key_exists('config_resolution', $this->details)) {
            return null;
        }

        return $this->details['config_resolution'];
    }

    function getScreenTypeIcon()
    {
        $type = $this->getScreenType();

        if (empty($type)) {
            return null;
        }

        return getScreensLogo($type);
    }

}