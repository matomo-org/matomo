<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\API\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugins\API\ProcessedReport;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @phpcs:ignoreFile PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class ProcessedReportTest extends IntegrationTestCase
{
    /**
     * @var int
     */
    private $idSite;

    /**
     * @var ProcessedReport
     */
    private $processedReport;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2020-02-03 03:04:05');

        $this->processedReport = StaticContainer::get(ProcessedReport::class);

        $this->addTestReports();
    }

    public function test_getMetadata_matchesCorrectReport_whenExactNoParametersProvided()
    {
        $reports = $this->getTestReportMetadata([]);
        $this->assertNotEmpty($reports);
        $this->assertCount(1, $reports);

        $report = $reports[0];
        $this->assertNotEmpty($report);
        $this->assertEquals('TestPlugin', $report['module']);
        $this->assertEquals('getTestReport', $report['action']);
        $this->assertEmpty($report['parameters'] ?? []);
    }

    public function test_getMetadata_matchesCorrectReport_whenExactApiParametersProvided()
    {
        $reports = $this->getTestReportMetadata(['param0' => 'testvalue', 'param1' => 'testvalue']);
        $this->assertNotEmpty($reports);
        $this->assertCount(1, $reports);

        $report = $reports[0];
        $this->assertNotEmpty($report);
        $this->assertEquals('TestPlugin', $report['module']);
        $this->assertEquals('getTestReport', $report['action']);
        $this->assertEquals(['param0' => 'testvalue', 'param1' => 'testvalue'], $report['parameters']);
    }

    public function test_getMetadata_returnsFirstMatchedReportWithParams_whenUnneededApiParametersProvided()
    {
        $reports = $this->getTestReportMetadata(['param0' => 'testvalue', 'param1' => 'testvalue', 'extraParam' => 'value']);

        $report = $reports[0];
        $this->assertNotEmpty($report);
        $this->assertEquals('TestPlugin', $report['module']);
        $this->assertEquals('getTestReport', $report['action']);
        $this->assertEquals(['param0' => 'testvalue', 'param1' => 'testvalue'], $report['parameters']);
    }

    public function test_getMetadata_doesNotMatch_whenMissingApiParameters()
    {
        $reports = $this->getTestReportMetadata(['param1' => 'testvalue']);
        $this->assertEmpty($reports);
    }

    private function addTestReports()
    {
        Piwik::addAction('Report.addReports', function (&$reports) {
            $reports[] = $this->makeTestReportWithParameters(0);
            $reports[] = $this->makeTestReportWithParameters(1);
            $reports[] = $this->makeTestReportWithParameters(2);
        });

        Fixture::clearInMemoryCaches();

        $reports = StaticContainer::get(ReportsProvider::class)->getAllReports();
        $reports = array_filter($reports, function (Report $r) {
            return $r->getModule() === 'TestPlugin';
        });

        // sanity check
        $addedReports = 3;
        $this->assertCount($addedReports, $reports);
    }

    private function makeTestReportWithParameters(int $paramCount): Report
    {
        $report = new class () extends Report {
            public $paramCount = 0;

            public function init()
            {
                parent::init();

                $this->module = 'TestPlugin';
                $this->action = 'getTestReport';
                $this->order = 1 + $this->paramCount;

                for ($i = 0; $i < $this->paramCount; ++$i) {
                    $this->parameters['param' . $i] = 'testvalue';
                }
            }
        };

        // have to set the property this way since Report::__construct() is final
        $report->paramCount = $paramCount;
        $report->init();
        return $report;
    }

    private function getTestReportMetadata(array $parameters)
    {
        return $this->processedReport->getMetadata(
            $this->idSite,
            'TestPlugin',
            'getTestReport',
            $parameters,
            false,
            false,
            false,
            false,
            false
        );
    }
}
