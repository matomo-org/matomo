<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Settings\Storage\Backend\PluginSettingsTable;

abstract class BasePart
{
    abstract public function getDescription();
    abstract public function getSteps();

    private static $settings = null;

    private function getSettings()
    {
        if (!isset(self::$settings)) {
            $pluginSettings = new PluginSettingsTable('Tour', $login = '');
            self::$settings = $pluginSettings->load();
        }

        return self::$settings;
    }

    public function clearCache()
    {
        self::$settings = null;
    }

    protected function isSkipped($key)
    {
        $settings = $this->getSettings();

        if (!empty($settings[$key . '_skipped'])) {
            return true;
        }

        return false;
    }

    public static function skipStep($key)
    {
        $pluginSettings = new PluginSettingsTable('Tour', $login = '');
        $settings = $pluginSettings->load();
        $settings[$key . '_skipped'] = '1';
        $pluginSettings->save($settings);
    }

}