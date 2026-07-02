<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites\RecordBuilders;

use Piwik\ArchiveProcessor;
use Piwik\ArchiveProcessor\Record;
use Piwik\Db;
use Piwik\Metrics as PiwikMetrics;
use Piwik\Plugin\Manager;
use Piwik\Plugins\BotTracking\BotDetector;
use Piwik\Plugins\BotTracking\Dao\BotRequestsDao;

/**
 * DEV-16541 spike: consolidates the numeric metrics the All Websites dashboard
 * needs into a single MultiSites-owned record set, so a range dashboard call
 * only prepares one plugin's archive per site instead of one per source plugin.
 */
class AllSitesMetrics extends ArchiveProcessor\RecordBuilder
{
    public const NB_VISITS            = 'MultiSites_nb_visits';
    public const NB_ACTIONS           = 'MultiSites_nb_actions';
    public const NB_PAGEVIEWS         = 'MultiSites_nb_pageviews';
    public const HITS                 = 'MultiSites_hits';
    public const REVENUE              = 'MultiSites_revenue';
    public const NB_CONVERSIONS       = 'MultiSites_nb_conversions';
    public const ORDERS               = 'MultiSites_orders';
    public const ECOMMERCE_REVENUE    = 'MultiSites_ecommerce_revenue';
    public const AI_CHATBOTS_REQUESTS = 'MultiSites_ai_chatbots_requests';

    public function getRecordMetadata(ArchiveProcessor $archiveProcessor): array
    {
        return [
            Record::make(Record::TYPE_NUMERIC, self::NB_VISITS),
            Record::make(Record::TYPE_NUMERIC, self::NB_ACTIONS),
            Record::make(Record::TYPE_NUMERIC, self::NB_PAGEVIEWS),
            Record::make(Record::TYPE_NUMERIC, self::HITS),
            Record::make(Record::TYPE_NUMERIC, self::REVENUE),
            Record::make(Record::TYPE_NUMERIC, self::NB_CONVERSIONS),
            Record::make(Record::TYPE_NUMERIC, self::ORDERS),
            Record::make(Record::TYPE_NUMERIC, self::ECOMMERCE_REVENUE),
            Record::make(Record::TYPE_NUMERIC, self::AI_CHATBOTS_REQUESTS),
        ];
    }

    protected function aggregate(ArchiveProcessor $archiveProcessor): array
    {
        $logAggregator = $archiveProcessor->getLogAggregator();

        // Visits + actions from log_visit — same query the core VisitsSummary path uses.
        $visitsRow = $logAggregator->queryVisitsByDimension()->fetch();
        $nbVisits  = (int) ($visitsRow[PiwikMetrics::INDEX_NB_VISITS] ?? 0);
        $nbActions = (int) ($visitsRow[PiwikMetrics::INDEX_NB_ACTIONS] ?? 0);

        // Hits + pageviews from log_link_visit_action.
        // Group by action type so we can split pageviews (type=1) from other action types.
        $hits = 0;
        $nbPageviews = 0;
        $actionsQuery = $logAggregator->queryActionsByDimension(
            ['action_type' => 'log_action.type'],
            '',
            ['count(distinct log_link_visit_action.idlink_va) as `hits`'],
            [],
            null,
            'idaction_url'
        );
        while ($row = $actionsQuery->fetch()) {
            $rowHits = (int) $row['hits'];
            $hits += $rowHits;
            if ((int) $row['action_type'] === 1) {
                $nbPageviews += $rowHits;
            }
        }

        // Conversions + revenue from log_conversion. Goals plugin has richer per-goal
        // and ecommerce splits; for the dashboard we only need totals across all
        // non-ecommerce goals plus ecommerce order totals.
        $revenue = 0.0;
        $conversions = 0;
        $orders = 0;
        $ecommerceRevenue = 0.0;

        if (Manager::getInstance()->isPluginActivated('Goals')) {
            // queryConversionsByDimension auto-prepends `idgoal` as a dimension
            // and applies getConversionsMetricFields(), so rows come back with
            // 'idgoal' plus Metrics::INDEX_GOAL_* integer keys.
            $goalsQuery = $logAggregator->queryConversionsByDimension();
            if ($goalsQuery !== false) {
                while ($row = $goalsQuery->fetch()) {
                    $idGoal = (int) $row['idgoal'];
                    $rowConv = (int) ($row[PiwikMetrics::INDEX_GOAL_NB_CONVERSIONS] ?? 0);
                    $rowRev = (float) ($row[PiwikMetrics::INDEX_GOAL_REVENUE] ?? 0);
                    // Total revenue across all goals mirrors legacy Goals_revenue
                    // record, which sums goal revenue including ecommerce (idgoal=0).
                    // Ecommerce-only totals are also emitted separately for the
                    // enhanced dashboard columns (Goals_revenue_0, Goals_nb_conversions_0).
                    if ($idGoal >= 0) {
                        $revenue += $rowRev;
                        $conversions += $rowConv;
                    }
                    if ($idGoal === 0) {
                        $orders += $rowConv;
                        $ecommerceRevenue += $rowRev;
                    }
                    // idgoal < 0 (abandoned carts, -1) is not counted in the dashboard totals.
                }
            }
        }

        // AI chatbot requests from log_bot — mirrors the count that the BotTracking
        // plugin's AIChatbotReports record builder emits, so the dashboard can drop
        // the BotTracking archive prep.
        $aiChatbotRequests = 0;
        if (Manager::getInstance()->isPluginActivated('BotTracking')) {
            $botTable = BotRequestsDao::getPrefixedTableName();
            $where = $logAggregator->getWhereStatement('bot', 'server_time');
            $sql = "SELECT COUNT(*) FROM `$botTable` AS bot WHERE bot.bot_type = ? AND $where";
            $bind = array_merge([BotDetector::BOT_TYPE_AI_CHATBOT], $logAggregator->getGeneralQueryBindParams());
            $aiChatbotRequests = (int) Db::fetchOne($sql, $bind);
        }

        return [
            self::NB_VISITS            => $nbVisits,
            self::NB_ACTIONS           => $nbActions,
            self::NB_PAGEVIEWS         => $nbPageviews,
            self::HITS                 => $hits,
            self::REVENUE              => round($revenue, 2),
            self::NB_CONVERSIONS       => $conversions,
            self::ORDERS               => $orders,
            self::ECOMMERCE_REVENUE    => round($ecommerceRevenue, 2),
            self::AI_CHATBOTS_REQUESTS => $aiChatbotRequests,
        ];
    }
}
