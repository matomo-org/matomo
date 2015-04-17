<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Test\Unit\Diagnostic;

use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResultItem;

class DiagnosticResultTest extends \PHPUnit_Framework_TestCase
{
    public function test_getStatus_shouldReturnTheWorstStatus()
    {
        $result = new DiagnosticResult('Label');

        $this->assertEquals(DiagnosticResult::STATUS_OK, $result->getStatus());

        $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_WARNING));
        $this->assertEquals(DiagnosticResult::STATUS_WARNING, $result->getStatus());

        $result->addItem(new DiagnosticResultItem(DiagnosticResult::STATUS_ERROR));
        $this->assertEquals(DiagnosticResult::STATUS_ERROR, $result->getStatus());
    }

    public function test_singleResult_shouldReturnAResultWithASingleItem()
    {
        $result = DiagnosticResult::singleResult('Label', DiagnosticResult::STATUS_ERROR);

        $this->assertInstanceOf('Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult', $result);
        $this->assertEquals('Label', $result->getLabel());
        $this->assertEquals(DiagnosticResult::STATUS_ERROR, $result->getStatus());
    }
}
