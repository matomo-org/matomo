<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\DataTable;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Piwik;
use Piwik\Tracker\Action;

/**
 * Class encapsulating logic to process Day/Period Archiving for the Actions reports
 *
 */
class Metrics
{

    public static $actionTypes = array(
        Action::TYPE_PAGE_URL,
        Action::TYPE_OUTLINK,
        Action::TYPE_DOWNLOAD,
        Action::TYPE_PAGE_TITLE,
        Action::TYPE_SITE_SEARCH,
    );

    public static $columnsToRenameAfterAggregation = array(
        PiwikMetrics::INDEX_NB_UNIQ_VISITORS            => PiwikMetrics::INDEX_SUM_DAILY_NB_UNIQ_VISITORS,
        PiwikMetrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS => PiwikMetrics::INDEX_PAGE_ENTRY_SUM_DAILY_NB_UNIQ_VISITORS,
        PiwikMetrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS  => PiwikMetrics::INDEX_PAGE_EXIT_SUM_DAILY_NB_UNIQ_VISITORS,
    );

    public static $columnsToDeleteAfterAggregation = array(
        PiwikMetrics::INDEX_NB_UNIQ_VISITORS,
        PiwikMetrics::INDEX_PAGE_ENTRY_NB_UNIQ_VISITORS,
        PiwikMetrics::INDEX_PAGE_EXIT_NB_UNIQ_VISITORS,
    );

    public static $columnsAggregationOperation = array(
        PiwikMetrics::INDEX_PAGE_MAX_TIME_GENERATION => 'max',
        PiwikMetrics::INDEX_PAGE_MIN_TIME_GENERATION => 'min'
    );

    public static function getActionMetrics()
    {
        $metricsConfig = array(
            PiwikMetrics::INDEX_NB_VISITS => array(
                'aggregation' => 'sum',
                'query' => "count(distinct log_link_visit_action.idvisit)"
            ),
            PiwikMetrics::INDEX_NB_UNIQ_VISITORS => array(
                'aggregation' => false,
                'query' => "count(distinct log_link_visit_action.idvisitor)"
            ),
            PiwikMetrics::INDEX_PAGE_NB_HITS => array(
                'aggregation' => 'sum',
                'query' => "count(*)"
            ),
            PiwikMetrics::INDEX_PAGE_SUM_TIME_GENERATION => array(
                'aggregation' => 'sum',
                'query' => "sum(
                        case when " . Action::DB_COLUMN_CUSTOM_FLOAT . " is null
                            then 0
                            else " . Action::DB_COLUMN_CUSTOM_FLOAT . "
                        end
                ) / 1000"
            ),
            PiwikMetrics::INDEX_PAGE_NB_HITS_WITH_TIME_GENERATION => array(
                'aggregation' => 'sum',
                'query' => "sum(
                    case when " . Action::DB_COLUMN_CUSTOM_FLOAT . " is null
                        then 0
                        else 1
                    end
                )"
            ),
            PiwikMetrics::INDEX_PAGE_MIN_TIME_GENERATION => array(
                'aggregation' => 'min',
                'query' => "min(" . Action::DB_COLUMN_CUSTOM_FLOAT . ") / 1000"
            ),
            PiwikMetrics::INDEX_PAGE_MAX_TIME_GENERATION => array(
                'aggregation' => 'max',
                'query' => "max(" . Action::DB_COLUMN_CUSTOM_FLOAT . ") / 1000"
            ),
        );

        Piwik::postEvent('Actions.Archiving.addActionMetrics', array(&$metricsConfig));

        return $metricsConfig;
    }
}
