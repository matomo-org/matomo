<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation\Widgets;

use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\DiagnosticReport;
use Piwik\Plugins\Diagnostics\DiagnosticService;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class GetSystemCheck extends Widget
{
    /**
     * @var DiagnosticService
     */
    private $diagnosticService;

    public function __construct(DiagnosticService $diagnosticService)
    {
        $this->diagnosticService = $diagnosticService;
    }

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('Installation_SystemCheck');
        $config->setOrder(16);

        $config->setIsEnabled(Piwik::hasUserSuperUserAccess() 
            && Manager::getInstance()->isPluginActivated('Diagnostics')
        );
    }

    public function render()
    {
        $report = $this->diagnosticService->runDiagnostics();
        $numErrors = $report->getErrorCount();
        $numWarnings = $report->getWarningCount();

        $errors = array();
        $warnings = array();

        if ($report->hasErrors()) {
            $errors = $this->getResults($report, DiagnosticResult::STATUS_ERROR);
        }

        if ($report->hasWarnings()) {
            $warnings = $this->getResults($report, DiagnosticResult::STATUS_WARNING);
        }

        return $this->renderTemplate('getSystemCheckWidget', array(
            'numErrors' => $numErrors,
            'numWarnings' => $numWarnings,
            'errors' => $errors,
            'warnings' => $warnings,

        ));
    }

    private function getResults(DiagnosticReport $report, $type)
    {
        $results = $report->getAllResults();

        $reports = array();
        foreach ($results as $result) {
            if ($result->getStatus() === $type) {
                $reports[] = $result;
            }
        }

        return $reports;
    }

}