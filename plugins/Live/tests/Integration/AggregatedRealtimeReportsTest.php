<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\Live\Live;
use Piwik\Plugins\Live\MeasurableSettings;
use Piwik\Plugins\Live\SystemSettings;
use Piwik\Plugins\Live\Widgets\Widget;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetConfig;

/**
 * @group Live
 * @group AggregatedRealtimeReportsTest
 * @group Plugins
 */
class AggregatedRealtimeReportsTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->setSuperUser();
        Fixture::createWebsite('2010-01-01');
        Fixture::createWebsite('2010-01-01');
    }

    public function testShouldShowAggregatedRealtimeOnlyFollowsTheVisitsLogState()
    {
        // visits log enabled => full widget
        $this->assertTrue(Live::isVisitorLogEnabled(1));
        $this->assertFalse(Live::shouldShowAggregatedRealtimeOnly(1));

        // visits log disabled for one site only => aggregated-only for that site
        $this->updateSiteLiveSettings(1, [['name' => 'disable_visitor_log', 'value' => '1']]);
        $this->assertTrue(Live::shouldShowAggregatedRealtimeOnly(1));
        $this->assertFalse(Live::shouldShowAggregatedRealtimeOnly(2));

        // visits log disabled globally => aggregated-only for every site
        $this->disableVisitorLog(true);
        $this->assertTrue(Live::shouldShowAggregatedRealtimeOnly(1));
        $this->assertTrue(Live::shouldShowAggregatedRealtimeOnly(2));
    }

    public function testWidgetIsNeverDisabled()
    {
        $this->assertTrue($this->configureWidgetForSite(1)->isEnabled());

        $this->updateSiteLiveSettings(1, [['name' => 'disable_visitor_log', 'value' => '1']]);
        $this->assertTrue($this->configureWidgetForSite(1)->isEnabled());

        $this->disableVisitorLog(true);
        $this->assertTrue($this->configureWidgetForSite(1)->isEnabled());
        $this->assertTrue($this->configureWidgetForSite(2)->isEnabled());
    }

    public function testTheGlobalAndPerSiteSettingsAreGone()
    {
        // the visits log being disabled is the state that used to register them
        $this->disableVisitorLog(true);

        $this->assertNull((new SystemSettings())->getSetting('enable_aggregated_realtime_reports'));
        $this->assertNull((new MeasurableSettings(1))->getSetting('enable_aggregated_realtime_reports'));
    }

    public function testStoredValuesOfTheRemovedSettingAreIgnored()
    {
        // installations that saved the removed opt-in before it went away still have it stored as off
        Db::query(
            'INSERT INTO ' . Common::prefixTable('plugin_setting') . ' (plugin_name, setting_name, setting_value) VALUES (?, ?, ?)',
            ['Live', 'enable_aggregated_realtime_reports', '0']
        );
        Db::query(
            'INSERT INTO ' . Common::prefixTable('site_setting') . ' (idsite, plugin_name, setting_name, setting_value) VALUES (?, ?, ?, ?)',
            [1, 'Live', 'enable_aggregated_realtime_reports', '0']
        );

        $this->disableVisitorLog(true);

        $this->assertTrue(Live::shouldShowAggregatedRealtimeOnly(1));
        $this->assertTrue($this->configureWidgetForSite(1)->isEnabled());
    }

    private function updateSiteLiveSettings(int $idSite, array $liveSettings): void
    {
        \Piwik\Plugins\SitesManager\API::getInstance()->updateSite(
            $idSite,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            ['Live' => $liveSettings]
        );
    }

    private function configureWidgetForSite(int $idSite): WidgetConfig
    {
        $_GET['idSite'] = $idSite;
        $config = new WidgetConfig();
        Widget::configure($config);
        unset($_GET['idSite']);

        return $config;
    }

    private function disableVisitorLog(bool $value): void
    {
        $settings = new SystemSettings();
        $settings->disableVisitorLog->setValue($value);
        $settings->save();
    }

    protected function setSuperUser()
    {
        FakeAccess::$superUser = true;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
