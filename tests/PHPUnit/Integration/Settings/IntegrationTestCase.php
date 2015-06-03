<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Access;
use Piwik\Db;
use Piwik\Settings\Setting;
use Piwik\Settings\Storage;
use Piwik\Tests\Framework\Mock\FakeAccess;

/**
 * @group PluginSettings
 * @group Settings
 * @group Storage
 */
class IntegrationTestCase extends \Piwik\Tests\Framework\TestCase\IntegrationTestCase
{
    /**
     * @var CorePluginTestSettings
     */
    protected $settings;

    public function setUp()
    {
        parent::setUp();
        Access::setSingletonInstance(null);
        Db::destroyDatabaseObject();
        $this->settings = $this->createSettingsInstance();
    }

    public function tearDown()
    {
        $this->setSuperUser();
        if ($this->settings) {
            $this->settings->removeAllPluginSettings();
        }

        parent::tearDown();
    }

    public function test_constructor_shouldNotEstablishADatabaseConnection()
    {
        $this->assertNotDbConnectionCreated();

        new Storage('PluginName');

        $this->assertNotDbConnectionCreated();
    }

    protected function assertSettingHasValue(Setting $setting, $expectedValue, $expectedType = null)
    {
        $value = $setting->getValue($setting);
        $this->assertEquals($expectedValue, $value);

        if (!is_null($expectedType)) {
            $this->assertInternalType($expectedType, $value);
        }
    }

    protected function buildUserSetting($name, $title, $userLogin = null)
    {
        $userSetting = new \Piwik\Settings\UserSetting($name, $title, $userLogin);
        $userSetting->setStorage(new Storage('ExampleSettingsPlugin'));

        return $userSetting;
    }

    protected function buildSystemSetting($name, $title)
    {
        $systemSetting = new \Piwik\Settings\SystemSetting($name, $title);
        $systemSetting->setStorage(new Storage('ExampleSettingsPlugin'));

        return $systemSetting;
    }

    protected function setSuperUser()
    {
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Access::setSingletonInstance($pseudoMockAccess);
    }

    protected function setUser()
    {
        $pseudoMockAccess = new FakeAccess();
        FakeAccess::$idSitesView = array(1);
        Access::setSingletonInstance($pseudoMockAccess);
    }

    protected function setAnonymousUser()
    {
        FakeAccess::clearAccess();
        Access::setSingletonInstance(new FakeAccess());
    }

    protected function createSettingsInstance()
    {
        return new CorePluginTestSettings('ExampleSettingsPlugin');
    }

    protected function addSystemSetting($name, $title)
    {
        $setting = $this->buildSystemSetting($name, $title);
        $this->settings->addSetting($setting);
        return $setting;
    }

    protected function addUserSetting($name, $title)
    {
        $setting = $this->buildUserSetting($name, $title);
        $this->settings->addSetting($setting);
        return $setting;
    }


    protected function assertSettingIsNotSavedInTheDb($settingName, $expectedValue)
    {
        // by creating a new instance...
        $setting = $this->buildSystemSetting($settingName, 'mytitle');
        $verifySettings = $this->createSettingsInstance();
        $verifySettings->addSetting($setting);

        $this->assertEquals($expectedValue, $setting->getValue());
    }
}
