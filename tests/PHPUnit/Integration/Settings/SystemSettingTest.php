<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Config;
use Piwik\Db;
use Piwik\Plugin\Settings;
use Piwik\Settings\SystemSetting;

/**
 * @group PluginSettings
 * @group Settings
 * @group SystemSetting
 */
class SystemSettingTest extends IntegrationTestCase
{

    public function tearDown()
    {
        Config::getInstance()->MyPluginName = array();
        parent::tearDown();
    }

    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        new SystemSetting('name', 'title');

        $this->assertNotDbConnectionCreated();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_setSettingValue_shouldThrowException_IfAUserIsTryingToSetASettingWhichNeedsSuperUserPermission()
    {
        $this->setUser();
        $setting = $this->addSystemSetting('mysystem', 'mytitle');

        $setting->setValue(2);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_setSettingValue_shouldThrowException_IfAnonymousIsTryingToSetASettingWhichNeedsSuperUserPermission()
    {
        $this->setAnonymousUser();
        $setting = $this->addSystemSetting('mysystem', 'mytitle');

        $setting->setValue(2);
    }

    public function test_setSettingValue_shouldSucceed_IfSuperUserTriesToSaveASettingWhichRequiresSuperUserPermission()
    {
        $this->setSuperUser();

        $setting = $this->addSystemSetting('mysystem', 'mytitle');
        $setting->setValue(2);

        $this->assertSettingHasValue($setting, 2);
    }

    public function test_setSettingValue_shouldNotPersistValueInDatabase_OnSuccess()
    {
        $this->setSuperUser();

        $setting = $this->buildSystemSetting('mysystem', 'mytitle');
        $this->settings->addSetting($setting);
        $setting->setValue(2);

        // make sure stored on the instance
        $this->assertSettingHasValue($setting, 2);
        $this->assertSettingIsNotSavedInTheDb('mysystem', null);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingReadNotAllowed
     */
    public function test_getSettingValue_shouldThrowException_IfUserHasNotEnoughPermissionToReadValue()
    {
        $this->setUser();
        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $setting->getValue();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingReadNotAllowed
     */
    public function test_getSettingValue_shouldThrowException_IfAnonymousTriedToReadValue()
    {
        $this->setAnonymousUser();
        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $setting->getValue();
    }

    public function test_getSettingValue_shouldBeReadableBySuperUser()
    {
        $this->setSuperUser();
        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $this->assertEquals('', $setting->getValue());
    }

    public function test_getSettingValue_shouldReturnValue_IfReadbleByCurrentUserIsAllowed()
    {
        $this->setUser();
        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $setting->readableByCurrentUser = true;

        $this->assertEquals('', $setting->getValue());
    }

    public function test_getSettingValue_fromConfig_IfOneIsConfiguredInsteadOfTheValueFromDatabase()
    {
        $this->setSuperUser();
        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $setting->setPluginName('MyPluginName');
        $setting->setValue('test');
        $this->assertEquals('test', $setting->getValue());

        Config::getInstance()->MyPluginName = array('myusersetting' => 'mynewvalue');
        $value = $setting->getValue();
        $this->assertEquals('mynewvalue', $value);
    }

    public function test_getSettingValue_fromConfig_ShouldConvertToTheSpecifiedType()
    {
        $this->setSuperUser();
        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $setting->setPluginName('MyPluginName');

        Config::getInstance()->MyPluginName = array('myusersetting' => '1');

        $this->assertSame('1', $setting->getValue());

        $setting->type = Settings::TYPE_BOOL;
        $this->assertTrue($setting->getValue());
    }

    public function test_getSettingValue_fromConfig_isCaseSensitive()
    {
        $this->setSuperUser();
        $setting = $this->addSystemSetting('myUsersetting', 'mytitle');
        $setting->setPluginName('MyPluginName');

        Config::getInstance()->MyPluginName = array('myusersetting' => '1');

        $this->assertNull($setting->getValue());

        Config::getInstance()->MyPluginName = array('myUsersetting' => '1');

        $this->assertSame('1', $setting->getValue());
    }

    public function test_getSettingsValue_fromConfig_ShouldSetObjectToNotWritableAsSoonAsAValueIsConfigured()
    {
        $this->setSuperUser();
        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $setting->setPluginName('MyPluginName');

        $this->assertTrue($setting->isWritableByCurrentUser());

        Config::getInstance()->MyPluginName = array('myusersetting' => '0');
        $this->assertFalse($setting->isWritableByCurrentUser());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_setSettingsValue_shouldNotBePossible_AsSoonAsAConfigValueIsConfigured()
    {
        $this->setSuperUser();
        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $setting->setPluginName('MyPluginName');

        Config::getInstance()->MyPluginName = array('myusersetting' => '0');
        $setting->setValue('test');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_removeSettingValue_shouldThrowException_IfUserHasNotEnoughAdminPermissions()
    {
        $this->setUser();
        $setting = $this->addSystemSetting('mysystemsetting', 'mytitle');
        $setting->removeValue();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage CoreAdminHome_PluginSettingChangeNotAllowed
     */
    public function test_removeSettingValue_shouldThrowException_IfAnonymousTriesToRemoveValue()
    {
        $this->setAnonymousUser();
        $setting = $this->addSystemSetting('mysystemsetting', 'mytitle');
        $setting->removeValue();
    }

    public function test_removeSettingValue_shouldRemoveValue_ShouldNotSaveValueInDb()
    {
        $this->setSuperUser();

        $setting = $this->addSystemSetting('myusersetting', 'mytitle');
        $setting->setValue('12345657');
        $this->settings->save();

        $setting->removeValue();
        $this->assertSettingHasValue($setting, null);

        // should still have same value
        $this->assertSettingIsNotSavedInTheDb('myusersetting', '12345657');
    }
}
