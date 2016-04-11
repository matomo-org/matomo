<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\Mock\Settings;

use Piwik\Settings\Setting;

class FakeUserSettings extends \Piwik\Plugins\ExampleSettingsPlugin\UserSettings {
    protected $pluginName = 'ExampleSettingsPlugin';

    public function init()
    {

    }

    public function makeSetting($name, $defaultValue, $type, $configureCallback)
    {
        return parent::makeSetting($name, $defaultValue, $type, $configureCallback);
    }

    public function addSetting(Setting $setting)
    {
        parent::addSetting($setting);
    }
}

