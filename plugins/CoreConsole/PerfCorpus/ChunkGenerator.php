<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Piwik\Tracker\Action;

/**
 * Turns one chunk's plan records into the rows of all five log tables.
 *
 * The chunk owns pre-allocated ranges of idvisit, idlink_va and unique-URL idaction, so every id
 * written here is computed, never fetched, and two workers running side by side can never collide.
 *
 * Plan records are sorted by start time before ids are handed out, which makes idvisit ordered by
 * time within the day as well as across days - the visits log leans on ORDER BY idvisit DESC as
 * its recency proxy, and an unsorted chunk would make recent visits appear in the wrong order.
 */
class ChunkGenerator
{
    /** Actions per visit are spaced by this, compressed if the visit would otherwise cross midnight. */
    private const ACTION_GAP_MEDIAN_SECONDS = 38.0;
    private const ACTION_GAP_SIGMA = 1.05;

    /** Chance the next pageview stays in the section the visitor is already in (B4). */
    private const SECTION_STICKINESS = 0.6;

    public const VISIT_COLUMNS = [
        'idvisit', 'idsite', 'idvisitor', 'visit_first_action_time', 'visit_last_action_time',
        'visit_total_time', 'config_id', 'location_ip', 'user_id', 'visitor_returning',
        'visitor_count_visits', 'visitor_seconds_since_first', 'visitor_seconds_since_last',
        'visit_goal_converted', 'visit_goal_buyer', 'visit_entry_idaction_url',
        'visit_entry_idaction_name', 'visit_exit_idaction_url', 'visit_exit_idaction_name',
        'visit_total_actions', 'visit_total_interactions', 'visit_total_events',
        'visit_total_searches', 'referer_type', 'referer_name', 'referer_keyword', 'referer_url',
        'location_browser_lang', 'config_browser_engine', 'config_browser_name',
        'config_browser_version', 'config_client_type', 'config_device_brand',
        'config_device_model', 'config_device_type', 'config_os', 'config_os_version',
        'config_resolution', 'config_cookie', 'location_city', 'location_country',
        'location_latitude', 'location_longitude', 'location_region', 'visitor_localtime',
        'last_idlink_va', 'profilable', 'custom_dimension_1', 'custom_dimension_2',
        'custom_dimension_3', 'custom_dimension_4', 'custom_dimension_5',
    ];

    public const ACTION_COLUMNS = [
        'idlink_va', 'idsite', 'idvisitor', 'idvisit', 'server_time', 'idaction_url',
        'idaction_name', 'idaction_url_ref', 'idaction_name_ref', 'idpageview',
        'pageview_position', 'time_spent_ref_action', 'time_spent', 'search_cat', 'search_count',
        'idaction_event_category', 'idaction_event_action', 'time_network', 'time_server',
        'time_transfer', 'time_dom_processing', 'time_dom_completion', 'time_on_load',
        'custom_dimension_1', 'custom_dimension_2',
    ];

    public const CONVERSION_COLUMNS = [
        'idvisit', 'idsite', 'idvisitor', 'server_time', 'idaction_url', 'idlink_va', 'idgoal',
        'buster', 'idorder', 'items', 'url', 'revenue', 'revenue_subtotal', 'revenue_tax',
        'revenue_shipping', 'revenue_discount', 'pageviews_before', 'visitor_returning',
        'visitor_seconds_since_first', 'visitor_count_visits', 'referer_type', 'referer_name',
        'referer_keyword', 'config_browser_name', 'config_client_type', 'config_device_brand',
        'config_device_model', 'config_device_type', 'location_city', 'location_country',
        'location_latitude', 'location_longitude', 'location_region', 'custom_dimension_1',
        'custom_dimension_2', 'custom_dimension_3', 'custom_dimension_4', 'custom_dimension_5',
    ];

    public const ITEM_COLUMNS = [
        'idsite', 'idvisitor', 'server_time', 'idvisit', 'idorder', 'idaction_sku',
        'idaction_name', 'idaction_category', 'idaction_category2', 'idaction_category3',
        'idaction_category4', 'idaction_category5', 'price', 'quantity', 'deleted',
    ];

