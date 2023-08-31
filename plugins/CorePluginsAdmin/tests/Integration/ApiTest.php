<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\tests\Integration;

use Piwik\Access;
use Piwik\Auth;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\CoreUpdater\SystemSettings;
use Piwik\Plugins\UsersManager\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ApiTest extends IntegrationTestCase
{
    const TEST_USER = 'atestuser';
    const TEST_PASSWORD = 'testpassword';

    private $testSystemSettingsPayload = [
        'CoreUpdater' => [
            ['name' => 'release_channel', 'value' => 'latest_beta'],
        ],
    ];

    protected static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        API::getInstance()->addUser(self::TEST_USER, self::TEST_PASSWORD, 'someuser@email.com');
        API::getInstance()->setSuperUserAccess(self::TEST_USER, true, Fixture::ADMIN_USER_PASSWORD);
    }

    public function setUp(): void
    {
        parent::setUp();

        Access::getInstance()->setSuperUserAccess(false);
        $auth = StaticContainer::get(Auth::class);
        $auth->setLogin(self::TEST_USER);
        $auth->setPassword(self::TEST_PASSWORD);
        Access::getInstance()->reloadAccess($auth);
    }

    public function test_setSystemSettings_throwsIfNoPasswordConfirmation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ConfirmWithPassword');

        $settingValues = $this->testSystemSettingsPayload;
        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->setSystemSettings($settingValues);
    }

    public function test_setSystemSettings_throwsIfPasswordConfirmationWrong()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_CurrentPasswordNotCorrect');

        $settingValues = $this->testSystemSettingsPayload;
        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->setSystemSettings($settingValues, 'blahblah');
    }

    public function test_setSystemSettings_correctlySetsSettings()
    {
        $settingValues = $this->testSystemSettingsPayload;
        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->setSystemSettings($settingValues, self::TEST_PASSWORD);

        $coreUpdaterSettings = StaticContainer::get(SystemSettings::class);
        $value = $coreUpdaterSettings->releaseChannel->getValue();
        $this->assertEquals('latest_beta', $value);
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
