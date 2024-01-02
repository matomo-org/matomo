<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Plugin;

use Piwik\Settings\Plugin\UserSetting;
use Piwik\Settings\Plugin\UserSettings;
use Piwik\Tests\Framework\Mock\Settings\FakeUserSettings;
use Piwik\Tests\Integration\Settings\BaseSettingsTestCase;

/**
 * @group PluginSettings
 * @group UserSettings
 */
class UserSettingsTest extends BaseSettingsTestCase
{
    protected $updateEventName = 'UserSettings.updated';

    protected function createSettingsInstance()
    {
        return new FakeUserSettings();
    }

    public function test_weAreWorkingWithUserSettings()
    {
        $this->assertTrue($this->settings instanceof UserSettings);
    }

    public function test_constructor_getPluginName_canDetectPluginNameAutomatically()
    {
        $settings = new \Piwik\Plugins\ExampleSettingsPlugin\UserSettings();
        $this->assertSame('ExampleSettingsPlugin', $settings->getPluginName());
        $this->assertSame('ExampleSettingsPlugin', $this->settings->getPluginName());
    }

    public function test_makeSetting_ShouldCreateAUserSetting()
    {
        $setting = $this->makeSetting('myName');

        $this->assertTrue($setting instanceof UserSetting);
    }
}
