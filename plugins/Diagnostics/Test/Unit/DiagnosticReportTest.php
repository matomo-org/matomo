<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Test\Unit;

use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\DiagnosticReport;

class DiagnosticReportTest extends \PHPUnit_Framework_TestCase
{
    public function test_shouldComputeErrorAndWarningCount()
    {
        $report = new DiagnosticReport(
            array(DiagnosticResult::singleResult('Error', DiagnosticResult::STATUS_ERROR, 'Comment')),
            array(DiagnosticResult::singleResult('Warning', DiagnosticResult::STATUS_WARNING, 'Comment'))
        );

        $this->assertEquals(1, $report->getErrorCount());
        $this->assertTrue($report->hasErrors());
        $this->assertEquals(1, $report->getWarningCount());
        $this->assertTrue($report->hasWarnings());

        $report = new DiagnosticReport(array(), array());

        $this->assertEquals(0, $report->getErrorCount());
        $this->assertFalse($report->hasErrors());
        $this->assertEquals(0, $report->getWarningCount());
        $this->assertFalse($report->hasWarnings());
    }

    public function test_getAllResults()
    {
        $report = new DiagnosticReport(
            array(DiagnosticResult::singleResult('Error', DiagnosticResult::STATUS_ERROR, 'Comment')),
            array(DiagnosticResult::singleResult('Warning', DiagnosticResult::STATUS_WARNING, 'Comment'))
        );

        $this->assertCount(1, $report->getMandatoryDiagnosticResults());
        $this->assertCount(1, $report->getOptionalDiagnosticResults());
        $this->assertCount(2, $report->getAllResults());
    }
}
