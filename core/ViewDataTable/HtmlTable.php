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
     * PHP array conversion of the Piwik_DataTable
     *
     * @var array
     */
    public $arrayDataTable; // phpArray
    
    public function __construct()
    {
        parent::__construct();

        $this->dataTableTemplate = '@CoreHome/_dataTable';
        $this->viewProperties['enable_sort'] = '1';
        $this->viewProperties['disable_row_evolution'] = false;
        $this->viewProperties['disable_row_actions'] = false;
        
        $this->setSortedColumn('nb_visits', 'desc');
        $this->setLimit(Piwik_Config::getInstance()->General['datatable_default_limit']);
        $this->handleLowPopulation();
        $this->setSubtableTemplate("@CoreHome/_dataTable.twig");
        $this->viewProperties['datatable_js_type'] = 'dataTable';
        $this->viewProperties['datatable_css_class'] = $this->getDefaultDataTableCssClass();
    }
    
    public function getJavaScriptProperties()
    {
        $result = parent::getJavaScriptProperties();
        $result[] = 'search_recursive';
        return $result;
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
            $this->loadingError = array('message' => $e->getMessage());
        }

        $this->postDataTableLoadedFromAPI();
        $this->view = $this->buildView();
    }
    
    public function getDefaultDataTableCssClass()
    {
        return 'dataTableNormal';
    }
    
    public function setDataTableCssClass($type)
    {
        $this->viewProperties['datatable_css_class'] = $type;
    }
    
    public function setJsType($type)
    {
        $this->viewProperties['datatable_js_type'] = $type;
    }
    
    public function setSubtableTemplate($subtableTemplate)
    {
        $this->viewProperties['subtable_template'] = $subtableTemplate;
    }
    
    public function showExpanded()
    {
        $this->viewProperties['show_expanded'] = true;
    }

    /**
     * @return Piwik_View with all data set
     */
    protected function buildView()
    {
        $template = $this->idSubtable ? $this->viewProperties['subtable_template'] : $this->dataTableTemplate;
        $view = new Piwik_View($template);

        if (!empty($this->loadingError)) {
            $view->error = $this->loadingError;
        }

        if (!$this->isDataAvailable) {
            $view->dataTable = null;
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
                $nbColumns = count($this->viewProperties['columns_to_display']);
            }

            $view->dataTable = $this->dataTable;
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
        $this->viewProperties['columns_to_display'][] = $columnName;
    }

    /**
     * Sets the search on a table to be recursive (also searches in subtables)
     * Works only on Actions/Downloads/Outlinks tables.
     */
    public function setSearchRecursive()
    {
        $this->viewProperties['search_recursive'] = true;
    }

    protected function getRequestArray()
    {
        $requestArray = parent::getRequestArray();
        if (parent::shouldLoadExpanded()) {
            $requestArray['expanded'] = 1;
        }
        return $requestArray;
    }

    /**
     * Disable the row evolution feature which is enabled by default
     */
    public function disableRowEvolution()
    {
        $this->viewProperties['disable_row_evolution'] = true;
    }

    /**
     * Disables row actions.
     */
    public function disableRowActions()
    {
        $this->viewProperties['disable_row_actions'] = true;
    }

}
