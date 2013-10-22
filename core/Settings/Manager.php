<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik\Settings;

use Piwik\Piwik;

/**
 * Settings manager
 *
 * @package Piwik
 * @subpackage Manager
 */
class Manager
{
    private static $settings = array();

    /**
     * @return \Piwik\Plugin\Settings[]
     */
    public static function getAllPluginSettings()
    {
        if (empty(static::$settings)) {

            $pluginSettings = array();
            // TODO: document hook and think about better name

            Piwik::postEvent('Plugin.addSettings', array(&$pluginSettings));

            $settings = array();
            foreach ($pluginSettings as $pluginName => $pluginSetting) {
                $settings[$pluginName] = new $pluginSetting($pluginName);
            }

            static::$settings = $settings;
        }

        return static::$settings;
    }

    /**
     * @return bool
     */
    public static function hasPluginSettingsForCurrentUser()
    {
        $settings = static::getAllPluginSettings();

        foreach ($settings as $setting) {
            $forUser = $setting->getSettingsForCurrentUser();
            if (!empty($forUser)) {
                return true;
            }
        }

        return false;
    }
}
