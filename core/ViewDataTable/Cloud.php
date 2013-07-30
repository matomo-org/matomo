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
use Piwik\ViewDataTable;
use Piwik\View;

/**
 * Reads the requested DataTable from the API, and prepares the data to give
 * to Piwik_Visualization_Cloud that will display the tag cloud (via the template _dataTable_cloud.twig).
 *
 * @package Piwik
 * @subpackage ViewDataTable
 */
class Piwik_ViewDataTable_Cloud extends ViewDataTable
{
    public function setDisplayLogoInTagCloud($bool)
    {
        $this->viewProperties['display_logo_instead_of_label'] = $bool;
    }

    protected function getViewDataTableId()
    {
        return 'cloud';
    }
    
    public function __construct()
    {
        parent::__construct();

        $this->disableOffsetInformation();
        $this->disableExcludeLowPopulation();
        $this->viewProperties['display_logo_instead_of_label'] = false;
    }

    /**
     * @see ViewDataTable::main()
     *
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
        } catch (Exception $e) {
            Piwik::log("Failed to get data from API: " . $e->getMessage());

            $this->loadingError = array('message' => $e->getMessage());
        }

        $this->checkStandardDataTable();
        $this->postDataTableLoadedFromAPI();

        $visualization = new Piwik_Visualization_Cloud();
        $this->view = $this->buildView($visualization);
    }
}
