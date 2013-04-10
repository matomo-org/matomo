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
 * Outputs an AJAX Table for a given DataTable.
 *
 * Reads the requested DataTable from the API.
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
class Piwik_ViewDataTable_HtmlTable extends Piwik_ViewDataTable
{
    /**
     * Set to true when the DataTable must be loaded along with all its children subtables
     * Useful when searching for a pattern in the DataTable Actions (we display the full hierarchy)
     *
     * @var bool
     */
    protected $recursiveDataTableLoad = false;

    /**
     * PHP array conversion of the Piwik_DataTable
     *
     * @var array
     */
    public $arrayDataTable; // phpArray

    /**
     * @see Piwik_ViewDataTable::init()
     * @param string $currentControllerName
     * @param string $currentControllerAction
     * @param string $apiMethodToRequestDataTable
     * @param null|string $controllerActionCalledWhenRequestSubTable
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
        $this->dataTableTemplate = 'CoreHome/templates/datatable.tpl';
        $this->variablesDefault['enable_sort'] = '1';
        $this->setSortedColumn('nb_visits', 'desc');
        $this->setLimit(Piwik_Config::getInstance()->General['datatable_default_limit']);
        $this->handleLowPopulation();
    }

    protected function getViewDataTableId()
    {
        return 'table';
    }

    /**
     * @see Piwik_ViewDataTable::main()
     * @throws Exception|Piwik_Access_NoAccessException
     * @return null
     */
    public function main()
    {
        if ($this->mainAlreadyExecuted) {
            return;
        }
        $this->mainAlreadyExecuted = true;

        $this->isDataAvailable = true;
        try {
            $this->loadDataTableFromAPI();
        } catch (Piwik_Access_NoAccessException $e) {
            throw $e;
        } catch (Exception $e) {
            Piwik::log("Failed to get data from API: " . $e->getMessage());

            $this->isDataAvailable = false;
        }

        $this->postDataTableLoadedFromAPI();
        $this->view = $this->buildView();
    }

    /**
     * @return Piwik_View with all data set
     */
    protected function buildView()
    {
        $view = new Piwik_View($this->dataTableTemplate);

        if (!$this->isDataAvailable) {
            $view->arrayDataTable = array();
        } else {
            $columns = $this->getColumnsToDisplay();
            $columnTranslations = $columnDocumentation = array();
            foreach ($columns as $columnName) {
                $columnTranslations[$columnName] = $this->getColumnTranslation($columnName);
                $columnDocumentation[$columnName] = $this->getMetricDocumentation($columnName);
            }
            $nbColumns = count($columns);
            // case no data in the array we use the number of columns set to be displayed
            if ($nbColumns == 0) {
                $nbColumns = count($this->columnsToDisplay);
            }

            $view->arrayDataTable = $this->getPHPArrayFromDataTable();
            $view->dataTableColumns = $columns;
            $view->reportDocumentation = $this->getReportDocumentation();
            $view->columnTranslations = $columnTranslations;
            $view->columnDocumentation = $columnDocumentation;
            $view->nbColumns = $nbColumns;
            $view->defaultWhenColumnValueNotDefined = '-';

            // if it's likely that the report data for this data table has been purged,
            // set whether we should display a message to that effect.
            $view->showReportDataWasPurgedMessage = $this->hasReportBeenPurged();
            $view->deleteReportsOlderThan = Piwik_GetOption('delete_reports_older_than');
        }
        $view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
        $view->properties = $this->getViewProperties();
        return $view;
    }

    protected function handleLowPopulation($columnToApplyFilter = null)
    {
        if (Piwik_Common::getRequestVar('enable_filter_excludelowpop', '0', 'string') == '0') {
            return;
        }
        if (is_null($columnToApplyFilter)) {
            $columnToApplyFilter = 'nb_visits';
        }
        $this->setExcludeLowPopulation($columnToApplyFilter);
    }

    /**
     * Returns friendly php array from the Piwik_DataTable
     * @see Piwik_DataTable_Renderer_Php
     * @return array
     */
    protected function getPHPArrayFromDataTable()
    {
        $renderer = Piwik_DataTable_Renderer::factory('php');
        $renderer->setTable($this->dataTable);
        $renderer->setSerialize(false);
        // we get the php array from the datatable but conserving the original datatable format,
        // ie. rows 'columns', 'metadata' and 'idsubdatatable'
        $phpArray = $renderer->originalRender();
        return $phpArray;
    }


    /**
     * Adds a column to the list of columns to be displayed
     *
     * @param string $columnName
     */
    public function addColumnToDisplay($columnName)
    {
        $this->columnsToDisplay[] = $columnName;
    }

    /**
     * Sets the search on a table to be recursive (also searches in subtables)
     * Works only on Actions/Downloads/Outlinks tables.
     *
     * @return bool If the pattern for a recursive search was set in the URL
     */
    public function setSearchRecursive()
    {
        $this->variablesDefault['search_recursive'] = true;
        return $this->setRecursiveLoadDataTableIfSearchingForPattern();
    }

    protected function getRequestString()
    {
        $requestString = parent::getRequestString();
        if ($this->recursiveDataTableLoad
            && !Piwik_Common::getRequestVar('flat', false)
        ) {
            $requestString .= '&expanded=1';
        }
        return $requestString;
    }

    /**
     * Set the flag to load the datatable recursively so we can search on subtables as well
     *
     * @return bool if recursive search is enabled
     */
    protected function setRecursiveLoadDataTableIfSearchingForPattern()
    {
        try {
            $requestValue = Piwik_Common::getRequestVar('filter_column_recursive');
            $requestValue = Piwik_Common::getRequestVar('filter_pattern_recursive');
            // if the 2 variables are set we are searching for something.
            // we have to load all the children subtables in this case

            $this->recursiveDataTableLoad = true;
            return true;
        } catch (Exception $e) {
            $this->recursiveDataTableLoad = false;
            return false;
        }
    }

    /**
     * Disable the row evolution feature which is enabled by default
     */
    public function disableRowEvolution()
    {
        $this->variablesDefault['disable_row_evolution'] = true;
    }

    /**
     * Disables row actions.
     */
    public function disableRowActions()
    {
        $this->variablesDefault['disable_row_actions'] = true;
    }

}
