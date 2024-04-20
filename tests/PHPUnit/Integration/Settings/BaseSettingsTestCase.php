<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Db;
use Piwik\Piwik;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Setting;

/**
 * @group PluginSettings
 * @group SystemSettings
 */
class BaseSettingsTestCase extends IntegrationTestCase
{
    protected $updateEventName;

    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        Db::destroyDatabaseObject();

        $this->assertNotDbConnectionCreated();

        $this->createSettingsInstance();

        $this->assertNotDbConnectionCreated();
    }

    public function test_makeSetting_ShouldAlsoAddTheSetting()
    {
        $this->assertNull($this->settings->getSetting('myName'));

        $this->makeSetting('myName');

        $this->assertNotNull($this->settings->getSetting('myName'));
    }

    public function test_makeSetting_ShouldFailWhenAdingSameSettingTwice()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('A setting with name "myName" does already exist for plugin "ExampleSettingsPlugin"');

        $this->makeSetting('myName');
        $this->makeSetting('myName');
    }

    public function test_getSetting_CanRetrieveAspecificSetting()
    {
        $this->makeSetting('myName');

        $this->assertSame('myName', $this->settings->getSetting('myName')->getName());
    }

    public function test_getSetting_IsCaseSensitive()
    {
        $this->makeSetting('myName');

        $this->assertNull($this->settings->getSetting('myname'));
    }

    public function test_getSetting_ReturnsNullWhenNoSuchSettingFound()
    {
        $this->assertNull($this->settings->getSetting('myName'));
    }

    public function test_getSettingsWritableByCurrentUser_returnsOnlySettingsThatAreWritable()
    {
        $this->assertSame(array(), $this->settings->getSettingsWritableByCurrentUser());

        $setting1 = $this->makeSetting('myName1');
        $setting1->setIsWritableByCurrentUser(true);

        $setting2 = $this->makeSetting('myName2');
        $setting2->setIsWritableByCurrentUser(false);

        $setting3 = $this->makeSetting('myName3');
        $setting3->setIsWritableByCurrentUser(true);

        $expected = array(
            'myName1' => $setting1,
            'myName3' => $setting3
        );
        $this->assertSame($expected, $this->settings->getSettingsWritableByCurrentUser());
    }

    public function test_save_triggersAnEvent()
    {
        $settings = null;

        Piwik::addAction($this->updateEventName, function ($instance) use (&$settings) {
            $settings = $instance;
        });

        $this->settings->save();

        $this->assertSame($settings, $this->settings);
    }

    public function test_getTitle_shouldDefaultToPluginName()
    {
        $this->assertNotEmpty($this->settings->getTitle());
        $this->assertSame($this->settings->getTitle(), $this->settings->getPluginName());
    }

    public function test_getTitle_PrefersSetTitleOverPluginName()
    {
        if (method_exists($this->settings, 'setTitle')) {
            $this->settings->setTitle('title');
            $this->assertSame('title', $this->settings->getTitle());
        } else {
            self::expectNotToPerformAssertions();
        }
    }

    protected function makeSetting($name)
    {
        $type = FieldConfig::TYPE_STRING;
        return $this->settings->makeSetting($name, $default = '', $type, function () {
        });
    }

    public function test_addSetting_shouldAddNewSetting()
    {
        $settingName = 'testSetting';
        $setting  = $this->buildSetting($settingName);
        $settings = $this->createSettingsInstance();

        $this->assertEmpty($settings->getSetting($settingName));

        $settings->addSetting($setting);

        $this->assertSame($setting, $settings->getSetting($settingName));
    }

    public function test_addSetting_throwsException_IfSameSettingAddedTwice()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('"testSetting" does already exist');

        $settingName = 'testSetting';
        $setting  = $this->buildSetting($settingName);
        $settings = $this->createSettingsInstance();

        $settings->addSetting($setting);
        $settings->addSetting($setting);
    }

    private function buildSetting($name, $type = null, $default = '')
    {
        if (!isset($type)) {
            $type = FieldConfig::TYPE_STRING;
        }

        $userSetting = new Setting($name, $default, $type, 'MyPluginName');

        return $userSetting;
    }
}
