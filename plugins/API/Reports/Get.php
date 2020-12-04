<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\API\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ReportsProvider;

class Get extends Report
{
    /**
     * List of Plugin.Get reports that are merged in this one.
     *
     * @var Report[]
     */
    private $reportsToMerge = array();

    protected function init()
    {
        parent::init();

        $this->reportsToMerge = $this->getReportsToMerge();

        $this->module = 'API';
        $this->action = 'get';

        $this->categoryId = 'API';
        $this->name = Piwik::translate('General_MainMetrics');
        $this->documentation = Piwik::translate('API_MainMetricsReportDocumentation');

        $this->processedMetrics = array();
        foreach ($this->reportsToMerge as $report) {
            if (!is_array($report->processedMetrics)) {
                continue;
            }

            $this->processedMetrics = array_merge($this->processedMetrics, $report->processedMetrics);
        }

        $this->metrics = array();
        foreach ($this->reportsToMerge as $report) {
            if (!is_array($report->metrics)) {
                continue;
            }

            $this->metrics = array_merge($this->metrics, $report->metrics);
        }

        $this->order = 6;
    }

    public function getMetrics()
    {
        $metrics = array();
        foreach ($this->reportsToMerge as $report) {
            $metrics = array_merge($metrics, $report->getMetrics());
        }
        return $metrics;
    }

    public function getProcessedMetrics()
    {
        $processedMetrics = array();
        foreach ($this->reportsToMerge as $report) {
            $reportMetrics = $report->getProcessedMetrics();
            if (is_array($reportMetrics)) {
                $processedMetrics = array_merge($processedMetrics, $reportMetrics);
            }
        }
        return $processedMetrics;
    }

    /**
     * @return Report[]
     */
    private function getReportsToMerge()
    {
        $reports = new ReportsProvider();
        $result = array();
        foreach ($reports->getAllReportClasses() as $reportClass) {
            if ($reportClass == 'Piwik\\Plugins\\API\\Reports\\Get') {
                continue;
            }

            /** @var Report $report */
            $report = new $reportClass();

            if ($report->getModule() == 'API'
                || $report->getAction() != 'get'
            ) {
                continue;
            }

            $metrics = $report->getMetrics();
            if (!empty($report->parameters)
                || empty($metrics)
            ) {
                continue;
            }

            $result[] = $report;
        }
        return $result;
    }
}