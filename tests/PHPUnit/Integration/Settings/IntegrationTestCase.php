<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use PHPUnit\Framework\Constraint\IsType;
use Piwik\Db;
use Piwik\Settings\Setting;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\Mock\Settings\FakeSystemSettings;

/**
 * @group PluginSettings
 * @group Settings
 * @group Storage
 */
class IntegrationTestCase extends \Piwik\Tests\Framework\TestCase\IntegrationTestCase
{
    /**
     * @var FakeSystemSettings
     */
    protected $settings;

    public function setUp(): void
    {
        parent::setUp();
        Db::destroyDatabaseObject();
        $this->settings = $this->createSettingsInstance();
    }

    protected function assertSettingHasValue(Setting $setting, $expectedValue, $expectedType = null)
    {
        $value = $setting->getValue();
        $this->assertEquals($expectedValue, $value);

        if (!is_null($expectedType)) {
            static::assertThat(
                $value,
                new IsType($expectedType)
            );
        }
    }

    protected function setSuperUser()
    {
        FakeAccess::$superUser = true;
    }

    protected function setUser()
    {
        FakeAccess::clearAccess();
        FakeAccess::$idSitesView = array(1);
    }

    protected function setAnonymousUser()
    {
        FakeAccess::clearAccess();
    }

    protected function createSettingsInstance()
    {
        return new FakeSystemSettings();
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
        );
    }
}
