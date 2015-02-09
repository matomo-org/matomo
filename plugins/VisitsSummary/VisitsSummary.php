<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary;
use Piwik\DataTable;
use Piwik\Plugins\CoreHome\Columns\UserId;

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
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'API.API.getProcessedReport.end' => 'enrichProcessedReportIfVisitsSummaryGet',
        );
    }

    public function enrichProcessedReportIfVisitsSummaryGet(&$response, $infos)
    {
        $params = $infos['parameters'];
        $module = $params[3];
        $method = $params[4];

        if ($module !== 'VisitsSummary' || $method !== 'get') {
            return;
        }

        $userId = new UserId();

        /** @var DataTable|DataTable\Map $dataTable */
        $dataTable = $response['reportData'];

        if ($userId->hasDataTableUsers($dataTable)) {
            return;
        }

        $idSites = $params[0];
        if (!is_array($idSites)) {
            $idSites = array($idSites);
        }

        $period = $params[1];
        $date   = $params[2];

        if ($userId->isUsedInAtLeastOneSite($idSites, $period, $date)) {
            return;
        }

        if (!empty($response['metadata']['metrics']['nb_users'])) {
            unset($response['metadata']['metrics']['nb_users']);
        }

        if (!empty($response['metadata']['metricsDocumentation']['nb_users'])) {
            unset($response['metadata']['metricsDocumentation']['nb_users']);
        }

        if (!empty($response['columns']['nb_users'])) {
            unset($response['columns']['nb_users']);
        }

        $dataTable->deleteColumn('nb_users');
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/VisitsSummary/stylesheets/datatable.less";
    }

}

