<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package DBStats
 */
namespace Piwik\Plugins\DBStats;

use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\View;
use Piwik\ViewDataTable\Factory;

/**
 * @package DBStats
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    /**
     * Returns the index for this plugin. Shows every other report defined by this plugin,
     * except the '...ByYear' reports. These can be loaded as related reports.
     *
     * Also, the 'getIndividual...Summary' reports are loaded by AJAX, as they can take
     * a significant amount of time to load on setups w/ lots of websites.
     */
    public function index()
    {
        Piwik::checkUserIsSuperUser();
        $view = new View('@DBStats/index');
        $this->setBasicVariablesView($view);

        $view->databaseUsageSummary = $this->getDatabaseUsageSummary(true);
        $view->trackerDataSummary = $this->getTrackerDataSummary(true);
        $view->metricDataSummary = $this->getMetricDataSummary(true);
        $view->reportDataSummary = $this->getReportDataSummary(true);
        $view->adminDataSummary = $this->getAdminDataSummary(true);

        list($siteCount, $userCount, $totalSpaceUsed) = API::getInstance()->getGeneralInformation();
        $view->siteCount = MetricsFormatter::getPrettyNumber($siteCount);
        $view->userCount = MetricsFormatter::getPrettyNumber($userCount);
        $view->totalSpaceUsed = MetricsFormatter::getPrettySizeFromBytes($totalSpaceUsed);

        echo $view->render();
    }

    /**
     * Shows a datatable that displays how much space the tracker tables, numeric
     * archive tables, report tables and other tables take up in the MySQL database.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     * @return string
     */
    public function getDatabaseUsageSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each individual log table
     * takes up in the MySQL database.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     * @return string|void
     */
    public function getTrackerDataSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each numeric archive table
     * takes up in the MySQL database.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     * @return string|void
     */
    public function getMetricDataSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each numeric archive table
     * takes up in the MySQL database, for each year of numeric data.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     * @return string|void
     */
    public function getMetricDataSummaryByYear($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each blob archive table
     * takes up in the MySQL database.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     * @return string|void
     */
    public function getReportDataSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each blob archive table
     * takes up in the MySQL database, for each year of blob data.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     * @return string|void
     */
    public function getReportDataSummaryByYear($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    /**
     * Shows a datatable that displays how many occurances there are of each individual
     * report type stored in the MySQL database.
     *
     * Goal reports and reports of the format: .*_[0-9]+ are grouped together.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     * @return string|void
     */
    public function getIndividualReportsSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    /**
     * Shows a datatable that displays how many occurances there are of each individual
     * metric type stored in the MySQL database.
     *
     * Goal metrics, metrics of the format .*_[0-9]+ and 'done...' metrics are grouped together.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     * @return string|void
     */
    public function getIndividualMetricsSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }

    /**
     * Shows a datatable that displays the amount of space each 'admin' table takes
     * up in the MySQL database.
     *
     * An 'admin' table is a table that is not central to analytics functionality.
     * So any table that isn't an archive table or a log table is an 'admin' table.
     *
     * @param bool $fetch If true, the rendered HTML datatable is returned, otherwise,
     *                    it is echoed.
     * @return string|void
     */
    public function getAdminDataSummary($fetch = false)
    {
        Piwik::checkUserIsSuperUser();
        return Factory::renderReport($this->pluginName, __FUNCTION__, $fetch);
    }
}
