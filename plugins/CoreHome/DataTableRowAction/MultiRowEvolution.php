<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\DataTableRowAction;

use Piwik\Common;
use Piwik\Context;
use Piwik\Piwik;

/**
 * MULTI ROW EVOLUTION
 * The class handles the popover that shows the evolution of a multiple rows in a data table
 */
class MultiRowEvolution extends RowEvolution
{
    /** The requested metric */
    protected $metric;

    /** Show all metrics in the evolution graph when the popover opens */
    protected $initiallyShowAllMetrics = true;

    /** The metrics available in the metrics select */
    protected $metricsForSelect;

    /**
     * The constructor
     * @param int $idSite
     * @param \Piwik\Date $date ($this->date from controller)
     */
    public function __construct($idSite, $date)
    {
        $this->metric = Common::getRequestVar('column', '', 'string');
        parent::__construct($idSite, $date);
    }

    protected function loadEvolutionReport($column = false)
    {
        // set the "column" parameter for the API.getRowEvolution call
        parent::loadEvolutionReport($this->metric);
    }

    protected function extractEvolutionReport($report)
    {
        $this->metric = $report['column'];
        $this->dataTable = $report['reportData'];
        $this->availableMetrics = $report['metadata']['metrics'];
        $this->metricsForSelect = $report['metadata']['columns'];
        $this->dimension = $report['metadata']['dimension'];
    }

    /**
     * Render the popover
     * @param \Piwik\Plugins\CoreHome\Controller $controller
     * @param \Piwik\View (the popover_rowevolution template)
     */
    public function renderPopover($controller, $view)
    {
        // add data for metric select box
        $view->availableMetrics = $this->metricsForSelect;
        $view->selectedMetric = $this->metric;

        $view->availableRecordsText = $this->dimension . ': '
            . Piwik::translate('RowEvolution_ComparingRecords', array(count($this->availableMetrics)));

        return parent::renderPopover($controller, $view);
    }

    protected function getRowEvolutionGraphFromController(\Piwik\Plugins\CoreHome\Controller $controller)
    {
        // the row evolution graphs should not compare serieses
        return Context::executeWithQueryParameters(['compareSegments' => [], 'comparePeriods' => [], 'compareDates' => []], function () use ($controller) {
            return parent::getRowEvolutionGraphFromController($controller);
        });
    }

    public function getRowEvolutionGraph($graphType = false, $metrics = false)
    {
        // the row evolution graphs should not compare serieses
        return Context::executeWithQueryParameters(['compareSegments' => [], 'comparePeriods' => [], 'compareDates' => []], function () use ($graphType, $metrics) {
            return parent::getRowEvolutionGraph($graphType, $metrics);
        });
    }

    protected function getSparkline($metric)
    {
        // the row evolution graphs should not compare serieses
        return Context::executeWithQueryParameters(['compareSegments' => [], 'comparePeriods' => [], 'compareDates' => []], function () use ($metric) {
            return parent::getSparkline($metric);
        });
    }
}
