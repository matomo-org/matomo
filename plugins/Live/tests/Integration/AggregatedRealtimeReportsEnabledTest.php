<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Integration;

use Piwik\Policy\CnilPolicy;
use Piwik\Plugins\Live\Live;
use Piwik\Plugins\Live\MeasurableSettings;
use Piwik\Plugins\Live\Settings\AggregatedRealtimeReportsEnabled;
use Piwik\Plugins\Live\SystemSettings;
use Piwik\Plugins\Live\Widgets\Widget;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetConfig;

/**
 * @group Live
 * @group AggregatedRealtimeReportsEnabledTest
 * @group Plugins
 */
class AggregatedRealtimeReportsEnabledTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->setSuperUser();
        Fixture::createWebsite('2010-01-01');
        Fixture::createWebsite('2010-01-01');
    }

    public function tearDown(): void
    {
        CnilPolicy::setActiveStatus(null, false);
        parent::tearDown();
    }

    public function testDefaultsToDisabled()
    {
        $this->assertFalse(AggregatedRealtimeReportsEnabled::getSystemValue());
        $this->assertFalse(AggregatedRealtimeReportsEnabled::getMeasurableValue(1));
        $this->assertFalse(AggregatedRealtimeReportsEnabled::getInstance()->getValue());
        $this->assertFalse(AggregatedRealtimeReportsEnabled::getInstance(1)->getValue());
        $this->assertFalse(Live::isAggregatedRealtimeEnabled(1));
    }

    public function testSystemSettingEnablesItGlobally()
    {
        $this->setSystemValue(true);

        $this->assertTrue(AggregatedRealtimeReportsEnabled::getInstance()->getValue());
        $this->assertTrue(AggregatedRealtimeReportsEnabled::getInstance(1)->getValue());
        $this->assertTrue(Live::isAggregatedRealtimeEnabled(1));
    }

    public function testMeasurableSettingOnlyEnablesItForThatSite()
    {
        $this->setMeasurableValue(1, true);

        $this->assertTrue(AggregatedRealtimeReportsEnabled::getInstance(1)->getValue());
        $this->assertTrue(Live::isAggregatedRealtimeEnabled(1));

        $this->assertFalse(AggregatedRealtimeReportsEnabled::getInstance(2)->getValue());
        $this->assertFalse(Live::isAggregatedRealtimeEnabled(2));
    }

    public function testShouldShowAggregatedRealtimeOnlyRequiresVisitsLogDisabledAndSettingEnabled()
    {
        // visits log enabled + setting enabled => not aggregated-only (full widget)
        $this->setSystemValue(true);
        $this->assertTrue(Live::isVisitorLogEnabled(1));
        $this->assertFalse(Live::shouldShowAggregatedRealtimeOnly(1));

        // visits log disabled + setting disabled => not shown at all
        $this->setSystemValue(false);
        $this->disableVisitorLog(true);
        $this->assertFalse(Live::isVisitorLogEnabled(1));
        $this->assertFalse(Live::shouldShowAggregatedRealtimeOnly(1));

        // visits log disabled + setting enabled => aggregated-only
        $this->setSystemValue(true);
        $this->assertTrue(Live::shouldShowAggregatedRealtimeOnly(1));
    }

    public function testWidgetIsDisabledWhenVisitsLogDisabledAndSettingDisabled()
    {
        $this->disableVisitorLog(true);

        $config = $this->configureWidgetForSite(1);

        $this->assertFalse($config->isEnabled());
    }

    public function testWidgetStaysEnabledWhenVisitsLogDisabledButSettingEnabled()
    {
        $this->disableVisitorLog(true);
        $this->setSystemValue(true);

        $config = $this->configureWidgetForSite(1);

        $this->assertTrue($config->isEnabled());
    }

    public function testEnablingPerSiteViaUpdateSiteTakesEffect()
    {
        // Exercises the same path as the admin UI: SitesManager.updateSite only persists settings that
        // are writable by the current user. The aggregated setting is only exposed (writable) once the
        // Visits log is disabled, so it has to be enabled in a second step.
        $this->updateSiteLiveSettings(1, [['name' => 'disable_visitor_log', 'value' => '1']]);
        $this->updateSiteLiveSettings(1, [['name' => 'enable_aggregated_realtime_reports', 'value' => '1']]);

        $this->assertTrue(AggregatedRealtimeReportsEnabled::getMeasurableValue(1));
        $this->assertTrue(Live::isAggregatedRealtimeEnabled(1));
        $this->assertFalse(Live::isVisitorLogEnabled(1));
        $this->assertTrue(Live::shouldShowAggregatedRealtimeOnly(1));

        // and the per-site value is scoped to the site it was set for
        $this->assertFalse(AggregatedRealtimeReportsEnabled::getMeasurableValue(2));
        $this->assertTrue($this->configureWidgetForSite(1)->isEnabled());
    }

    public function testMeasurableSettingIsOnlyExposedWhenTheSiteVisitsLogIsDisabled()
    {
        // Visits log enabled (default): the setting is not registered in the site settings form,
        // so it does not depend on a client-side condition that can break when disable_visitor_log
        // is non-writable.
        $settings = new MeasurableSettings(1);
        $this->assertNull($settings->getSetting('enable_aggregated_realtime_reports'));

        $this->updateSiteLiveSettings(1, [['name' => 'disable_visitor_log', 'value' => '1']]);

        $settings = new MeasurableSettings(1);
        $this->assertNotNull($settings->getSetting('enable_aggregated_realtime_reports'));
    }

    public function testSystemSettingIsOnlyExposedWhenTheVisitsLogIsDisabledGlobally()
    {
        $settings = new SystemSettings();
        $this->assertNull($settings->getSetting('enable_aggregated_realtime_reports'));

        $this->disableVisitorLog(true);

        $settings = new SystemSettings();
        $this->assertNotNull($settings->getSetting('enable_aggregated_realtime_reports'));
    }

    public function testCnilForcesSettingEnabledAndCompliant()
    {
        // stored value is off, so the CNIL "must be enabled" requirement is not yet met
        $this->assertFalse(AggregatedRealtimeReportsEnabled::getInstance(1)->getValue());
        $this->assertFalse(AggregatedRealtimeReportsEnabled::isCompliant(CnilPolicy::class, 1));

        CnilPolicy::setActiveStatus(null, true);

        // CNIL forces the setting on at read time regardless of the stored value, and it is now compliant
        $this->assertTrue(AggregatedRealtimeReportsEnabled::getInstance(1)->getValue());
        $this->assertTrue(AggregatedRealtimeReportsEnabled::isCompliant(CnilPolicy::class, 1));
        $this->assertTrue(Live::isAggregatedRealtimeEnabled(1));
    }

    public function testCnilLocksThePerSiteSetting()
    {
        $settings = new MeasurableSettings(1);
        $this->assertTrue($settings->enableAggregatedRealtimeReports->isWritableByCurrentUser());

        CnilPolicy::setActiveStatus(null, true);

        $settings = new MeasurableSettings(1);
        $this->assertFalse($settings->enableAggregatedRealtimeReports->isWritableByCurrentUser());
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

    private function setSystemValue(bool $value): void
    {
        $settings = new SystemSettings();
        $settings->enableAggregatedRealtimeReports->setValue($value);
        $settings->save();
    }

    private function setMeasurableValue(int $idSite, bool $value): void
    {
        $settings = new MeasurableSettings($idSite);
        $settings->enableAggregatedRealtimeReports->setValue($value);
        $settings->save();
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
