<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Settings\Setting;

class CorePluginTestSettings extends \Piwik\Plugins\ExampleSettingsPlugin\Settings {

    public function init()
    {

    }

    public function addSetting(Setting $setting)
    {
        parent::addSetting($setting);
    }
}

