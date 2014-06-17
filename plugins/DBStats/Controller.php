<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats;

use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\View;

/**
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
        Piwik::checkUserHasSuperUserAccess();
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

        return $view->render();
    }

    /**
     * Shows a datatable that displays how much space the tracker tables, numeric
     * archive tables, report tables and other tables take up in the MySQL database.
     *
     * @return string
     */
    public function getDatabaseUsageSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Shows a datatable that displays the amount of space each individual log table
     * takes up in the MySQL database.
     * @return string|void
     */
    public function getTrackerDataSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Shows a datatable that displays the amount of space each numeric archive table
     * takes up in the MySQL database.
     *
     * @return string|void
     */
    public function getMetricDataSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Shows a datatable that displays the amount of space each numeric archive table
     * takes up in the MySQL database, for each year of numeric data.
     *
     * @return string|void
     */
    public function getMetricDataSummaryByYear()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Shows a datatable that displays the amount of space each blob archive table
     * takes up in the MySQL database.
     *
     * @return string|void
     */
    public function getReportDataSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Shows a datatable that displays the amount of space each blob archive table
     * takes up in the MySQL database, for each year of blob data.
     *
     * @return string|void
     */
    public function getReportDataSummaryByYear()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Shows a datatable that displays how many occurances there are of each individual
     * report type stored in the MySQL database.
     *
     * Goal reports and reports of the format: .*_[0-9]+ are grouped together.
     *
     * @return string|void
     */
    public function getIndividualReportsSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Shows a datatable that displays how many occurances there are of each individual
     * metric type stored in the MySQL database.
     *
     * Goal metrics, metrics of the format .*_[0-9]+ and 'done...' metrics are grouped together.
     *
     * @return string|void
     */
    public function getIndividualMetricsSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->renderReport(__FUNCTION__);
    }

    /**
     * Shows a datatable that displays the amount of space each 'admin' table takes
     * up in the MySQL database.
     *
     * An 'admin' table is a table that is not central to analytics functionality.
     * So any table that isn't an archive table or a log table is an 'admin' table.
     *
     * @return string|void
     */
    public function getAdminDataSummary()
    {
        Piwik::checkUserHasSuperUserAccess();
        return $this->renderReport(__FUNCTION__);
    }
}
