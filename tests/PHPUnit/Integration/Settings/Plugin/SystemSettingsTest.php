<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function testWeAreWorkingWithSystemSettings()
    {
        $this->assertTrue($this->settings instanceof SystemSettings);
    }

    public function testConstructorGetPluginNameCanDetectPluginNameAutomatically()
    {
        $settings = new \Piwik\Plugins\ExampleSettingsPlugin\SystemSettings();
        $this->assertSame('ExampleSettingsPlugin', $settings->getPluginName());
        $this->assertSame('ExampleSettingsPlugin', $this->settings->getPluginName());
    }

    public function testMakeSettingShouldCreateASystemSetting()
    {
        $setting = $this->makeSetting('myName');

        $this->assertTrue($setting instanceof SystemSetting);
    }
}
