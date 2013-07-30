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
namespace Piwik\ViewDataTable;

use Exception;
use Piwik\Config;
use Piwik\DataTable\Renderer;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\ViewDataTable;
use Piwik\View;
use Piwik\Visualization;

/**
 * Outputs an AJAX Table for a given DataTable.
 *
 * Reads the requested DataTable from the API.
 *
 * @package Piwik
 * @subpackage ViewDataTable
 */
class HtmlTable extends ViewDataTable
{
    /**
     * PHP array conversion of the DataTable
     *
     * @var array
     */
    public $arrayDataTable; // phpArray

    public function __construct()
    {
        parent::__construct();

        $this->viewProperties['enable_sort'] = '1';
        $this->viewProperties['disable_row_evolution'] = false;
        $this->viewProperties['disable_row_actions'] = false;

        $this->setLimit(Config::getInstance()->General['datatable_default_limit']);
        $this->handleLowPopulation();
        $this->setSubtableTemplate("@CoreHome/_dataTable.twig");
        $this->viewProperties['datatable_js_type'] = 'dataTable';
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
     * @see ViewDataTable::main()
     * @throws Exception|\Piwik\NoAccessException
     * @return null
     */
    public function main()
    {
        if ($this->mainAlreadyExecuted) {
            return;
        }
        $this->mainAlreadyExecuted = true;

        try {
            $this->loadDataTableFromAPI();
        } catch (\Piwik\NoAccessException $e) {
            throw $e;
        } catch (Exception $e) {
            Piwik::log("Failed to get data from API: " . $e->getMessage());

            $this->loadingError = array('message' => $e->getMessage());
        }

        $this->postDataTableLoadedFromAPI();

        $template = $this->idSubtable ? $this->viewProperties['subtable_template'] : $this->viewProperties['datatable_template'];
        $this->view = $this->buildView(new Visualization\HtmlTable(), $template);
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

    protected function handleLowPopulation($columnToApplyFilter = null)
    {
        if (Common::getRequestVar('enable_filter_excludelowpop', '0', 'string') == '0') {
            return;
        }
        if (is_null($columnToApplyFilter)) {
            $columnToApplyFilter = 'nb_visits';
        }
        $this->setExcludeLowPopulation($columnToApplyFilter);
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
