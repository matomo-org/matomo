<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 * MULTI ROW EVOLUTION
 * The class handles the popover that shows the evolution of a multiple rows in a data table
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome_DataTableRowAction_MultiRowEvolution
    extends Piwik_CoreHome_DataTableRowAction_RowEvolution
{
    const IS_MULTI_EVOLUTION_PARAM = 'is_multi_evolution';

    /** The requested metric */
    protected $metric;

    /** Show all metrics in the evolution graph when the popover opens */
    protected $initiallyShowAllMetrics = true;

    /** The metrics available in the metrics select */
    protected $metricsForSelect;

    /**
     * The constructor
     * @param int
     * @param Piwik_Date ($this->date from controller)
     */
    public function __construct($idSite, $date)
    {
        $this->metric = Piwik_Common::getRequestVar('column', '', 'string');
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
     * @param Piwik_CoreHome_Controller
     * @param Piwik_View (the popover_rowevolution template)
     */
    public function renderPopover($controller, $view)
    {
        // add data for metric select box
        $view->availableMetrics = $this->metricsForSelect;
        $view->selectedMetric = $this->metric;

        $view->availableRecordsText = $this->dimension . ': '
            . Piwik_Translate('RowEvolution_ComparingRecords', array(count($this->availableMetrics)));

        return parent::renderPopover($controller, $view);
    }

    /**
     * Generic method to get an evolution graph or a sparkline for the row evolution popover.
     * Do as much as possible from outside the controller.
     *
     * @return Piwik_ViewDataTable
     */
    public function getRowEvolutionGraph()
    {
        $view = parent::getRowEvolutionGraph();
        $view->setCustomParameter(self::IS_MULTI_EVOLUTION_PARAM, 1); // set in JS
        return $view;
    }
}
