<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Db;
use Piwik\Plugin\Settings as PluginSettings;
use Piwik\Settings\Storage;
use Piwik\SettingsServer;
use Piwik\Tests\Integration\Settings\CorePluginTestSettings;
use Piwik\Tests\Integration\Settings\IntegrationTestCase;
use Piwik\Tracker\Cache;
use Piwik\Tracker\SettingsStorage;

/**
 * @group PluginSettings
 */
class SettingsTest extends IntegrationTestCase
{

    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        Db::destroyDatabaseObject();

        $this->assertNotDbConnectionCreated();

        $this->createSettingsInstance();

        $this->assertNotDbConnectionCreated();
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
     * @expectedExceptionMessage The setting name "myname-" in plugin "ExampleSettingsPlugin" is not valid. Only underscores, alpha and numerical characters are allowed
     */
    public function test_addSetting_shouldThrowException_IfTheSettingNameIsNotValid()
    {
        $setting = $this->buildUserSetting('myname-', 'mytitle');
        $this->settings->addSetting($setting);
    }

    public function test_addSetting_shouldAssignDefaultType_IfFieldIsGivenButNoType()
    {
        $setting = $this->buildUserSetting('myname', 'mytitle');
        $setting->uiControlType = CorePluginTestSettings::CONTROL_MULTI_SELECT;

        $this->settings->addSetting($setting);

        $this->assertEquals(CorePluginTestSettings::TYPE_ARRAY, $setting->type);
    }

    public function test_addSetting_shouldAssignDefaultField_IfTypeIsGivenButNoField()
    {
        $setting = $this->buildUserSetting('myname', 'mytitle');
        $setting->type = CorePluginTestSettings::TYPE_ARRAY;

        $this->settings->addSetting($setting);

        $this->assertEquals(CorePluginTestSettings::CONTROL_MULTI_SELECT, $setting->uiControlType);
    }

    public function test_addSetting_shouldAddAValidator_IfFieldOptionsAreGiven()
    {
        $setting = $this->buildUserSetting('myname', 'mytitle');
        $setting->availableValues = array('allowedval' => 'DisplayName', 'allowedval2' => 'Name 2');

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

        $storage = $setting->getStorage();
        $this->assertTrue($storage instanceof Storage);

        $setting->setValue(5);
        $this->assertSettingHasValue($setting, 5);

        $this->assertEquals($this->settings->getSetting('myname')->getValue(), 5);
    }