    /**
     * Every column this writes, by table, so the command can check up front that they all exist.
     * Matomo's log tables are extended at install time by the dimension classes, which differ by
     * version and by which plugins are active - a missing column should be a clear message before
     * anything is written, not an SQL error two hours into a load.
     *
     * @return array<string,string[]>
     */
    public static function getRequiredColumns(): array
    {
        return [
            'log_visit' => self::VISIT_COLUMNS,
            'log_link_visit_action' => self::ACTION_COLUMNS,
            'log_conversion' => self::CONVERSION_COLUMNS,
            'log_conversion_item' => self::ITEM_COLUMNS,
            'log_action' => ActionDictionary::COLUMNS,
        ];
    }

    private RunContext $context;
    private Profile $profile;
    private ActionDictionary $dictionary;
    private int $seed;
    private array $referrerTypeCdf;

    public function __construct(RunContext $context, ActionDictionary $dictionary)
    {
        $this->context = $context;
        $this->profile = $context->getProfile();
        $this->dictionary = $dictionary;
        $this->seed = $this->profile->getSeed();
        $this->referrerTypeCdf = Vocabulary::REFERRER_TYPE_CDF;
    }

    /**
     * @return array{rows: int, visits: int, actions: int, conversions: int, items: int, tailUrls: int}
     */
    public function generate(array $chunk, RowSink $sink, ?callable $onProgress = null): array
    {
        $date = $chunk['day'];
        $shard = (int) $chunk['shard'];
        $path = PlanSpool::pathFor($this->context->getSpoolDir(), $date, $shard);
        $records = PlanSpool::read($path);

        // Sorted by start time, so ids increase with time inside the day too.
        usort($records, static function (array $a, array $b): int {
            return $a['startSecond'] <=> $b['startSecond'] ?: $a['ordinal'] <=> $b['ordinal'];
        });

        $dayStart = strtotime($date . ' 00:00:00 UTC');
        $nextIdVisit = (int) $chunk['idvisit_start'];
        $nextIdLinkVa = (int) $chunk['idlink_va_start'];
        $nextTailId = (int) $chunk['idaction_tail_start'];
        $tailLimit = $nextTailId + (int) $chunk['idaction_tail_count'];

        $visitRows = [];
        $actionRows = [];
        $conversionRows = [];
        $itemRows = [];
        $tailRows = [];

        $stats = ['visits' => 0, 'actions' => 0, 'conversions' => 0, 'items' => 0, 'tailUrls' => 0];

        foreach ($records as $index => $record) {
            $visit = $this->generateVisit(
                $record,
                $dayStart,
                $nextIdVisit,
                $nextIdLinkVa,
                $nextTailId,
                $tailLimit
            );

            $visitRows[] = $visit['visit'];
            foreach ($visit['actions'] as $row) {
                $actionRows[] = $row;
            }
            foreach ($visit['conversions'] as $row) {
                $conversionRows[] = $row;
            }
            foreach ($visit['items'] as $row) {
                $itemRows[] = $row;
            }
            foreach ($visit['tailUrls'] as $row) {
                $tailRows[] = $row;
            }

            $nextIdVisit++;
            $nextIdLinkVa += count($visit['actions']);
            $nextTailId += count($visit['tailUrls']);

            $stats['visits']++;
            $stats['actions'] += count($visit['actions']);
            $stats['conversions'] += count($visit['conversions']);
            $stats['items'] += count($visit['items']);
            $stats['tailUrls'] += count($visit['tailUrls']);

            if (0 === $index % 2000) {
                $this->drain($sink, $tailRows, $visitRows, $actionRows, $conversionRows, $itemRows);

                if (null !== $onProgress) {
                    $onProgress($index, count($records));
                }
            }
        }

        $this->drain($sink, $tailRows, $visitRows, $actionRows, $conversionRows, $itemRows);
        $sink->flush();

        $stats['rows'] = $stats['visits'] + $stats['actions'] + $stats['conversions']
            + $stats['items'] + $stats['tailUrls'];

        return $stats;
    }

    /**
     * log_action rows go first: the other tables reference them, and a reader that arrives between
     * two batches should never see an idaction that does not resolve.
     */
    private function drain(
        RowSink $sink,
        array &$tailRows,
        array &$visitRows,
        array &$actionRows,
        array &$conversionRows,
        array &$itemRows
    ): void {
        if (!empty($tailRows)) {
            $sink->write('log_action', ActionDictionary::COLUMNS, $tailRows);
            $tailRows = [];
        }
        if (!empty($visitRows)) {
            $sink->write('log_visit', self::VISIT_COLUMNS, $visitRows);
            $visitRows = [];
        }
        if (!empty($actionRows)) {
            $sink->write('log_link_visit_action', self::ACTION_COLUMNS, $actionRows);
            $actionRows = [];
        }
        if (!empty($conversionRows)) {
            $sink->write('log_conversion', self::CONVERSION_COLUMNS, $conversionRows);
            $conversionRows = [];
        }
        if (!empty($itemRows)) {
            $sink->write('log_conversion_item', self::ITEM_COLUMNS, $itemRows);
            $itemRows = [];
        }
    }

