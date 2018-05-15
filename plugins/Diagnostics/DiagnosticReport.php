<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics;

use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;

/**
 * A diagnostic report contains all the results of all the diagnostics.
 */
class DiagnosticReport
{
    /**
     * @var DiagnosticResult[]
     */
    private $mandatoryDiagnosticResults;

    /**
     * @var DiagnosticResult[]
     */
    private $optionalDiagnosticResults;

    /**
     * @var int
     */
    private $errorCount = 0;

    /**
     * @var int
     */
    private $warningCount = 0;

    /**
     * @param DiagnosticResult[] $mandatoryDiagnosticResults
     * @param DiagnosticResult[] $optionalDiagnosticResults
     */
    public function __construct(array $mandatoryDiagnosticResults, array $optionalDiagnosticResults)
    {
        $this->mandatoryDiagnosticResults = $mandatoryDiagnosticResults;
        $this->optionalDiagnosticResults = $optionalDiagnosticResults;

        $this->computeErrorAndWarningCount();
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->getErrorCount() > 0;
    }

    /**
     * @return bool
     */
    public function hasWarnings()
    {
        return $this->getWarningCount() > 0;
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * @return int
     */
    public function getWarningCount()
    {
        return $this->warningCount;
    }

    /**
     * @return DiagnosticResult[]
     */
    public function getAllResults()
    {
        return array_merge($this->mandatoryDiagnosticResults, $this->optionalDiagnosticResults);
    }

    /**
     * @return DiagnosticResult[]
     */
    public function getMandatoryDiagnosticResults()
    {
        return $this->mandatoryDiagnosticResults;
    }

    /**
     * @return DiagnosticResult[]
     */
    public function getOptionalDiagnosticResults()
    {
        return $this->optionalDiagnosticResults;
    }

    private function computeErrorAndWarningCount()
    {
        foreach ($this->getAllResults() as $result) {
            switch ($result->getStatus()) {
                case DiagnosticResult::STATUS_ERROR:
                    $this->errorCount++;
                    break;
                case DiagnosticResult::STATUS_WARNING:
                    $this->warningCount++;
                    break;
            }
        }
    }
}
