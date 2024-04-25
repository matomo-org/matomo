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