    private function generateVisit(
        array $record,
        int $dayStart,
        int $idVisit,
        int $idLinkVa,
        int $nextTailId,
        int $tailLimit
    ): array {
        $ordinal = $record['ordinal'];
        $visitor = VisitorProfile::build($this->seed, $ordinal, $this->profile);
        $rng = Rng::forStream($this->seed, Rng::S_VISIT, $ordinal, $record['visitIndex']);

        $idSite = $record['idsite'];
        $actionCount = $record['actionCount'];
        $startSecond = $record['startSecond'];

        $times = $this->buildActionTimes($rng, $actionCount, $startSecond);
        $referrer = $this->buildReferrer($rng);

        $actions = [];
        $tailUrls = [];
        $events = 0;
        $searches = 0;
        $pageviewPosition = 0;
        $previousUrlAction = null;
        $previousNameAction = null;
        $entryUrlAction = null;
        $entryNameAction = null;
        $exitUrlAction = null;
        $exitNameAction = null;
        $currentHotIndex = $rng->nextZipfRank($this->dictionary->getBlockCount('hotUrl'), Profile::ZIPF_EXPONENT) - 1;
        $lastConvertibleAction = null;

        for ($i = 0; $i < $actionCount; $i++) {
            $serverTime = $dayStart + $times[$i];
            $gap = 0 === $i ? null : $times[$i] - $times[$i - 1];
            $typeIndex = $rng->pickFromCdf(Profile::ACTION_TYPE_CDF);

            $row = array_fill(0, count(self::ACTION_COLUMNS), null);
            $row[0] = $idLinkVa + $i;
            $row[1] = $idSite;
            $row[2] = $visitor->idVisitorHex;
            $row[3] = $idVisit;
            $row[4] = gmdate('Y-m-d H:i:s', $serverTime);
            $row[7] = $previousUrlAction ?? 0;
            $row[8] = $previousNameAction;
            $row[11] = $gap;
            $row[12] = $gap;
            $row[23] = 'cd-' . ($ordinal % 20);
            $row[24] = 'trace-' . ($rng->nextInt(0, 99999));

            if (0 === $typeIndex) {
                // Page view. Either a hot page reached by a walk over the URL space, or a URL that
                // will never be seen again - the two regimes a high-traffic site shows at once.
                $isTail = $rng->nextBool($this->profile->getUniqueUrlShare())
                    && ($nextTailId + count($tailUrls)) < $tailLimit;

                if ($isTail) {
                    $tailId = $nextTailId + count($tailUrls);
                    $name = Vocabulary::tailUrl($tailId);
                    $tailUrls[] = [$tailId, $name, ActionDictionary::hash($name), Action::TYPE_PAGE_URL, 2];
                    $idActionUrl = $tailId;
                    $idActionName = $this->dictionary->id('tailTitle', $rng->nextInt(0, 9));
                } else {
                    $currentHotIndex = $this->walk($rng, $currentHotIndex);
                    $idActionUrl = $this->dictionary->id('hotUrl', $currentHotIndex);
                    $idActionName = $this->dictionary->id('hotTitle', $currentHotIndex);
                }

                $pageviewPosition++;
                $row[5] = $idActionUrl;
                $row[6] = $idActionName;
                $row[9] = substr(md5($idVisit . ':' . $i), 0, 6);
                $row[10] = $pageviewPosition;

                // PagePerformance: every pageview carries all six timings, with a long slow tail.
                $row[17] = $this->timing($rng, 40);
                $row[18] = $this->timing($rng, 180);
                $row[19] = $this->timing($rng, 60);
                $row[20] = $this->timing($rng, 300);
                $row[21] = $this->timing($rng, 500);
                $row[22] = $this->timing($rng, 120);

                $previousUrlAction = $idActionUrl;
                $previousNameAction = $idActionName;
                $entryUrlAction = $entryUrlAction ?? $idActionUrl;
                $entryNameAction = $entryNameAction ?? $idActionName;
                $exitUrlAction = $idActionUrl;
                $exitNameAction = $idActionName;
                $lastConvertibleAction = ['idaction' => $idActionUrl, 'idlink_va' => $row[0], 'time' => $serverTime];
            } elseif (1 === $typeIndex) {
                $events++;
                $row[15] = $this->dictionary->randomId('eventCategory', $rng);
                $row[16] = $this->dictionary->randomId('eventAction', $rng);
                $row[6] = $this->dictionary->randomId('eventName', $rng);
            } elseif (2 === $typeIndex) {
                $searches++;
                $row[6] = $this->dictionary->randomId('searchKeyword', $rng);
                $row[13] = 'cat-' . $rng->nextInt(0, 19);
                $row[14] = $rng->nextInt(0, 200);
            } elseif (3 === $typeIndex) {
                $row[5] = $this->dictionary->randomId('download', $rng);
            } else {
                $row[5] = $this->dictionary->randomId('outlink', $rng);
            }

            $actions[] = $row;
        }

        // A visit with no pageview at all still needs entry and exit actions to look sane.
        if (null === $entryUrlAction) {
            $currentHotIndex = $this->walk($rng, $currentHotIndex);
            $entryUrlAction = $this->dictionary->id('hotUrl', $currentHotIndex);
            $entryNameAction = $this->dictionary->id('hotTitle', $currentHotIndex);
            $exitUrlAction = $entryUrlAction;
            $exitNameAction = $entryNameAction;
            $lastConvertibleAction = [
                'idaction' => $entryUrlAction,
                'idlink_va' => $idLinkVa,
                'time' => $dayStart + $times[0],
            ];
        }

        $firstTime = $dayStart + $times[0];
        $lastTime = $dayStart + $times[$actionCount - 1];

        $conversions = [];
        $items = [];

        if ($record['flags'] & PlanSpool::FLAG_GOAL_CONVERSION) {
            $conversions[] = $this->buildConversion(
                $rng,
                $record,
                $visitor,
                $idVisit,
                $idSite,
                $lastConvertibleAction,
                $rng->nextInt(1, Profile::GOAL_COUNT),
                null,
                0,
                $referrer,
                $pageviewPosition
            );
        }

        if ($record['flags'] & PlanSpool::FLAG_ECOMMERCE_ORDER) {
            $idOrder = sprintf('%d-%d', $idVisit, $record['visitIndex']);
            $conversions[] = $this->buildConversion(
                $rng,
                $record,
                $visitor,
                $idVisit,
                $idSite,
                $lastConvertibleAction,
                0,
                $idOrder,
                $record['itemCount'],
                $referrer,
                $pageviewPosition
            );
            $items = $this->buildItems($rng, $record, $visitor, $idVisit, $idSite, $idOrder, $lastConvertibleAction['time']);
        }

        $visit = [
            $idVisit,
            $idSite,
            $visitor->idVisitorHex,
            gmdate('Y-m-d H:i:s', $firstTime),
            gmdate('Y-m-d H:i:s', $lastTime),
            $lastTime - $firstTime,
            $visitor->configIdHex,
            $visitor->ipHex,
            $visitor->userId,
            $record['visitIndex'] > 1 ? 1 : 0,
            $record['visitIndex'],
            $record['secondsSinceFirst'],
            $record['visitIndex'] > 1 ? $record['secondsSinceLast'] : null,
            $record['flags'] & PlanSpool::FLAG_GOAL_CONVERSION ? 1 : 0,
            $record['flags'] & PlanSpool::FLAG_ECOMMERCE_ORDER ? 1 : 0,
            $entryUrlAction,
            $entryNameAction,
            $exitUrlAction,
            $exitNameAction,
            $actionCount,
            $actionCount,
            $events,
            $searches,
            $referrer['type'],
            $referrer['name'],
            $referrer['keyword'],
            $referrer['url'],
            $visitor->language,
            $visitor->browserEngine,
            $visitor->browserName,
            $visitor->browserVersion,
            $visitor->clientType,
            $visitor->deviceBrand,
            $visitor->deviceModel,
            $visitor->deviceType,
            $visitor->os,
            $visitor->osVersion,
            $visitor->resolution,
            1,
            $visitor->city,
            $visitor->country,
            $visitor->latitude,
            $visitor->longitude,
            $visitor->region,
            gmdate('H:i:s', $firstTime + $visitor->localtimeOffsetSeconds),
            $idLinkVa + $actionCount - 1,
            null === $visitor->userId ? 0 : 1,
            $visitor->customDimensions[1],
            $visitor->customDimensions[2],
            $visitor->customDimensions[3],
            $visitor->customDimensions[4],
            $visitor->customDimensions[5],
        ];

        return [
            'visit' => $visit,
            'actions' => $actions,
            'conversions' => $conversions,
            'items' => $items,
            'tailUrls' => $tailUrls,
        ];
    }

