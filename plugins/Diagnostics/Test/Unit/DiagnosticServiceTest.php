<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Test\Unit;

use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\DiagnosticService;
use Piwik\Plugins\Diagnostics\Test\Mock\DiagnosticWithError;
use Piwik\Plugins\Diagnostics\Test\Mock\DiagnosticWithWarning;

class DiagnosticServiceTest extends \PHPUnit_Framework_TestCase
{
    public function test_runDiagnostics()
    {
        $mandatoryDiagnostics = array(
            new DiagnosticWithError(),
        );
        $optionalDiagnostics = array(
            new DiagnosticWithWarning(),
        );

        $service = new DiagnosticService($mandatoryDiagnostics, $optionalDiagnostics);

        $report = $service->runDiagnostics();

        $results = $report->getAllResults();

        $this->assertCount(2, $results);
        $this->assertEquals('Error', $results[0]->getLabel());
        $this->assertEquals(DiagnosticResult::STATUS_ERROR, $results[0]->getStatus());
        $this->assertEquals('Warning', $results[1]->getLabel());
        $this->assertEquals(DiagnosticResult::STATUS_WARNING, $results[1]->getStatus());
    }
}
