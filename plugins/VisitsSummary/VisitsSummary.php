<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary;

use Piwik\Piwik;

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
            'API.getReportMetadata'   => 'getReportMetadata',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
        );
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'         => Piwik::translate('VisitsSummary_VisitsSummary'),
            'name'             => Piwik::translate('VisitsSummary_VisitsSummary'),
            'module'           => 'VisitsSummary',
            'action'           => 'get',
            'metrics'          => array(
                'nb_uniq_visitors',
                'nb_visits',
                'nb_actions',
                'nb_actions_per_visit',
                'bounce_rate',
                'avg_time_on_site' => Piwik::translate('General_VisitDuration'),
                'max_actions'      => Piwik::translate('General_ColumnMaxActions'),
// Used to process metrics, not displayed/used directly
//								'sum_visit_length',
//								'nb_visits_converted',
            ),
            'processedMetrics' => false,
            'order'            => 1
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/VisitsSummary/stylesheets/datatable.less";
    }

}


