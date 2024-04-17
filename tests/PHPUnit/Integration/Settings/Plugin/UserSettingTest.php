<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Plugin;

use Piwik\Db;
use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\UserSetting;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\Mock\Settings\FakeUserSettings;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;

/**
 * @group PluginSettings
 * @group Settings
 * @group UserSetting
 */
class UserSettingTest extends IntegrationTestCase
{
    protected function createSettingsInstance()
    {
        return new FakeUserSettings();
    }

    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        new UserSetting('name', $default = 5, FieldConfig::TYPE_INT, 'MyPlugin', 'login');

        $this->assertNotDbConnectionCreated();
    }

    public function test_constructor_shouldEstablishADatabaseConnection_AsSoonAsWeGetAValue()
    {
        $this->setSuperUser();
        Db::destroyDatabaseObject();

        $setting = $this->buildSetting('testSetting');

        $this->assertNotDbConnectionCreated();

        $setting->getValue();

        $this->assertDbConnectionCreated();
    }

    public function test_constructor_shouldEstablishADatabaseConnection_AsSoonAsWeSetAValue()
    {
        $this->setSuperUser();
        Db::destroyDatabaseObject();

        $setting  = $this->buildSetting('testSetting');
        $settings = $this->createSettingsInstance();
        $settings->addSetting($setting);

        $this->assertNotDbConnectionCreated();

        $setting->setValue('5');

        $this->assertDbConnectionCreated();
    }

    public function test_setSettingValue_shouldThrowException_IfAnonymousIsTryingToSetASettingWhichNeedsUserPermission()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('CoreAdminHome_PluginSettingChangeNotAllowed');

        $this->setAnonymousUser();
        $setting = $this->buildSetting('mysystem');

        $setting->setValue(2);
    }

    public function test_setSettingValue_shouldSucceed_IfUserIsTryingToSetASettingWhichNeedsUserPermission()
    {
        $this->setUser();
        $setting = $this->buildSetting('mysystem');
        $setting->setValue(2);

        $this->assertSettingHasValue($setting, 2);
    }

    public function test_setSettingValue_shouldCastValue_IfTypeIsSetButNoFilter()
    {
        $this->setUser();

        // cast to INT
        $setting = $this->buildSetting('mysystem', FieldConfig::TYPE_INT);
        $setting->setValue('31');
        $this->assertSettingHasValue($setting, 31, 'integer');

        // ARRAY
        $setting = $this->buildSetting('mysystem2', FieldConfig::TYPE_ARRAY);
        $setting->setValue('31xm42');
        $this->assertSettingHasValue($setting, array('31xm42'), 'array');

        // BOOL
        $setting = $this->buildSetting('mysystem3', FieldConfig::TYPE_BOOL);
        $setting->setValue('1');
        $this->assertSettingHasValue($setting, true, 'boolean');

        // FLOAT
        $setting = $this->buildSetting('mysystem4', FieldConfig::TYPE_FLOAT);
        $setting->setValue('1.21');
        $this->assertSettingHasValue($setting, 1.21, 'float');

        // STRING
        $setting = $this->buildSetting('mysystem5', FieldConfig::TYPE_STRING);
        $setting->setValue('31xm42');
        $this->assertSettingHasValue($setting, '31xm42');
    }

    public function test_setSettingValue_shouldApplyFilterAndNotCast_IfAFilterIsSet()
    {
        $this->setUser();

        $self = $this;

        $setting = $this->buildSetting('mysystem', FieldConfig::TYPE_INT);
        $setting->setConfigureCallback(function (FieldConfig $field) use ($self, $setting) {
            $field->transform = function ($value, $userSetting) use ($self, $setting) {
                $self->assertEquals('31', $value);
                $self->assertEquals($setting, $userSetting);

                return '43939kmf3m3';
            };
        });

        $setting->setValue('31');

        // should not be casted to int
        $this->assertSettingHasValue($setting, 43939, 'integer');
    }

    public function test_setSettingValue_shouldValidateAValue_IfAFilterIsSet()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Validation Fail');

        $this->setUser();
        $self = $this;

        $setting = $this->buildSetting('mysystem', FieldConfig::TYPE_INT);
        $setting->setConfigureCallback(function (FieldConfig $field) use ($self, $setting) {
            $field->validate = function ($value, $userSetting) use ($self, $setting) {
                $self->assertEquals('31xm42', $value);
                $self->assertEquals($setting, $userSetting);

                throw new \Exception('Validation Fail');
            };
        });

        $setting->setValue('31xm42');
    }

    public function test_getSettingValue_shouldReturnUncastedDefaultValue_IfNoValueIsSet()
    {
        $this->setUser();

        $setting = $this->buildSetting('mydefaultsystem', FieldConfig::TYPE_INT, $default = 'mytestvalue');
        $this->settings->addSetting($setting);

        // should not be casted to int
        $this->assertSettingHasValue($setting, 'mytestvalue', 'string');
    }

    public function test_getSettingValue_shouldReturnValue_IfValueExistsAndUserHasPermission()
    {
        $this->setUser();
        $setting = $this->buildSetting('myusersetting', FieldConfig::TYPE_ARRAY);
        $setting->setValue(array(2,3,4));

        $this->assertSettingHasValue($setting, array(2,3,4));
    }

    public function test_save_shouldSaveDifferentValuesForDifferentUsersAndFields()
    {
        $login1 = $this->buildSetting('field1', null, '', $login = 'user1');
        $login1->setValue('value1');
        $login1->save();

        $login2 = $this->buildSetting('field1', null, '', $login = 'user2');
        $this->assertSame('value1', $login1->getValue());
        $this->assertSame('', $login2->getValue());
        $login2->setValue('value2');
        $login2->save();

        $login3 = $this->buildSetting('field1', null, '', $login = 'user3');
        $this->assertSame('value1', $login1->getValue());
        $this->assertSame('value2', $login2->getValue());
        $this->assertSame('', $login3->getValue());

        $login1Field2 = $this->buildSetting('field2', null, '', $login = 'user1');
        $this->assertSame('', $login1Field2->getValue());
        $login1Field2->setValue('value1Field2');
        $login1Field2->save();

        $this->assertSame('value1', $login1->getValue());
        $this->assertSame('value1Field2', $login1Field2->getValue());
    }

    private function buildSetting($name, $type = null, $default = '', $login = null)
    {
        if (!isset($type)) {
            $type = FieldConfig::TYPE_STRING;
        }

        if (!isset($login)) {
            $login = FakeAccess::$identity;
        }

        $userSetting = new UserSetting($name, $default, $type, 'MyPluginName', $login);

        return $userSetting;
    }
}