    /**
     * Action timestamps inside the visit. Gaps are drawn, then compressed if the visit would run
     * past midnight - visits must not cross the day boundary, which is what keeps a day's chunk
     * self-contained.
     *
     * @return int[] seconds from midnight, ascending
     */
    private function buildActionTimes(Rng $rng, int $actionCount, int $startSecond): array
    {
        $times = [$startSecond];
        $gaps = [];
        $total = 0;

        for ($i = 1; $i < $actionCount; $i++) {
            $gap = max(1, (int) round($rng->nextLogNormal(self::ACTION_GAP_MEDIAN_SECONDS, self::ACTION_GAP_SIGMA)));
            $gaps[] = $gap;
            $total += $gap;
        }

        $available = 86399 - $startSecond;

        if ($total > $available && $total > 0) {
            $scale = $available / $total;
            $rescaled = 0;
            foreach ($gaps as $index => $gap) {
                $gaps[$index] = max(1, (int) floor($gap * $scale));
                $rescaled += $gaps[$index];
            }
            // Even at one second each the visit can be longer than the day allows; clamp instead
            // of letting it spill over midnight.
            if ($rescaled > $available) {
                $gaps = array_fill(0, count($gaps), 0);
            }
        }

        $current = $startSecond;
        foreach ($gaps as $gap) {
            $current = min(86399, $current + $gap);
            $times[] = $current;
        }

        return $times;
    }

