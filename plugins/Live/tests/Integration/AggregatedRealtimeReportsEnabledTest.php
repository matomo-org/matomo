<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Integration;

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
        // Exercises the same path as the admin UI: SitesManager.updateSite only persists settings
        // that are writable by the current user, so a regression there would silently drop the value.
        \Piwik\Plugins\SitesManager\API::getInstance()->updateSite(
            1,
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
            [
                'Live' => [
                    ['name' => 'disable_visitor_log', 'value' => '1'],
                    ['name' => 'enable_aggregated_realtime_reports', 'value' => '1'],
                ],
            ]
        );

        $this->assertTrue(AggregatedRealtimeReportsEnabled::getMeasurableValue(1));
        $this->assertTrue(Live::isAggregatedRealtimeEnabled(1));
        $this->assertFalse(Live::isVisitorLogEnabled(1));
        $this->assertTrue(Live::shouldShowAggregatedRealtimeOnly(1));

        // and the per-site value is scoped to the site it was set for
        $this->assertFalse(AggregatedRealtimeReportsEnabled::getMeasurableValue(2));
        $this->assertTrue($this->configureWidgetForSite(1)->isEnabled());
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
