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
 * Reads the requested DataTable from the API, and prepares the data to give
 * to Piwik_Visualization_Cloud that will display the tag cloud (via the template _dataTable_cloud.twig).
 *
 * @package Piwik
 * @subpackage Piwik_ViewDataTable
 */
class Piwik_ViewDataTable_Cloud extends Piwik_ViewDataTable
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

        $this->dataTableTemplate = '@CoreHome/_dataTable';
        $this->disableOffsetInformation();
        $this->disableExcludeLowPopulation();
        $this->viewProperties['display_logo_instead_of_label'] = false;
    }

    /**
     * @see Piwik_ViewDataTable::main()
     *
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
        } catch (Exception $e) {
            $this->isDataAvailable = false;
        }

        $this->checkStandardDataTable();
        $this->postDataTableLoadedFromAPI();

        $visualization = new Piwik_Visualization_Cloud();
        $this->view = $this->buildView($visualization);
    }
}
