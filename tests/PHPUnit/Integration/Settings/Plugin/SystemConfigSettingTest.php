<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Plugin;

use Piwik\Config;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\SystemConfigSetting;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;

/**
 * @group PluginSettings
 * @group Settings
 * @group SystemConfigSetting
 */
class SystemConfigSettingTest extends IntegrationTestCase
{
    private $section = 'MySection';

    public function tearDown(): void
    {
        $this->setConfigValues(array());
        parent::tearDown();
    }

    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        $this->buildSetting('name', FieldConfig::TYPE_INT, 'MyPlugin');

        $this->assertNotDbConnectionCreated();
    }

    public function test_setSettingValue_shouldThrowException_IfAUserIsTryingToSetASettingWhichNeedsSuperUserPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CoreAdminHome_PluginSettingChangeNotAllowed');

        $this->setUser();
        $setting = $this->buildSetting('mysystem');

        $setting->setValue(2);
    }

    public function test_setSettingValue_shouldThrowException_IfAnonymousIsTryingToSetASettingWhichNeedsSuperUserPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CoreAdminHome_PluginSettingChangeNotAllowed');

        $this->setAnonymousUser();
        $setting = $this->buildSetting('mysystem');

        $setting->setValue(2);
    }

    public function test_setSettingValue_shouldSucceed_IfSuperUserTriesToSaveASettingWhichRequiresSuperUserPermission()
    {
        $this->setSuperUser();

        $setting = $this->buildSetting('mysystem');
        $setting->setValue(2);

        $this->assertSettingHasValue($setting, 2);
    }

    public function test_getSettingValue_shouldBeReadableBySuperUser()
    {
        $this->setSuperUser();
        $setting = $this->buildSetting('myusersetting');
        $this->assertEquals('', $setting->getValue());
    }

    public function test_getSettingValue_shouldReturnValue_IfReadbleByCurrentUserIsAllowed()
    {
        $this->setUser();
        $setting = $this->buildSetting('myusersetting');

        $this->assertEquals('', $setting->getValue());
    }

    public function test_getSettingValue_fromConfig()
    {
        $this->setSuperUser();
        $this->setConfigValues(array('myusersetting' => 'mynewvalue'));

        $setting = $this->buildSetting('myusersetting');
        $this->assertEquals('mynewvalue', $setting->getValue());
    }

    public function test_getSettingValue_fromConfig_ShouldConvertToTheSpecifiedType()
    {
        $this->setSuperUser();
        $setting = $this->buildSetting('myusersetting', FieldConfig::TYPE_BOOL);

        $this->setConfigValues(array('myusersetting' => '1'));

        $this->assertTrue($setting->getValue());
    }

    public function test_getSettingValue_fromConfig_isCaseSensitive()
    {
        $this->setSuperUser();
        $this->setConfigValues(array('myusersetting' => '1', 'myUsersetting2' => '1'));

        $setting = $this->buildSetting('myUsersetting');
        $this->assertSame('', $setting->getValue());

        $setting = $this->buildSetting('myUsersetting2');
        $this->assertSame('1', $setting->getValue());
    }

    public function test_setIsWritableByCurrentUser()
    {
        $this->setSuperUser();
        $setting = $this->buildSetting('myusersetting');

        $this->assertTrue($setting->isWritableByCurrentUser());

        $setting->setIsWritableByCurrentUser(false);
        $this->assertFalse($setting->isWritableByCurrentUser());

        $setting->setIsWritableByCurrentUser(true);
        $this->assertTrue($setting->isWritableByCurrentUser());
    }

    public function test_save_shouldSaveDifferentValuesForDifferentFieldsAndSections()
    {
        $plugin1 = $this->buildSetting('field1', null, $plugin = 'plugin1', 'section1');
        $plugin1->setValue('value1');
        $plugin1->save();

        $plugin2 = $this->buildSetting('field1', null, $plugin = 'plugin2', 'section2');
        $this->assertSame('value1', $plugin1->getValue());
        $this->assertSame('', $plugin2->getValue());
        $plugin2->setValue('value2');
        $plugin2->save();

        $plugin3 = $this->buildSetting('field3', null, $plugin = 'plugin3', 'section1');
        $this->assertSame('value1', $plugin1->getValue());
        $this->assertSame('value2', $plugin2->getValue());
        $this->assertSame('', $plugin3->getValue());

        $plugin3->setValue('test');

        $this->assertSame('value1', $plugin1->getValue());
        $this->assertSame('test', $plugin3->getValue());
    }

    private function setConfigValues($values)
    {
        Config::getInstance()->{$this->section} = $values;
    }

    private function buildSetting($name, $type = null, $plugin = null, $section = null)
    {
        if (!isset($type)) {
            $type = FieldConfig::TYPE_STRING;
        }
        if (!isset($plugin)) {
            $plugin = 'MyPluginName';
        }
        if (!isset($section)) {
            $section = $this->section;
        }

        $systemSetting = new SystemConfigSetting($name, $default = '', $type, $plugin, $section);

        return $systemSetting;
    }
}
