<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Plugin\Report;
use Piwik\Plugins\ExampleReport\Reports\GetExampleReport;
use Piwik\Metrics;

/**
 * @group Core
 */
class Plugin_ReportTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Report
     */
    private $report;

    public function setUp()
    {
        $this->report = new GetExampleReport();
    }

    public function test_reportShouldUseDefaultMetrics()
    {
        $this->assertEquals(Metrics::getDefaultMetrics(), $this->report->getMetrics());
    }

    public function test_reportShouldUseDefaultProcessedMetrics()
    {
        $this->assertEquals(Metrics::getDefaultProcessedMetrics(), $this->report->getProcessedMetrics());
    }
}