    /**
     * A step in the walk over the URL space: mostly stay near where the visitor already is, which
     * is what makes idaction_url_ref chains meaningful for Transitions.
     */
    private function walk(Rng $rng, int $currentIndex): int
    {
        $poolSize = $this->dictionary->getBlockCount('hotUrl');

        if ($rng->nextBool(self::SECTION_STICKINESS)) {
            // Neighbouring indexes share a section by construction of Vocabulary::hotUrl().
            $step = $rng->nextInt(1, 40) * count(Vocabulary::SECTIONS);

            return ($currentIndex + $step) % $poolSize;
        }

        return $rng->nextZipfRank($poolSize, Profile::ZIPF_EXPONENT) - 1;
    }

    private function timing(Rng $rng, int $median): ?int
    {
        if ($rng->nextBool(0.05)) {
            return null; // not every pageview reports every timing
        }

        return min(65535, max(0, (int) round($rng->nextLogNormal($median, 0.8))));
    }

    private function buildReferrer(Rng $rng): array
    {
        $typeIndex = $rng->pickFromCdf($this->referrerTypeCdf);
        $type = Vocabulary::REFERRER_TYPES[$typeIndex];

        switch ($type) {
            case 2:
                return [
                    'type' => 2,
                    'name' => Vocabulary::SEARCH_ENGINES[$rng->nextInt(0, count(Vocabulary::SEARCH_ENGINES) - 1)],
                    // Search engines stopped passing keywords years ago; mostly null is correct.
                    'keyword' => $rng->nextBool(0.05) ? Vocabulary::searchKeyword($rng->nextInt(0, 9999)) : null,
                    'url' => null,
                ];
            case 3:
                $domain = Vocabulary::referrerWebsite($rng->nextZipfRank(5000, 1.0) - 1);

                return [
                    'type' => 3,
                    'name' => $domain,
                    'keyword' => null,
                    'url' => 'https://' . $domain . '/article/' . $rng->nextInt(1, 5000),
                ];
            case 6:
                // Core has no campaign_* columns - those belong to the premium campaign plugin.
                // A campaign visit is referer_type 6 with the campaign in name and keyword.
                return [
                    'type' => 6,
                    'name' => Vocabulary::campaignName($rng->nextInt(0, 199)),
                    'keyword' => Vocabulary::CAMPAIGN_MEDIUMS[$rng->nextInt(0, count(Vocabulary::CAMPAIGN_MEDIUMS) - 1)]
                        . '-' . Vocabulary::CAMPAIGN_SOURCES[$rng->nextInt(0, count(Vocabulary::CAMPAIGN_SOURCES) - 1)],
                    'url' => null,
                ];
            default:
                return ['type' => 1, 'name' => null, 'keyword' => null, 'url' => null];
        }
    }

