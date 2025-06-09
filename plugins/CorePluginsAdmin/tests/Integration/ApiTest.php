<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\tests\Integration;

use Piwik\Access;
use Piwik\Auth;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\CorePluginsAdmin\SettingsMetadata;
use Piwik\Plugins\CoreUpdater\SystemSettings;
use Piwik\Plugins\UsersManager\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class ApiTest extends IntegrationTestCase
{
    public const TEST_USER = 'atestuser';
    public const TEST_PASSWORD = 'testpassword';

    private $testSystemSettingsPayload = [
        'CoreUpdater' => [
            ['name' => 'release_channel', 'value' => 'latest_beta'],
        ],
    ];

    private $testSystemSettingsEmptyValuePayload = [
        'ExampleSettingsPlugin' => [
            ['name' => 'browsers', 'value' => '__empty__'],
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

    public function testSetSystemSettingsThrowsIfNoPasswordConfirmation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_ConfirmWithPassword');

        $settingValues = $this->testSystemSettingsPayload;
        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->setSystemSettings($settingValues);
    }

    public function testSetSystemSettingsThrowsIfPasswordConfirmationWrong()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('UsersManager_CurrentPasswordNotCorrect');

        $settingValues = $this->testSystemSettingsPayload;
        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->setSystemSettings($settingValues, 'blahblah');
    }

    public function testSetSystemSettingsCorrectlySetsSettings()
    {
        $settingValues = $this->testSystemSettingsPayload;
        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->setSystemSettings($settingValues, self::TEST_PASSWORD);

        $coreUpdaterSettings = StaticContainer::get(SystemSettings::class);
        $value = $coreUpdaterSettings->releaseChannel->getValue();
        $this->assertEquals('latest_beta', $value);
    }

    public function testGetSystemSettingsRedactsPasswordValues()
    {
        $pluginSettings = StaticContainer::get(\Piwik\Plugins\ExampleSettingsPlugin\SystemSettings::class);
        $settings = $this->getPluginSettings('ExampleSettingsPlugin', 'password');

        self::assertEquals('password', $settings['name']);
        self::assertEquals('', $settings['value']);
        self::assertEquals('', $pluginSettings->getSetting('password')->getValue());

        $settingValues = [
            'ExampleSettingsPlugin' => [
                ['name' => 'password', 'value' => 'newPassword'],
            ],
        ];
        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->setSystemSettings($settingValues, self::TEST_PASSWORD);

        $newSettings = $this->getPluginSettings('ExampleSettingsPlugin', 'password');

        self::assertEquals('password', $newSettings['name']);
        self::assertEquals(SettingsMetadata::PASSWORD_PLACEHOLDER, $newSettings['value']); // API returns value redacted
        self::assertTrue(password_verify('newPassword', $pluginSettings->getSetting('password')->getValue()));

        // check that sending the placeholder as value doesn't update the setting
        $settingValues = [
            'ExampleSettingsPlugin' => [
                ['name' => 'password', 'value' => SettingsMetadata::PASSWORD_PLACEHOLDER],
            ],
        ];
        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->setSystemSettings($settingValues, self::TEST_PASSWORD);

        $newSettings = $this->getPluginSettings('ExampleSettingsPlugin', 'password');

        self::assertEquals('password', $newSettings['name']);
        self::assertEquals(SettingsMetadata::PASSWORD_PLACEHOLDER, $newSettings['value']); // API returns value redacted
        self::assertTrue(password_verify('newPassword', $pluginSettings->getSetting('password')->getValue()));
    }

    public function testSetSystemSettingsCorrectlySetsEmptyValue()
    {
        $pluginSettings = StaticContainer::get(\Piwik\Plugins\ExampleSettingsPlugin\SystemSettings::class);
        $settings = $this->getPluginSettings('ExampleSettingsPlugin', 'browsers');
        $defaultValue = ['firefox', 'chromium', 'safari'];

        self::assertEquals('browsers', $settings['name']);
        self::assertEquals($defaultValue, $settings['value']);
        self::assertEquals($defaultValue, $pluginSettings->getSetting('browsers')->getValue());

        $settingValues = $this->testSystemSettingsEmptyValuePayload;
        \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->setSystemSettings($settingValues, self::TEST_PASSWORD);

        $newSettings = $this->getPluginSettings('ExampleSettingsPlugin', 'browsers');

        self::assertEquals('browsers', $newSettings['name']);
        self::assertEquals([], $newSettings['value']);
        self::assertEquals([], $pluginSettings->getSetting('browsers')->getValue());
    }

    private function getPluginSettings(string $pluginName, string $settingName): array
    {
        $settings = \Piwik\Plugins\CorePluginsAdmin\API::getInstance()->getSystemSettings();

        foreach ($settings as $pluginSettings) {
            if ($pluginSettings['pluginName'] === $pluginName) {
                foreach ($pluginSettings['settings'] as $pSetting) {
                    if ($pSetting['name'] === $settingName) {
                        return $pSetting;
                    }
                }
            }
        }

        return [];
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
