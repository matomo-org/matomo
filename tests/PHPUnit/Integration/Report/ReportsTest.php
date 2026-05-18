<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Report;

use Piwik\Plugin\ReportsProvider;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\DevicePlugins\Reports\GetPlugin;
use Piwik\Plugins\DevicesDetection\Reports\GetBrowsers;
use Piwik\Plugins\DevicesDetection\Reports\GetOsVersions;
use Piwik\Plugins\DevicesDetection\Reports\GetType;
use Piwik\Plugins\Resolution\Reports\GetConfiguration;
use Piwik\Plugins\UserCountry\Reports\GetCity;
use Piwik\Plugins\UserCountry\Reports\GetContinent;
use Piwik\Plugins\UserCountry\Reports\GetCountry;
use Piwik\Plugins\UserCountry\Reports\GetRegion;
use Piwik\Plugins\UserLanguage\Reports\GetLanguage;
use Piwik\Plugins\VisitTime\Reports\GetVisitInformationPerLocalTime;
use Piwik\Plugins\VisitTime\Reports\GetVisitInformationPerServerTime;

/**
 * @group Core
 */
class ReportTest extends \PHPUnit\Framework\TestCase
{
    public function testGetAllReportsShouldNotFindAReportIfNoPluginLoaded()
    {
        $this->unloadAllPlugins();

        $reports = new ReportsProvider();
        $report = $reports->getAllReports();

        $this->assertEquals(array(), $report);
    }

    public function testGetAllReportsShouldFindAllAvailableReports()
    {
        $this->loadExampleReportPlugin();
        $this->loadMorePlugins();

        $reports = new ReportsProvider();
        $reports = $reports->getAllReports();

        $this->assertGreaterThan(20, count($reports));

        foreach ($reports as $report) {
            $this->assertInstanceOf('Piwik\Plugin\Report', $report);
        }
    }

    public function testDeviceTypeReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetType())->supportsFlatten());
    }

    public function testContinentReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetContinent())->supportsFlatten());
    }

    public function testCountryReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetCountry())->supportsFlatten());
    }

    public function testRegionReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetRegion())->supportsFlatten());
    }

    public function testCityReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetCity())->supportsFlatten());
    }

    public function testLanguageReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetLanguage())->supportsFlatten());
    }

    public function testOperatingSystemVersionsReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetOsVersions())->supportsFlatten());
    }

    public function testConfigurationReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetConfiguration())->supportsFlatten());
    }

    public function testBrowserPluginsReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetPlugin())->supportsFlatten());
    }

    public function testBrowsersReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetBrowsers())->supportsFlatten());
    }

    public function testVisitsPerLocalTimeReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetVisitInformationPerLocalTime())->supportsFlatten());
    }

    public function testVisitsPerSiteTimezoneHourReportShouldNotSupportFlatten()
    {
        $this->assertFalse((new GetVisitInformationPerServerTime())->supportsFlatten());
    }

    private function loadExampleReportPlugin()
    {
        PluginManager::getInstance()->loadPlugins(array('ExampleReport'));
    }

    private function loadMorePlugins()
    {
        PluginManager::getInstance()->loadPlugins(array('Actions', 'DevicesDetection', 'CoreVisualizations', 'API', 'Morpheus'));
    }

    private function unloadAllPlugins()
    {
        PluginManager::getInstance()->unloadPlugins();
    }
}
