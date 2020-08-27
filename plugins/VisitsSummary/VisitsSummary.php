<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary;
use Piwik\DataTable;
use Piwik\Plugins\CoreHome\Columns\UserId;
use Piwik\Plugins\VisitsSummary\Reports\Get;

/**
 * Note: This plugin does not hook on Daily and Period Archiving like other Plugins because it reports the
 * very core metrics (visits, actions, visit duration, etc.) which are processed in the Core
 * Day class directly.
 * These metrics can be used by other Plugins so they need to be processed up front.
 *
 */
class VisitsSummary extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'API.API.getProcessedReport.end' => 'enrichProcessedReportIfVisitsSummaryGet',
        );
    }

    private function isRequestingVisitsSummaryGet($module, $method)
    {
        return ($module === 'VisitsSummary' && $method === 'get');
    }

    public function enrichProcessedReportIfVisitsSummaryGet(&$response, $infos)
    {
        if (empty($infos['parameters']['apiAction']) || empty($response['reportData'])) {
            return;
        }

        $params  = $infos['parameters'];
        $idSites = array($params['idSite']);
        $period  = $params['period'];
        $date    = $params['date'];
        $module  = $params['apiModule'];
        $method  = $params['apiAction'];

        if (!$this->isRequestingVisitsSummaryGet($module, $method)) {
            return;
        }

        $userId = new UserId();

        /** @var DataTable|DataTable\Map $dataTable */
        $dataTable = $response['reportData'];

        if (!$userId->hasDataTableUsers($dataTable) &&
            !$userId->isUsedInAtLeastOneSite($idSites, $period, $date)) {
            $report = new Get();
            $report->removeUsersFromProcessedReport($response);
        }
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/VisitsSummary/stylesheets/datatable.less";
    }

}

