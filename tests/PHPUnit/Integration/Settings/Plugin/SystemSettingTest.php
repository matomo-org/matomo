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
use Piwik\Settings\Plugin\SystemSetting;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;

/**
 * @group PluginSettings
 * @group Settings
 * @group SystemSetting
 */
class SystemSettingTest extends IntegrationTestCase
{
    public function tearDown(): void
    {
        Config::getInstance()->MyPluginName = array();
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

    public function test_getSettingValue_fromConfig_IfOneIsConfiguredInsteadOfTheValueFromDatabase()
    {
        $this->setSuperUser();
        $setting = $this->buildSetting('myusersetting');
        $setting->setValue('test');
        $this->assertEquals('test', $setting->getValue());

        Config::getInstance()->MyPluginName = array('myusersetting' => 'mynewvalue');
        $value = $setting->getValue();
        $this->assertEquals('mynewvalue', $value);
    }

    public function test_getSettingValue_fromConfig_ShouldConvertToTheSpecifiedType()
    {
        $this->setSuperUser();
        $setting = $this->buildSetting('myusersetting', FieldConfig::TYPE_BOOL);

        Config::getInstance()->MyPluginName = array('myusersetting' => '1');

        $this->assertTrue($setting->getValue());
    }

    public function test_getSettingValue_fromConfig_isCaseSensitive()
    {
        $this->setSuperUser();
        $setting = $this->buildSetting('myUsersetting');

        Config::getInstance()->MyPluginName = array('myusersetting' => '1');

        $this->assertSame('', $setting->getValue());

        Config::getInstance()->MyPluginName = array('myUsersetting' => '1');

        $this->assertSame('1', $setting->getValue());
    }

    public function test_getSettingsValue_fromConfig_ShouldSetObjectToNotWritableAsSoonAsAValueIsConfigured()
    {
        $this->setSuperUser();
        $setting = $this->buildSetting('myusersetting');

        $this->assertTrue($setting->isWritableByCurrentUser());

        Config::getInstance()->MyPluginName = array('myusersetting' => '0');
        $this->assertFalse($setting->isWritableByCurrentUser());
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

    public function test_setSettingsValue_shouldNotBePossible_AsSoonAsAConfigValueIsConfigured()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CoreAdminHome_PluginSettingChangeNotAllowed');

        $this->setSuperUser();
        $setting = $this->buildSetting('myusersetting');

        Config::getInstance()->MyPluginName = array('myusersetting' => '0');
        $setting->setValue('test');
    }

    public function test_save_shouldSaveDifferentValuesForDifferentPluginsAndFields()
    {
        $plugin1 = $this->buildSetting('field1', null, $login = 'plugin1');
        $plugin1->setValue('value1');
        $plugin1->save();

        $plugin2 = $this->buildSetting('field1', null, $login = 'plugin2');
        $this->assertSame('value1', $plugin1->getValue());
        $this->assertSame('', $plugin2->getValue());
        $plugin2->setValue('value2');
        $plugin2->save();

        $plugin3 = $this->buildSetting('field1', null, $login = 'plugin3');
        $this->assertSame('value1', $plugin1->getValue());
        $this->assertSame('value2', $plugin2->getValue());
        $this->assertSame('', $plugin3->getValue());

        $plugin1Field2 = $this->buildSetting('field2', null, $login = 'plugin1');
        $this->assertSame('', $plugin1Field2->getValue());
        $plugin1Field2->setValue('value1Field2');
        $plugin1Field2->save();

        $this->assertSame('value1', $plugin1->getValue());
        $this->assertSame('value1Field2', $plugin1Field2->getValue());
    }

    private function buildSetting($name, $type = null, $plugin = null)
    {
        if (!isset($type)) {
            $type = FieldConfig::TYPE_STRING;
        }
        if (!isset($plugin)) {
            $plugin = 'MyPluginName';
        }

        $systemSetting = new SystemSetting($name, $default = '', $type, $plugin);

        return $systemSetting;
    }
}