    public function test_addSetting_shouldPassTrackerStorage_IfInTrackerMode()
    {
        $this->setSuperUser();

        SettingsServer::setIsTrackerApiRequest();

        $settings = $this->createSettingsInstance();
        $setting = $this->buildUserSetting('myname', 'mytitle', 'myRandomName');
        $settings->addSetting($setting);

        SettingsServer::setIsNotTrackerApiRequest();

        $storage = $setting->getStorage();
        $this->assertTrue($storage instanceof SettingsStorage);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingsValueNotAllowed
     */
    public function test_setSettingValue_shouldApplyValidationAndFail_IfOptionsAreSet()
    {
        $this->setUser();
        $setting = $this->buildUserSetting('mysystem', 'mytitle');
        $setting->availableValues = array('allowed' => 'text', 'allowed2' => 'text2');
        $this->settings->addSetting($setting);
        $setting->setValue('notallowed');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingsValueNotAllowed
     */
    public function test_setSettingValue_shouldApplyValidationAndFail_IfOptionsAreSetAndValueIsAnArray()
    {
        $this->setUser();
        $setting = $this->buildUserSetting('mysystem', 'mytitle');
        $setting->availableValues = array('allowed' => 'text', 'allowed2' => 'text2');
        $setting->uiControlType        = PluginSettings::CONTROL_MULTI_SELECT;

        $this->settings->addSetting($setting);

        $setting->setValue(array('allowed', 'notallowed'));
    }

    public function test_setSettingValue_shouldApplyValidationAndSucceed_IfOptionsAreSet()
    {
        $this->setUser();
        $setting = $this->buildUserSetting('mysystem', 'mytitle');
        $setting->availableValues = array('allowed' => 'text', 'allowed2' => 'text2');
        $setting->uiControlType        = PluginSettings::CONTROL_MULTI_SELECT;

        $this->settings->addSetting($setting);

        $setting->setValue(array('allowed', 'allowed2'));
        $this->assertSettingHasValue($setting, array('allowed', 'allowed2'));

        $setting->type = PluginSettings::TYPE_STRING;
        $setting->setValue('allowed');
        $this->assertSettingHasValue($setting, 'allowed');
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
        $this->skipWhenPhp53();
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
        $this->addSystemSetting('mysystemsetting1', 'mytitle1')->setValue('111');
        $this->addSystemSetting('mysystemsetting4', 'mytitle4')->setValue('4444');
        $this->addUserSetting('myusersetting1', 'mytitle5')->setValue('55555');
        $this->addSystemSetting('mysystemsetting3', 'mytitle3');

        $this->settings->save();

        // verify actually saved
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

        $this->assertEquals('111', $setting1->getValue());
        $this->assertEquals(null, $setting2->getValue());
        $this->assertEquals(null, $setting3->getValue());
        $this->assertEquals('4444', $setting4->getValue());
        $this->assertEquals('55555', $setting5->getValue());
    }


    public function test_save_shouldClearTrackerCacheEntries()
    {
        $this->setSuperUser();

        Cache::setCacheGeneral(array('testSetting' => 1));

        $this->assertArrayHasKey('testSetting', Cache::getCacheGeneral());

        $this->addSystemSetting('mysystemsetting2', 'mytitle2');
        $this->settings->save();

        $this->assertArrayNotHasKey('testSetting', Cache::getCacheGeneral());
    }

    public function test_removeAllPluginSettings_shouldRemoveAllSettings()
    {
        $this->setSuperUser();

        $this->addSystemSetting('mysystemsetting3', 'mytitle3');
        $this->addSystemSetting('mysystemsetting4', 'mytitle4');
        $this->addSystemSetting('mysystemsetting1', 'mytitle1')->setValue('111');
        $this->addSystemSetting('mysystemsetting2', 'mytitle2')->setValue('4444');
        $this->addUserSetting('myusersetting1', 'mytitle5')->setValue('55555');
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

        $this->assertEquals(null, $setting1->getValue());
        $this->assertEquals(null, $setting2->getValue());
        $this->assertEquals(null, $setting3->getValue());
        $this->assertEquals(null, $setting4->getValue());
        $this->assertEquals(null, $setting5->getValue());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasSuperUserAccess Fake exception
     */
    public function test_removeAllPluginSettings_shouldThrowException_InCaseUserIsNotSuperUser()
    {
        $this->setUser();

        $this->settings->removeAllPluginSettings();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasSuperUserAccess Fake exception
     */
    public function test_removeAllPluginSettings_shouldThrowException_InCaseAnonymousUser()
    {
        $this->setAnonymousUser();

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

    public function test_userSetting_shouldSaveValuesPerUser()
    {
        $this->setSuperUser();
        $user1Login = 'user1';
        $user2Login = '_user2_';
        $user3Login = null; // current loggged in user

        $user = $this->buildUserSetting('myuser', 'mytitle', $user1Login);

        $this->settings->addSetting($user);

        $user->setValue('111');
        $user->setUserLogin($user2Login);
        $user->setValue('222');
        $user->setUserLogin($user3Login);
        $user->setValue('333');

        $user->setUserLogin($user1Login);
        $this->assertSettingHasValue($user, '111');
        $user->setUserLogin($user2Login);
        $this->assertSettingHasValue($user, '222');
        $user->setUserLogin($user3Login);
        $this->assertSettingHasValue($user, '333');

        $user->setUserLogin($user2Login);
        $user->removeValue();

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

    public function test_construct_shouldDetectTheNameOfThePluginAutomatically_IfPluginNameNotGiven()
    {
        $setting = new \Piwik\Plugins\ExampleSettingsPlugin\Settings();

        $this->assertEquals('ExampleSettingsPlugin', $setting->getPluginName());
    }
}
