<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ViewDataTable;

use Piwik\Plugin\ReportsProvider;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 * @group Core
 * @group ViewDataTable
 */
class ConfigTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-01 00:00:00');
        $_GET['idSite'] = 1;
        $_GET['date'] = '2014-01-01';
        $_GET['period'] = 'day';
    }

    public function tearDown(): void
    {
        unset($_GET['idSite'], $_GET['date'], $_GET['period']);
        parent::tearDown();
    }

    /**
     * @dataProvider provideReportsThatHideFlattenInTheUi
     */
    public function testViewFactoryDisablesFlattenUiAndExportForReportsThatHideFlattenInTheUi(string $apiAction): void
    {
        $view = ViewDataTableFactory::build(
            $defaultType = null,
            $apiAction,
            $controllerAction = $apiAction,
            $forceDefault = false,
            $loadViewDataTableParametersForUser = false
        );

        $report = ReportsProvider::factory(...explode('.', $apiAction));

        $this->assertTrue($report->supportsFlatten(), $apiAction);
        $this->assertFalse($view->config->show_flatten_table, $apiAction);
        $this->assertTrue($view->config->report_supports_flatten, $apiAction);
        $this->assertFalse($view->config->show_flatten_table_export, $apiAction);
    }

    public function testViewFactoryKeepsFlattenExportEnabledForReportsThatSupportFlattening(): void
    {
        $view = ViewDataTableFactory::build(
            $defaultType = null,
            'Actions.getPageUrls',
            $controllerAction = 'Actions.getPageUrls',
            $forceDefault = false,
            $loadViewDataTableParametersForUser = false
        );

        $this->assertTrue($view->config->show_flatten_table);
        $this->assertTrue($view->config->report_supports_flatten);
        $this->assertTrue($view->config->show_flatten_table_export);
    }

    public function testViewFactoryDisablesFlattenUiAndExportForReportsThatDoNotSupportFlattening(): void
    {
        $view = ViewDataTableFactory::build(
            $defaultType = null,
            'Referrers.getReferrerType',
            $controllerAction = 'Referrers.getReferrerType',
            $forceDefault = false,
            $loadViewDataTableParametersForUser = false
        );

        $this->assertFalse($view->config->show_flatten_table);
        $this->assertFalse($view->config->report_supports_flatten);
        $this->assertFalse($view->config->show_flatten_table_export);
    }

    public function provideReportsThatHideFlattenInTheUi(): iterable
    {
        yield ['UserLanguage.getLanguage'];
        yield ['VisitTime.getVisitInformationPerLocalTime'];
        yield ['VisitTime.getVisitInformationPerServerTime'];
        yield ['DevicesDetection.getOsVersions'];
        yield ['DevicesDetection.getBrowsers'];
        yield ['DevicesDetection.getType'];
        yield ['DevicePlugins.getPlugin'];
        yield ['Resolution.getConfiguration'];
        yield ['UserCountry.getCountry'];
        yield ['UserCountry.getContinent'];
        yield ['UserCountry.getCity'];
        yield ['UserCountry.getRegion'];
    }
}
