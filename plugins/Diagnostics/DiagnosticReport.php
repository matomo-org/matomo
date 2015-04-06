<?php

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
     * @var int|null
     */
    private $errorCount;

    /**
     * @var int|null
     */
    private $warningCount;

    /**
     * @param DiagnosticResult[] $mandatoryDiagnosticResults
     * @param DiagnosticResult[] $optionalDiagnosticResults
     */
    public function __construct(array $mandatoryDiagnosticResults, array $optionalDiagnosticResults)
    {
        $this->mandatoryDiagnosticResults = $mandatoryDiagnosticResults;
        $this->optionalDiagnosticResults = $optionalDiagnosticResults;
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
        if ($this->errorCount === null) {
            $this->errorCount = 0;
            foreach ($this->getAllResults() as $result) {
                if ($result->getStatus() === DiagnosticResult::STATUS_ERROR) {
                    $this->errorCount++;
                }
            }
        }

        return $this->errorCount;
    }

    /**
     * @return int
     */
    public function getWarningCount()
    {
        if ($this->warningCount === null) {
            $this->warningCount = 0;
            foreach ($this->getAllResults() as $result) {
                if ($result->getStatus() === DiagnosticResult::STATUS_WARNING) {
                    $this->warningCount++;
                }
            }
        }

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
}
