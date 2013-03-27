<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * This class generates the HTML code to embed graphs in the page.
 * It doesn't call the API but simply prints the html snippet.
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
abstract class Piwik_ViewDataTable_GenerateGraphHTML extends Piwik_ViewDataTable
{

    protected $width = '100%';
    protected $height = 250;
    protected $graphType = 'unknown';

    /**
     * Parameters to send to GenerateGraphData instance. Parameters are passed
     * via the $_GET array.
     *
     * @var array
     */
    protected $generateGraphDataParams = array();

    /**
     * @see Piwik_ViewDataTable::init()
     * @param string $currentControllerName
     * @param string $currentControllerAction
     * @param string $apiMethodToRequestDataTable
     * @param null $controllerActionCalledWhenRequestSubTable
     */
    function init($currentControllerName,
                  $currentControllerAction,
                  $apiMethodToRequestDataTable,
                  $controllerActionCalledWhenRequestSubTable = null)
    {
        parent::init($currentControllerName,
            $currentControllerAction,
            $apiMethodToRequestDataTable,
            $controllerActionCalledWhenRequestSubTable);

        $this->dataTableTemplate = 'CoreHome/templates/graph.tpl';

        $this->disableOffsetInformationAndPaginationControls();
        $this->disableExcludeLowPopulation();
        $this->disableSearchBox();
        $this->enableShowExportAsImageIcon();

        $this->parametersToModify = array(
            'viewDataTable' => $this->getViewDataTableIdToLoad(),
            // in the case this controller is being executed by another controller
            // eg. when being widgetized in an IFRAME
            // we need to put in the URL of the graph data the real module and action
            'module'        => $currentControllerName,
            'action'        => $currentControllerAction,
        );
    }

    public function enableShowExportAsImageIcon()
    {
        $this->viewProperties['show_export_as_image_icon'] = true;
    }

    public function addRowEvolutionSeriesToggle($initiallyShowAllMetrics)
    {
        $this->viewProperties['externalSeriesToggle'] = 'RowEvolutionSeriesToggle';
        $this->viewProperties['externalSeriesToggleShowAll'] = $initiallyShowAllMetrics;
    }

    /**
     * Sets parameters to modify in the future generated URL
     * @param array $array array('nameParameter' => $newValue, ...)
     */
    public function setParametersToModify($array)
    {
        $this->parametersToModify = array_merge($this->parametersToModify, $array);
    }

    /**
     * Show every x-axis tick instead of just every other one.
     */
    public function showAllTicks()
    {
        $this->generateGraphDataParams['show_all_ticks'] = 1;
    }

    /**
     * Adds a row to the report containing totals for contained metrics. Mainly useful
     * for evolution graphs where displaying the totals w/ the metrics is useful.
     */
    public function addTotalRow()
    {
        $this->generateGraphDataParams['add_total_row'] = 1;
    }

    /**
     * We persist the parametersToModify values in the javascript footer.
     * This is used by the "export links" that use the "date" attribute
     * from the json properties array in the datatable footer.
     * @return array
     */
    protected function getJavascriptVariablesToSet()
    {
        $original = parent::getJavascriptVariablesToSet();
        $originalViewDataTable = $original['viewDataTable'];

        $result = $this->parametersToModify + $original;
        ;
        $result['viewDataTable'] = $originalViewDataTable;

        return $result;
    }

    /**
     * @see Piwik_ViewDataTable::main()
     * @return null
     */
    public function main()
    {
        if ($this->mainAlreadyExecuted) {
            return;
        }
        $this->mainAlreadyExecuted = true;

        $this->view = $this->buildView();
    }

    protected function buildView()
    {
        // access control
        $idSite = Piwik_Common::getRequestVar('idSite', 1, 'int');
        Piwik_API_Request::reloadAuthUsingTokenAuth();
        if (!Piwik::isUserHasViewAccess($idSite)) {
            throw new Exception(Piwik_TranslateException('General_ExceptionPrivilegeAccessWebsite', array("'view'", $idSite)));
        }

        // collect data
        $this->parametersToModify['action'] = $this->currentControllerAction;
        $this->parametersToModify = array_merge($this->variablesDefault, $this->parametersToModify);
        $this->graphData = $this->getGraphData();

        // build view
        $view = new Piwik_View($this->dataTableTemplate);

        $view->width = $this->width;
        $view->height = $this->height;
        $view->chartDivId = $this->getUniqueIdViewDataTable() . "Chart";
        $view->graphType = $this->graphType;

        $view->data = $this->graphData;
        $view->isDataAvailable = strpos($this->graphData, '"series":[]') === false;

        $view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
        $view->properties = $this->getViewProperties();

        $view->reportDocumentation = $this->getReportDocumentation();

        // if it's likely that the report data for this data table has been purged,
        // set whether we should display a message to that effect.
        $view->showReportDataWasPurgedMessage = $this->hasReportBeenPurged();
        $view->deleteReportsOlderThan = Piwik_GetOption('delete_reports_older_than');

        return $view;
    }

    protected function getGraphData()
    {
        $saveGet = $_GET;

        $params = array_merge($this->generateGraphDataParams, $this->parametersToModify);
        foreach ($params as $key => $val) {
            // We do not forward filter data to the graph controller.
            // This would cause the graph to have filter_limit=5 set by default,
            // which would break them (graphs need the full dataset to build the "Others" aggregate value)
            if (strpos($key, 'filter_') !== false) {
                continue;
            }
            if (is_array($val)) {
                $val = implode(',', $val);
            }
            $_GET[$key] = $val;
        }
        $content = Piwik_FrontController::getInstance()->fetchDispatch($this->currentControllerName, $this->currentControllerAction, array());

        $_GET = $saveGet;

        return str_replace(array("\r", "\n"), '', $content);
    }
}
