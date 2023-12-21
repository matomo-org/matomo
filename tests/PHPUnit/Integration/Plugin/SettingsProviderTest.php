<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Plugin;
use Piwik\Plugin\SettingsProvider;
use Piwik\Settings\Measurable\MeasurableSettings;
use Piwik\Settings\Plugin\SystemSettings;
use Piwik\Settings\Plugin\UserSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SettingsProvider
 * @group SettingsProviderTest
 */
class SettingsProviderTest extends IntegrationTestCase
{
    /**
     * @var SettingsProvider
     */
    private $settings;

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    private $examplePlugin = 'ExampleSettingsPlugin';

    public function setUp(): void
    {
        parent::setUp();

        $_GET['idSite'] = 1;
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2015-01-01 00:00:00');
        }

        $this->pluginManager = StaticContainer::get('Piwik\Plugin\Manager');
        $this->settings = new SettingsProvider($this->pluginManager);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($_GET['idSite']);
    }

    public function test_getSystemSettings_shouldFindASystemSettingOfPlugin()
    {
        $settings = $this->settings->getSystemSettings($this->examplePlugin);

        $this->assertTrue($settings instanceof SystemSettings);
        $this->assertSame($this->examplePlugin, $settings->getPluginName());
    }

    public function test_getSystemSettings_shouldReturnNull_IfPluginHasNoSystemSettings()
    {
        $settings = $this->settings->getSystemSettings('Intl');

        $this->assertNull($settings);
    }

    public function test_getSystemSettings_shouldReturnNull_IfPluginHasSettingButIsNotLoaded()
    {
        $this->pluginManager->unloadPlugin($this->examplePlugin);
        $settings = $this->settings->getSystemSettings($this->examplePlugin);
        $this->pluginManager->loadPlugin($this->examplePlugin);

        $this->assertNull($settings);
    }

    public function test_getAllSystemSettings_shouldFindAllSystemSettings()
    {
        $settings = $this->settings->getAllSystemSettings();

        $this->assertArrayHasKey($this->examplePlugin, $settings);
        $this->assertArrayHasKey('AnonymousPiwikUsageMeasurement', $settings);
        $this->assertArrayHasKey('QueuedTracking', $settings);

        foreach ($settings as $setting) {
            $this->assertTrue($setting instanceof SystemSettings);
        }
    }

    public function test_getUserSettings_shouldFindASystemSettingOfPlugin()
    {
        $settings = $this->settings->getUserSettings($this->examplePlugin);

        $this->assertTrue($settings instanceof UserSettings);
        $this->assertSame($this->examplePlugin, $settings->getPluginName());
    }

    public function test_getUserSettings_shouldReturnNull_IfPluginHasNoSystemSettings()
    {
        $settings = $this->settings->getUserSettings('Intl');

        $this->assertNull($settings);
    }

    public function test_getUserSettings_shouldReturnNull_IfPluginHasSettingButIsNotLoaded()
    {
        $this->pluginManager->unloadPlugin($this->examplePlugin);
        $settings = $this->settings->getUserSettings($this->examplePlugin);
        $this->pluginManager->loadPlugin($this->examplePlugin);

        $this->assertNull($settings);
    }

    public function test_getAllUserSettings_shouldFindAllSystemSettings()
    {
        $settings = $this->settings->getAllUserSettings();

        $this->assertArrayHasKey($this->examplePlugin, $settings);

        foreach ($settings as $setting) {
            $this->assertTrue($setting instanceof UserSettings);
        }
    }

    public function test_getMeasurableSettings_shouldFindASystemSettingOfPlugin()
    {
        $settings = $this->settings->getMeasurableSettings($this->examplePlugin, $idSite = 1, $idType = null);

        $this->assertTrue($settings instanceof MeasurableSettings);
        $this->assertSame($this->examplePlugin, $settings->getPluginName());
    }

    public function test_getMeasurableSettings_shouldReturnNull_IfPluginHasNoSystemSettings()
    {
        $settings = $this->settings->getMeasurableSettings('Intl', $idSite = 1, $idType = null);

        $this->assertNull($settings);
    }

    public function test_getMeasurableSettings_shouldReturnNull_IfPluginHasSettingButIsNotLoaded()
    {
        $this->pluginManager->unloadPlugin($this->examplePlugin);
        $settings = $this->settings->getMeasurableSettings($this->examplePlugin, $idSite = 1, $idType = null);
        $this->pluginManager->loadPlugin($this->examplePlugin);

        $this->assertNull($settings);
    }

    public function test_getAllMeasurableSettings_shouldReturnOnlyMeasurableSettings()
    {
        $settings = $this->settings->getAllMeasurableSettings($idSite = 1, $idType = null);
        $this->assertArrayHasKey($this->examplePlugin, $settings);

        foreach ($settings as $setting) {
            $this->assertTrue($setting instanceof MeasurableSettings);
        }
    }
}
