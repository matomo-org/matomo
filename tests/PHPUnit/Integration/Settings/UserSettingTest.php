<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Db;
use Piwik\Settings\UserSetting;
use Piwik\Plugin\Settings as PluginSettings;

/**
 * @group PluginSettings
 * @group Settings
 * @group UserSetting
 */
class UserSettingTest extends IntegrationTestCase
{
    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        new UserSetting('name', 'title');

        $this->assertNotDbConnectionCreated();
    }

    public function test_constructor_shouldEstablishADatabaseConnection_AsSoonAsWeGetAValue()
    {
        $this->setSuperUser();
        Db::destroyDatabaseObject();

        $setting = $this->buildUserSetting('testSetting', 'Test Setting');

        $this->assertNotDbConnectionCreated();

        $setting->getValue($setting);

        $this->assertDbConnectionCreated();
    }

    public function test_constructor_shouldEstablishADatabaseConnection_AsSoonAsWeSetAValue()
    {
        $this->setSuperUser();
        Db::destroyDatabaseObject();

        $setting  = $this->buildUserSetting('testSetting', 'Test Setting');
        $settings = $this->createSettingsInstance();
        $settings->addSetting($setting);

        $this->assertNotDbConnectionCreated();

        $setting->setValue('5');

        $this->assertDbConnectionCreated();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_setSettingValue_shouldThrowException_IfAnonymousIsTryingToSetASettingWhichNeedsUserPermission()
    {
        $this->setAnonymousUser();
        $setting = $this->addUserSetting('mysystem', 'mytitle');

        $setting->setValue(2);
    }

    public function test_setSettingValue_shouldSucceed_IfUserIsTryingToSetASettingWhichNeedsUserPermission()
    {
        $this->setUser();
        $setting = $this->addUserSetting('mysystem', 'mytitle');
        $setting->setValue(2);

        $this->assertSettingHasValue($setting, 2);
    }

    public function test_setSettingValue_shouldCastValue_IfTypeIsSetButNoFilter()
    {
        $this->setUser();

        // cast to INT
        $setting       = $this->addUserSetting('mysystem', 'mytitle');
        $setting->type = PluginSettings::TYPE_INT;
        $setting->setValue('31xm42');
        $this->assertSettingHasValue($setting, 31, 'integer');

        // ARRAY
        $setting->type = PluginSettings::TYPE_ARRAY;
        $setting->setValue('31xm42');
        $this->assertSettingHasValue($setting, array('31xm42'), 'array');

        // BOOL
        $setting->type = PluginSettings::TYPE_BOOL;
        $setting->setValue('1');
        $this->assertSettingHasValue($setting, true, 'boolean');

        // FLOAT
        $setting->type = PluginSettings::TYPE_FLOAT;
        $setting->setValue('1.21');
        $this->assertSettingHasValue($setting, 1.21, 'float');

        // STRING
        $setting->type = PluginSettings::TYPE_STRING;
        $setting->setValue('31xm42');
        $this->assertSettingHasValue($setting, '31xm42');
    }

    public function test_setSettingValue_shouldApplyFilterAndNotCast_IfAFilterIsSet()
    {
        $this->setUser();

        $setting       = $this->buildUserSetting('mysystem', 'mytitle');
        $setting->type = PluginSettings::TYPE_INT;

        $self = $this;
        $setting->transform = function ($value, $userSetting) use ($self, $setting) {
            $self->assertEquals('31xm42', $value);
            $self->assertEquals($setting, $userSetting);

            return '43939kmf3m3';
        };

        $setting->setValue('31xm42');

        // should not be casted to int
        $this->assertSettingHasValue($setting, '43939kmf3m3', 'string');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Validation Fail
     */
    public function test_setSettingValue_shouldValidateAValue_IfAFilterIsSet()
    {
        $this->setUser();

        $setting       = $this->buildUserSetting('mysystem', 'mytitle');
        $setting->type = PluginSettings::TYPE_INT;

        $self = $this;
        $setting->validate = function ($value, $userSetting) use ($self, $setting) {
            $self->assertEquals('31xm42', $value);
            $self->assertEquals($setting, $userSetting);

            throw new \Exception('Validation Fail');
        };

        $setting->setValue('31xm42');
    }

    public function test_getSettingValue_shouldReturnUncastedDefaultValue_IfNoValueIsSet()
    {
        $this->setUser();

        $setting = $this->addUserSetting('mydefaultsystem', 'mytitle');
        $setting->type = PluginSettings::TYPE_INT;
        $setting->defaultValue ='mytestvalue';

        // should not be casted to int
        $this->assertSettingHasValue($setting, 'mytestvalue', 'string');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingReadNotAllowed
     */
    public function test_getSettingValue_shouldThrowException_IfGivenSettingDoesNotExist()
    {
        $this->setAnonymousUser();
        $setting = $this->buildUserSetting('myusersetting', 'mytitle');
        $setting->getValue();
    }

    public function test_getSettingValue_shouldReturnValue_IfValueExistsAndUserHasPermission()
    {
        $this->setUser();
        $setting = $this->addUserSetting('myusersetting', 'mytitle');
        $setting->type = PluginSettings::TYPE_ARRAY;
        $setting->setValue(array(2,3,4));

        $this->assertSettingHasValue($setting, array(2,3,4));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_removeSettingValue_shouldThrowException_IfUserHasNotEnoughUserPermissions()
    {
        $this->setAnonymousUser();
        $setting = $this->addUserSetting('myusersetting', 'mytitle');
        $setting->removeValue();
    }

    public function test_removeSettingValue_shouldRemoveValue_IfValueExistsAndHasEnoughPermissions()
    {
        $this->setUser();
        $setting = $this->addUserSetting('myusersetting', 'mytitle');
        $setting->setValue('12345657');
        $this->assertSettingHasValue($setting, '12345657');

        $setting->removeValue();
        $this->assertSettingHasValue($setting, null);
    }

    public function test_userSetting_shouldGenerateDifferentKey_ForDifferentUsers()
    {
        $this->setSuperUser();

        $user1 = $this->buildUserSetting('myname', 'mytitle', 'user1');
        $user2 = $this->buildUserSetting('myname', 'mytitle', '_user2_');
        $user3 = $this->buildUserSetting('myname', 'mytitle');

        $this->assertEquals('myname#user1#', $user1->getKey());
        $this->assertEquals('myname#_user2_#', $user2->getKey());
        $this->assertEquals('myname#superUserLogin#', $user3->getKey());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage You do not have the permission to read the settings of a different user
     */
    public function test_userSetting_shouldThrowException_IfSomeoneTriesToReadSettingsFromAnotherUserAndIsNotSuperuser()
    {
        $this->setUser();

        $this->buildUserSetting('myname', 'mytitle', 'myRandomName');
    }

    public function test_userSetting_shouldBeAbleToSetLoginAndChangeValues_IfUserHasSuperUserAccess()
    {
        $this->setSuperUser();

        $setting = $this->buildUserSetting('myname', 'mytitle', 'myRandomName');
        $this->settings->addSetting($setting);

        $setting->setValue(5);
        $this->assertSettingHasValue($setting, 5);

        $setting->removeValue();
        $this->assertSettingHasValue($setting, null);
    }

}
