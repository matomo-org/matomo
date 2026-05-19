<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\ViewDataTable;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\ViewDataTable\Factory as ViewDataTableFactory;

/**
 * @group Core
 * @group ViewDataTable
 */
class ConfigTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReportsThatDoNotSupportFlattening
     */
    public function testViewFactoryDisablesFlattenUiAndExportForReportsThatDoNotSupportIt(string $apiAction): void
    {
        $view = ViewDataTableFactory::build(
            $defaultType = null,
            $apiAction,
            $controllerAction = $apiAction,
            $forceDefault = false,
            $loadViewDataTableParametersForUser = false
        );

        $this->assertFalse($view->config->report_supports_flatten, $apiAction);
        $this->assertFalse($view->config->show_flatten_table, $apiAction);
    }

    public function provideReportsThatDoNotSupportFlattening(): iterable
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
