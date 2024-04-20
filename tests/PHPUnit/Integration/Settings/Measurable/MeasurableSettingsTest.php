<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings\Plugin;

use Piwik\Db;
use Piwik\Plugins\WebsiteMeasurable\Type;
use Piwik\Settings\Measurable\MeasurableSetting;
use Piwik\Settings\Measurable\MeasurableSettings;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\Settings\FakeMeasurableSettings;
use Piwik\Tests\Integration\Settings\BaseSettingsTestCase;

/**
 * @group PluginSettings
 * @group UserSettings
 */
class MeasurableSettingsTest extends BaseSettingsTestCase
{
    protected $updateEventName = 'MeasurableSettings.updated';

    protected function createSettingsInstance()
    {
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2014-01-01 00:00:01');
        }
        Db::destroyDatabaseObject();
        return new FakeMeasurableSettings($idSite = 1, $type = Type::ID);
    }

    public function test_weAreWorkingWithMeasurableSettings()
    {
        $this->assertTrue($this->settings instanceof MeasurableSettings);
    }

    public function test_constructor_getPluginName_canDetectPluginNameAutomatically()
    {
        $this->assertSame('ExampleSettingsPlugin', $this->settings->getPluginName());

        $settings = new \Piwik\Plugins\ExampleSettingsPlugin\MeasurableSettings($idSite = 1);
        $this->assertSame('ExampleSettingsPlugin', $settings->getPluginName());
    }

    public function test_makeSetting_ShouldCreateAMeasurableSetting()
    {
        $setting = $this->makeSetting('myName');

        $this->assertTrue($setting instanceof MeasurableSetting);
    }
}