    private function buildConversion(
        Rng $rng,
        array $record,
        VisitorProfile $visitor,
        int $idVisit,
        int $idSite,
        array $action,
        int $idGoal,
        ?string $idOrder,
        int $itemCount,
        array $referrer,
        int $pageviewsBefore
    ): array {
        $revenue = null;
        $subtotal = null;
        $tax = null;
        $shipping = null;
        $discount = null;

        if (null !== $idOrder) {
            $subtotal = round($rng->nextLogNormal(48.0, 0.9), 2);
            $tax = round($subtotal * 0.19, 2);
            $shipping = $rng->nextBool(0.6) ? 4.95 : 0.0;
            $discount = $rng->nextBool(0.15) ? round($subtotal * 0.1, 2) : 0.0;
            $revenue = round($subtotal + $tax + $shipping - $discount, 2);
        } elseif ($rng->nextBool(0.35)) {
            $revenue = round($rng->nextLogNormal(12.0, 0.8), 2);
        }

        // The dictionary can rebuild any name from its id, so the denormalised url column holds
        // the real converting page rather than a placeholder.
        $urlIndex = $action['idaction'] - $this->dictionary->getBlockBase('hotUrl');
        $url = $urlIndex >= 0 && $urlIndex < $this->dictionary->getBlockCount('hotUrl')
            ? Vocabulary::hotUrl($urlIndex)
            : Vocabulary::tailUrl($action['idaction']);

        return [
            $idVisit,
            $idSite,
            $visitor->idVisitorHex,
            gmdate('Y-m-d H:i:s', $action['time']),
            $action['idaction'],
            $action['idlink_va'],
            $idGoal,
            null === $idOrder ? 0 : crc32($idOrder) % 100000,
            $idOrder,
            null === $idOrder ? null : $itemCount,
            'https://' . $url,
            $revenue,
            $subtotal,
            $tax,
            $shipping,
            $discount,
            $pageviewsBefore,
            $record['visitIndex'] > 1 ? 1 : 0,
            $record['secondsSinceFirst'],
            $record['visitIndex'],
            $referrer['type'],
            $referrer['name'],
            $referrer['keyword'],
            $visitor->browserName,
            $visitor->clientType,
            $visitor->deviceBrand,
            $visitor->deviceModel,
            $visitor->deviceType,
            $visitor->city,
            $visitor->country,
            $visitor->latitude,
            $visitor->longitude,
            $visitor->region,
            $visitor->customDimensions[1],
            $visitor->customDimensions[2],
            $visitor->customDimensions[3],
            $visitor->customDimensions[4],
            $visitor->customDimensions[5],
        ];
    }

    private function buildItems(
        Rng $rng,
        array $record,
        VisitorProfile $visitor,
        int $idVisit,
        int $idSite,
        string $idOrder,
        int $time
    ): array {
        $rows = [];
        $skuCount = $this->dictionary->getBlockCount('sku');
        $categoryCount = $this->dictionary->getBlockCount('productCategory');
        $used = [];

        for ($i = 0; $i < $record['itemCount']; $i++) {
            $skuIndex = $rng->nextInt(0, $skuCount - 1);

            // The primary key is (idvisit, idorder, idaction_sku): the same product twice in one
            // order would be a duplicate key, so raise the quantity instead.
            if (isset($used[$skuIndex])) {
                continue;
            }
            $used[$skuIndex] = true;

            $rows[] = [
                $idSite,
                $visitor->idVisitorHex,
                gmdate('Y-m-d H:i:s', $time),
                $idVisit,
                $idOrder,
                $this->dictionary->id('sku', $skuIndex),
                $this->dictionary->id('productName', $skuIndex),
                $this->dictionary->id('productCategory', $skuIndex % $categoryCount),
                $this->dictionary->id('productCategory', ($skuIndex + 7) % $categoryCount),
                0,
                0,
                0,
                round($rng->nextLogNormal(18.0, 0.7), 2),
                $rng->nextInt(1, 3),
                0,
            ];
        }

        return $rows;
    }
}
