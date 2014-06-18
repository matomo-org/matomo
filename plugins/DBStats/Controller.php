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

        $view->databaseUsageSummary = $this->renderReport('getDatabaseUsageSummary');
        $view->trackerDataSummary   = $this->renderReport('getTrackerDataSummary');
        $view->metricDataSummary    = $this->renderReport('getMetricDataSummary');
        $view->reportDataSummary    = $this->renderReport('getReportDataSummary');
        $view->adminDataSummary     = $this->renderReport('getAdminDataSummary');

        list($siteCount, $userCount, $totalSpaceUsed) = API::getInstance()->getGeneralInformation();

        $view->siteCount      = MetricsFormatter::getPrettyNumber($siteCount);
        $view->userCount      = MetricsFormatter::getPrettyNumber($userCount);
        $view->totalSpaceUsed = MetricsFormatter::getPrettySizeFromBytes($totalSpaceUsed);

        return $view->render();
    }
}
