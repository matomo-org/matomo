<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation\Widgets;

use Piwik\Piwik;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\Diagnostics\DiagnosticReport;
use Piwik\Plugins\Diagnostics\DiagnosticService;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\View;

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
        $config->setCategoryId('About Piwik');
        $config->setName('Installation_SystemCheck');
        $config->setOrder(16);

        $config->setIsEnabled(Piwik::hasUserSuperUserAccess());
    }

    public function render()
    {
        $view = new View('@Installation/getSystemCheckWidget');

        $report = $this->diagnosticService->runDiagnostics();
        $view->numErrors = $report->getErrorCount();
        $view->numWarnings = $report->getWarningCount();

        $view->errors = array();
        $view->warnings = array();

        if ($report->hasErrors()) {
            $view->errors = $this->getResults($report, DiagnosticResult::STATUS_ERROR);
        }

        if ($report->hasWarnings()) {
            $view->warnings = $this->getResults($report, DiagnosticResult::STATUS_WARNING);
        }

        return $view->render();
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