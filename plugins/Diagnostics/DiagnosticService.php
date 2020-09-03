<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics;

use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;

/**
 * Runs the Piwik diagnostics.
 *
 * @api
 */
class DiagnosticService
{
    /**
     * @var Diagnostic[]
     */
    private $mandatoryDiagnostics;

    /**
     * @var Diagnostic[]
     */
    private $optionalDiagnostics;

    /**
     * @var Diagnostic[]
     */
    private $informationDiagnostics;

    /**
     * @param Diagnostic[] $mandatoryDiagnostics
     * @param Diagnostic[] $optionalDiagnostics
     * @param Diagnostic[] $disabledDiagnostics
     */
    public function __construct(array $mandatoryDiagnostics, array $optionalDiagnostics, array $informationDiagnostics, array $disabledDiagnostics)
    {
        $this->mandatoryDiagnostics = $this->removeDisabledDiagnostics($mandatoryDiagnostics, $disabledDiagnostics);
        $this->optionalDiagnostics = $this->removeDisabledDiagnostics($optionalDiagnostics, $disabledDiagnostics);
        $this->informationDiagnostics = $this->removeDisabledDiagnostics($informationDiagnostics, $disabledDiagnostics);
    }

    /**
     * @return DiagnosticReport
     */
    public function runDiagnostics()
    {
        return new DiagnosticReport(
            $this->run($this->mandatoryDiagnostics),
            $this->run($this->optionalDiagnostics),
            $this->run($this->informationDiagnostics)
        );
    }

    /**
     * @param Diagnostic[] $diagnostics
     * @return DiagnosticResult[]
     */
    private function run(array $diagnostics)
    {
        $results = array();

        foreach ($diagnostics as $diagnostic) {
            $results = array_merge($results, $diagnostic->execute());
        }

        return $results;
    }

    private function removeDisabledDiagnostics(array $diagnostics, array $disabledDiagnostics)
    {
        return array_filter($diagnostics, function (Diagnostic $diagnostic) use ($disabledDiagnostics) {
            return ! in_array($diagnostic, $disabledDiagnostics, true);
        });
    }
}
