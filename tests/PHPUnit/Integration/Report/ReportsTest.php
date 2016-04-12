<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Report;

use Piwik\API\Proxy;
use Piwik\Plugin\Report;
use Piwik\Plugins\ExampleReport\Reports\GetExampleReport;
use Piwik\Plugins\Actions\Columns\ExitPageUrl;
use Piwik\Piwik;
use Piwik\Metrics;
use Piwik\Plugins\ExampleTracker\Columns\ExampleDimension;
use Piwik\Plugins\Referrers\Columns\Keyword;
use Piwik\Plugin\ReportsProvider;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Translate;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Widget\WidgetsList;


/**
 * @group Core
 */
class ReportTest extends IntegrationTestCase
{
    public function test_getAllReports_shouldNotFindAReport_IfNoPluginLoaded()
    {
        $this->unloadAllPlugins();

        $reports = new ReportsProvider();
        $report = $reports->getAllReports();

        $this->assertEquals(array(), $report);
    }

    public function test_getAllReports_ShouldFindAllAvailableReports()
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
