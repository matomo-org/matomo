<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Plugin;

use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Settings\Plugin\SystemSettings;
use Piwik\Tests\Integration\Settings\BaseSettingsTestCase;

/**
 * @group PluginSettings
 * @group SystemSettings
 */
class SystemSettingsTest extends BaseSettingsTestCase
{
    protected $updateEventName = 'SystemSettings.updated';

    public function test_weAreWorkingWithSystemSettings()
    {
        $this->assertTrue($this->settings instanceof SystemSettings);
    }

    public function test_constructor_getPluginName_canDetectPluginNameAutomatically()
    {
        $settings = new \Piwik\Plugins\ExampleSettingsPlugin\SystemSettings();
        $this->assertSame('ExampleSettingsPlugin', $settings->getPluginName());
        $this->assertSame('ExampleSettingsPlugin', $this->settings->getPluginName());
    }

    public function test_makeSetting_ShouldCreateASystemSetting()
    {
        $setting = $this->makeSetting('myName');

        $this->assertTrue($setting instanceof SystemSetting);
    }
}
