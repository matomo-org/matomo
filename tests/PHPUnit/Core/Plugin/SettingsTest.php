<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Access;
use Piwik\Plugin\Settings as PluginSettings;
use Piwik\Settings\Setting;

class TestablePluginSettings extends \Piwik\Plugins\ExampleSettingsPlugin\Settings {

    public function init()
    {

    }

    public function addSetting(Setting $setting)
    {
        parent::addSetting($setting);
    }
}

/**
 * Class SettingsTest
 * @group Core
 * @group PluginSettings
 */
class SettingsTest extends DatabaseTestCase
{
    /**
     * @var TestablePluginSettings
     */
    private $settings;

    public function setUp()
    {
        parent::setUp();
        Access::setSingletonInstance(null);

        $this->settings = $this->createSettingsInstance();
    }

    public function tearDown()
    {
        $this->setSuperUser();
        $this->settings->removeAllPluginSettings();

        parent::tearDown();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage A setting with name "myname" does already exist for plugin "ExampleSettingsPlugin"
     */
    public function test_addSetting_shouldThrowException_InCaseTwoSettingsHaveTheSameName()
    {
        $this->addUserSetting('myname', 'mytitle');

        $setting = $this->buildUserSetting('myname', 'mytitle2');
        $this->settings->addSetting($setting);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The setting name "myname_" in plugin "ExampleSettingsPlugin" is not valid. Only alpha and numerical characters are allowed
     */
    public function test_addSetting_shouldThrowException_IfTheSettingNameIsNotValid()
    {
        $setting = $this->buildUserSetting('myname_', 'mytitle');
        $this->settings->addSetting($setting);
    }

    public function test_addSetting_shouldAssignDefaultType_IfFieldIsGivenButNoType()
    {
        $setting = $this->buildUserSetting('myname', 'mytitle');
        $setting->field = TestablePluginSettings::FIELD_MULTI_SELECT;

        $this->settings->addSetting($setting);

        $this->assertEquals(TestablePluginSettings::TYPE_ARRAY, $setting->type);
    }

    public function test_addSetting_shouldAssignDefaultField_IfTypeIsGivenButNoField()
    {
        $setting = $this->buildUserSetting('myname', 'mytitle');
        $setting->type = TestablePluginSettings::TYPE_ARRAY;

        $this->settings->addSetting($setting);

        $this->assertEquals(TestablePluginSettings::FIELD_MULTI_SELECT, $setting->field);
    }

    public function test_addSetting_shouldAddAValidator_IfFieldOptionsAreGiven()
    {
        $setting = $this->buildUserSetting('myname', 'mytitle');
        $setting->fieldOptions = array('allowedval' => 'DisplayName', 'allowedval2' => 'Name 2');

        $this->settings->addSetting($setting);

        $this->assertInstanceOf('\Closure', $setting->validate);
    }

    public function test_addSetting_shouldAddTheSettings_IfValid()
    {
        $setting = $this->addUserSetting('myname', 'mytitle');

        $this->assertEquals(array('myname' => $setting), $this->settings->getSettings());
    }

    public function test_addSetting_shouldPassTheStorage_ToTheSetting()
    {
        $this->setSuperUser();

        $setting = $this->buildUserSetting('myname', 'mytitle', 'myRandomName');
        $this->settings->addSetting($setting);

        $this->settings->setSettingValue($setting, 5);
        $this->assertSettingHasValue($setting, 5);

        $this->assertEquals($setting->getValue(), 5);

        $this->settings->setSettingValue($setting, 'test3434');
        $this->assertEquals($setting->getValue(), 'test3434');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The setting myname2 does not exist
     */
    public function test_setSettingValue_shouldThrowException_IfTryingToSetAValueForNotAvailableSetting()
    {
        $this->addUserSetting('myname', 'mytitle');

        $setting = $this->buildUserSetting('myname2', 'mytitle2');
        $this->settings->setSettingValue($setting, 2);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_setSettingValue_shouldThrowException_IfAUserIsTryingToSetASettingWhichNeedsSuperUserPermission()
    {
        $this->setUser();
        $setting = $this->addSystemSetting('mysystem', 'mytitle');

        $this->settings->setSettingValue($setting, 2);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_setSettingValue_shouldThrowException_IfAnonymousIsTryingToSetASettingWhichNeedsUserPermission()
    {
        $setting = $this->addUserSetting('mysystem', 'mytitle');

        $this->settings->setSettingValue($setting, 2);
    }

    public function test_setSettingValue_shouldSucceed_IfUserIsTryingToSetASettingWhichNeedsUserPermission()
    {
        $this->setUser();
        $setting = $this->addUserSetting('mysystem', 'mytitle');

        $this->settings->setSettingValue($setting, 2);

        $this->assertSettingHasValue($setting, 2);
    }

    public function test_setSettingValue_shouldSucceed_IfSuperUserTriesToSaveASettingWhichRequiresSuperUserPermission()
    {
        $this->setSuperUser();

        $setting = $this->addSystemSetting('mysystem', 'mytitle');

        $this->settings->setSettingValue($setting, 2);

        $this->assertSettingHasValue($setting, 2);
    }

    public function test_setSettingValue_shouldNotPersistValueInDatabase_OnSuccess()
    {
        $this->setSuperUser();

        $setting = $this->buildSystemSetting('mysystem', 'mytitle');
        $this->settings->addSetting($setting);
        $this->settings->setSettingValue($setting, 2);

        // make sure stored on the instance
        $this->assertSettingHasValue($setting, 2);
        $this->assertSettingIsNotSavedInTheDb('mysystem', null);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingsValueNotAllowed
     */
    public function test_setSettingValue_shouldApplyValidationAndFail_IfOptionsAreSet()
    {
        $this->setUser();
        $setting = $this->buildUserSetting('mysystem', 'mytitle');
        $setting->fieldOptions = array('allowed' => 'text', 'allowed2' => 'text2');

        $this->settings->addSetting($setting);

        $this->settings->setSettingValue($setting, 'notallowed');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingsValueNotAllowed
     */
    public function test_setSettingValue_shouldApplyValidationAndFail_IfOptionsAreSetAndValueIsAnArray()
    {
        $this->setUser();
        $setting = $this->buildUserSetting('mysystem', 'mytitle');
        $setting->fieldOptions = array('allowed' => 'text', 'allowed2' => 'text2');
        $setting->field        = PluginSettings::FIELD_MULTI_SELECT;

        $this->settings->addSetting($setting);

        $this->settings->setSettingValue($setting, array('allowed', 'notallowed'));
    }

    public function test_setSettingValue_shouldApplyValidationAndSucceed_IfOptionsAreSet()
    {
        $this->setUser();
        $setting = $this->buildUserSetting('mysystem', 'mytitle');
        $setting->fieldOptions = array('allowed' => 'text', 'allowed2' => 'text2');
        $setting->field        = PluginSettings::FIELD_MULTI_SELECT;

        $this->settings->addSetting($setting);

        $this->settings->setSettingValue($setting, array('allowed', 'allowed2'));
        $this->assertSettingHasValue($setting, array('allowed', 'allowed2'));

        $setting->type = PluginSettings::TYPE_STRING;
        $this->settings->setSettingValue($setting, 'allowed');
        $this->assertSettingHasValue($setting, 'allowed');
    }

    public function test_setSettingValue_shouldCastValue_IfTypeIsSetButNoFilter()
    {
        $this->setUser();

        // cast to INT
        $setting       = $this->addUserSetting('mysystem', 'mytitle');
        $setting->type = PluginSettings::TYPE_INT;
        $this->settings->setSettingValue($setting, '31xm42');
        $this->assertSettingHasValue($setting, 31, 'integer');

        // ARRAY
        $setting->type = PluginSettings::TYPE_ARRAY;
        $this->settings->setSettingValue($setting, '31xm42');
        $this->assertSettingHasValue($setting, array('31xm42'), 'array');

        // BOOL
        $setting->type = PluginSettings::TYPE_BOOL;
        $this->settings->setSettingValue($setting, '1');
        $this->assertSettingHasValue($setting, true, 'boolean');

        // FLOAT
        $setting->type = PluginSettings::TYPE_FLOAT;
        $this->settings->setSettingValue($setting, '1.21');
        $this->assertSettingHasValue($setting, 1.21, 'float');

        // STRING
        $setting->type = PluginSettings::TYPE_STRING;
        $this->settings->setSettingValue($setting, '31xm42');
        $this->assertSettingHasValue($setting, '31xm42');
    }

    public function test_setSettingValue_shouldApplyFilterAndNotCast_IfAFilterIsSet()
    {
        $this->setUser();

        $setting       = $this->buildUserSetting('mysystem', 'mytitle');
        $setting->type = PluginSettings::TYPE_INT;

        $self = $this;
        $setting->filter = function ($value, $userSetting) use ($self, $setting) {
            $self->assertEquals('31xm42', $value);
            $self->assertEquals($setting, $userSetting);

            return '43939kmf3m3';
        };

        $this->settings->addSetting($setting);
        $this->settings->setSettingValue($setting, '31xm42');

        // should not be casted to int
        $this->assertSettingHasValue($setting, '43939kmf3m3', 'string');
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
     * @expectedExceptionMessage The setting myusersetting does not exist
     */
    public function test_getSettingValue_shouldThrowException_IfGivenSettingDoesNotExist()
    {
        $setting = $this->buildUserSetting('myusersetting', 'mytitle');

        $this->settings->getSettingValue($setting);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_getSettingValue_shouldThrowException_IfUserHasNotEnoughPermissionToReadValue()
    {
        $this->setUser();
        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $this->settings->getSettingValue($setting);
    }

    public function test_getSettingValue_shouldReturnValue_IfValueExistsAndUserHasPermission()
    {
        $this->setUser();
        $setting = $this->addUserSetting('myusersetting', 'mytitle');
        $setting->type = PluginSettings::TYPE_ARRAY;
        $this->settings->setSettingValue($setting, array(2,3,4));

        $this->assertSettingHasValue($setting, array(2,3,4));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_removeSettingValue_shouldThrowException_IfUserHasNotEnoughUserPermissions()
    {
        $setting = $this->addUserSetting('myusersetting', 'mytitle');
        $this->settings->removeSettingValue($setting);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_removeSettingValue_shouldThrowException_IfUserHasNotEnoughAdminPermissions()
    {
        $this->setUser();
        $setting = $this->addSystemSetting('mysystemsetting', 'mytitle');
        $this->settings->removeSettingValue($setting);
    }

    public function test_removeSettingValue_shouldRemoveValue_IfValueExistsAndHasEnoughPermissions()
    {
        $this->setUser();
        $setting = $this->addUserSetting('myusersetting', 'mytitle');
        $this->settings->setSettingValue($setting, '12345657');
        $this->assertSettingHasValue($setting, '12345657');

        $this->settings->removeSettingValue($setting);
        $this->assertSettingHasValue($setting, null);
    }

    public function test_removeSettingValue_shouldRemoveValue_ShouldNotSaveValueInDb()
    {
        $this->setSuperUser();

        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $this->settings->setSettingValue($setting, '12345657');
        $this->settings->save();

        $this->settings->removeSettingValue($setting);
        $this->assertSettingHasValue($setting, null);

        // should still have same value
        $this->assertSettingIsNotSavedInTheDb('myusersetting', '12345657');
    }

    public function test_getSettingsForCurrentUser_shouldOnlyReturnSettingsHavingEnoughAdminPermissions()
    {
        $this->setUser();

        $this->addSystemSetting('mysystemsetting1', 'mytitle1');
        $this->addSystemSetting('mysystemsetting2', 'mytitle2');
        $this->addSystemSetting('mysystemsetting3', 'mytitle3');
        $this->addSystemSetting('mysystemsetting4', 'mytitle4');
        $userSetting = $this->addUserSetting('myusersetting1', 'mytitle5');

        $this->assertEquals(array('myusersetting1' => $userSetting), $this->settings->getSettingsForCurrentUser());

        // but all of them should be available via getSettings()
        $this->assertCount(5, $this->settings->getSettings());
    }

    public function test_getSettingsForCurrentUser_shouldReturnAllSettingsIfEnoughPermissionsAndSortThemBySettingOrder()
    {
        $this->setSuperUser();

        $this->addSystemSetting('mysystemsetting1', 'mytitle1');
        $this->addSystemSetting('mysystemsetting2', 'mytitle2');
        $this->addUserSetting('myusersetting2', 'mytitle6');
        $this->addSystemSetting('mysystemsetting3', 'mytitle3');
        $this->addSystemSetting('mysystemsetting4', 'mytitle4');
        $this->addUserSetting('myusersetting1', 'mytitle5');

        $expected = array('myusersetting2', 'myusersetting1', 'mysystemsetting1', 'mysystemsetting2', 'mysystemsetting3', 'mysystemsetting4');
        $this->assertEquals($expected, array_keys($this->settings->getSettingsForCurrentUser()));
    }

    public function test_save_shouldSaveAllValues()
    {
        $this->setSuperUser();

        $this->addSystemSetting('mysystemsetting2', 'mytitle2');
        $this->settings->setSettingValue($this->addSystemSetting('mysystemsetting1', 'mytitle1'), '111');
        $this->settings->setSettingValue($this->addSystemSetting('mysystemsetting4', 'mytitle4'), '4444');
        $this->settings->setSettingValue($this->addUserSetting('myusersetting1', 'mytitle5'), '55555');
        $this->addSystemSetting('mysystemsetting3', 'mytitle3');
        $this->settings->save();


        $verifySettings = $this->createSettingsInstance();

        $setting1 = $this->buildSystemSetting('mysystemsetting1', 'mytitle1');
        $setting2 = $this->buildSystemSetting('mysystemsetting2', 'mytitle2');
        $setting3 = $this->buildSystemSetting('mysystemsetting3', 'mytitle3');
        $setting4 = $this->buildSystemSetting('mysystemsetting4', 'mytitle4');
        $setting5 = $this->buildUserSetting('myusersetting1', 'mytitle5');

        $verifySettings->addSetting($setting1);
        $verifySettings->addSetting($setting2);
        $verifySettings->addSetting($setting3);
        $verifySettings->addSetting($setting4);
        $verifySettings->addSetting($setting5);

        $this->assertEquals('111', $verifySettings->getSettingValue($setting1));
        $this->assertEquals(null, $verifySettings->getSettingValue($setting2));
        $this->assertEquals(null, $verifySettings->getSettingValue($setting3));
        $this->assertEquals('4444', $verifySettings->getSettingValue($setting4));
        $this->assertEquals('55555', $verifySettings->getSettingValue($setting5));
    }

    public function test_removeAllPluginSettings_shouldRemoveAllSettings()
    {
        $this->setSuperUser();

        $this->addSystemSetting('mysystemsetting3', 'mytitle3');
        $this->addSystemSetting('mysystemsetting4', 'mytitle4');
        $this->settings->setSettingValue($this->addSystemSetting('mysystemsetting1', 'mytitle1'), '111');
        $this->settings->setSettingValue($this->addSystemSetting('mysystemsetting2', 'mytitle2'), '4444');
        $this->settings->setSettingValue($this->addUserSetting('myusersetting1', 'mytitle5'), '55555');
        $this->settings->save();

        $this->settings->removeAllPluginSettings();

        $verifySettings = $this->createSettingsInstance();

        $setting1 = $this->buildSystemSetting('mysystemsetting1', 'mytitle1');
        $setting2 = $this->buildSystemSetting('mysystemsetting2', 'mytitle2');
        $setting3 = $this->buildSystemSetting('mysystemsetting3', 'mytitle3');
        $setting4 = $this->buildSystemSetting('mysystemsetting4', 'mytitle4');
        $setting5 = $this->buildUserSetting('myusersetting1', 'mytitle5');

        $verifySettings->addSetting($setting1);
        $verifySettings->addSetting($setting2);
        $verifySettings->addSetting($setting3);
        $verifySettings->addSetting($setting4);
        $verifySettings->addSetting($setting5);

        $this->assertEquals(null, $verifySettings->getSettingValue($setting1));
        $this->assertEquals(null, $verifySettings->getSettingValue($setting2));
        $this->assertEquals(null, $verifySettings->getSettingValue($setting3));
        $this->assertEquals(null, $verifySettings->getSettingValue($setting4));
        $this->assertEquals(null, $verifySettings->getSettingValue($setting5));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserIsSuperUser Fake exception
     */
    public function test_removeAllPluginSettings_shouldThrowException_InCaseUserIsNotSuperUser()
    {
        $this->setUser();

        $this->settings->removeAllPluginSettings();
    }

    public function test_userSetting_shouldGenerateDifferentKey_ThenSystemSetting()
    {
        $this->setSuperUser();

        $user   = $this->buildUserSetting('myname', 'mytitle');
        $system = $this->buildSystemSetting('myname', 'mytitle');

        $this->assertNotEquals($user->getKey(), $system->getKey());
        $this->assertEquals('myname', $system->getKey());
        $this->assertEquals('myname#superUserLogin#', $user->getKey());
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

    public function test_userSetting_shouldSaveValuesPerUser()
    {
        $this->setSuperUser();
        $user1Login = 'user1';
        $user2Login = '_user2_';
        $user3Login = null; // current loggged in user

        $user = $this->buildUserSetting('myuser', 'mytitle', $user1Login);

        $this->settings->addSetting($user);

        $this->settings->setSettingValue($user, '111');
        $user->setUserLogin($user2Login);
        $this->settings->setSettingValue($user, '222');
        $user->setUserLogin($user3Login);
        $this->settings->setSettingValue($user, '333');

        $user->setUserLogin($user1Login);
        $this->assertSettingHasValue($user, '111');
        $user->setUserLogin($user2Login);
        $this->assertSettingHasValue($user, '222');
        $user->setUserLogin($user3Login);
        $this->assertSettingHasValue($user, '333');

        $user->setUserLogin($user2Login);
        $this->settings->removeSettingValue($user);

        $user->setUserLogin($user1Login);
        $this->assertSettingHasValue($user, '111');
        $user->setUserLogin($user2Login);
        $this->assertSettingHasValue($user, null);
        $user->setUserLogin($user3Login);
        $this->assertSettingHasValue($user, '333');

        $this->settings->removeAllPluginSettings();

        $user->setUserLogin($user1Login);
        $this->assertSettingHasValue($user, null);
        $user->setUserLogin($user2Login);
        $this->assertSettingHasValue($user, null);
        $user->setUserLogin($user3Login);
        $this->assertSettingHasValue($user, null);
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

    public function test_userSetting_shouldBeAbleToSetLoginAndChangeValues_IfUserIsSuperUser()
    {
        $this->setSuperUser();

        $setting = $this->buildUserSetting('myname', 'mytitle', 'myRandomName');
        $this->settings->addSetting($setting);

        $this->settings->setSettingValue($setting, 5);
        $this->assertSettingHasValue($setting, 5);

        $this->settings->removeSettingValue($setting);
        $this->assertSettingHasValue($setting, null);
    }

    private function buildUserSetting($name, $title, $userLogin = null)
    {
        return new \Piwik\Settings\UserSetting($name, $title, $userLogin);
    }

    private function buildSystemSetting($name, $title)
    {
        return new \Piwik\Settings\SystemSetting($name, $title);
    }

    private function setSuperUser()
    {
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Access::setSingletonInstance($pseudoMockAccess);
    }

    private function setUser()
    {
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$idSitesView = array(1);
        Access::setSingletonInstance($pseudoMockAccess);
    }

    private function addSystemSetting($name, $title)
    {
        $setting = $this->buildSystemSetting($name, $title);
        $this->settings->addSetting($setting);
        return $setting;
    }

    private function addUserSetting($name, $title)
    {
        $setting = $this->buildUserSetting($name, $title);
        $this->settings->addSetting($setting);
        return $setting;
    }

    private function assertSettingHasValue($setting, $expectedValue, $expectedType = null)
    {
        $value = $this->settings->getSettingValue($setting);
        $this->assertEquals($expectedValue, $value);

        if (!is_null($expectedType)) {
            $this->assertInternalType($expectedType, $value);
        }
    }

    private function assertSettingIsNotSavedInTheDb($settingName, $expectedValue)
    {
        // by creating a new instance...
        $setting = $this->buildSystemSetting($settingName, 'mytitle');
        $verifySettings = $this->createSettingsInstance();
        $verifySettings->addSetting($setting);

        $this->assertEquals($expectedValue, $verifySettings->getSettingValue($setting));
    }

    private function createSettingsInstance()
    {
        return new TestablePluginSettings('ExampleSettingsPlugin');
    }
}