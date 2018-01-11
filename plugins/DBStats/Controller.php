<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats;

use Piwik\Metrics\Formatter;
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

        $_GET['showtitle'] = '1';

        $view->databaseUsageSummary = $this->renderReport('getDatabaseUsageSummary');
        $view->trackerDataSummary   = $this->renderReport('getTrackerDataSummary');
        $view->metricDataSummary    = $this->renderReport('getMetricDataSummary');
        $view->reportDataSummary    = $this->renderReport('getReportDataSummary');
        $view->adminDataSummary     = $this->renderReport('getAdminDataSummary');

        list($siteCount, $userCount, $totalSpaceUsed) = API::getInstance()->getGeneralInformation();

        $formatter = new Formatter();
        $view->siteCount      = $formatter->getPrettyNumber($siteCount);
        $view->userCount      = $formatter->getPrettyNumber($userCount);
        $view->totalSpaceUsed = $formatter->getPrettySizeFromBytes($totalSpaceUsed);

        return $view->render();
    }
}
