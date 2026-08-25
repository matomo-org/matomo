<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

use Piwik\Common;
use Piwik\Db;

/**
 * Proves a generated corpus is correct before anything is measured against it.
 *
 * The point is not to be reassuring, it is to catch the failure mode that makes a generated
 * dataset misleading: data that loads without error and is quietly the wrong shape. An earlier
 * generator produced a corpus with a 100% bounce rate on paper, no hot pages at all, and empty
 * browser, geo and campaign columns - none of which shows up as an error anywhere.
 *
 * Two levels, because they cost very different amounts:
 *
 *   fast  reconciliation. Every chunk finished, id ranges contiguous and non-overlapping, and
 *         the rows actually present match what was planned. Seconds, and it runs at the end of
 *         every generate.
 *   full  distributions and invariants - the checks that catch wrong shape rather than wrong
 *         count. Minutes to hours at p200; run it once before snapshotting.
 */
class Verifier
{
    public const LEVEL_FAST = 'fast';
    public const LEVEL_FULL = 'full';

    private RunContext $context;
    private Profile $profile;

    /** @var array[] */
    private array $results = [];

    private ?array $bounds = null;

    public function __construct(RunContext $context)
    {
        $this->context = $context;
        $this->profile = $context->getProfile();
    }

    /**
     * @return array{passed: bool, checks: array[]}
     */
    public function run(string $level): array
    {
        $this->results = [];

        $checks = [
            'checkChunksFinished',
            'checkIdRangesContiguous',
            'checkPlannedRowsPresent',
            'checkAutoIncrementAboveCorpus',
        ];

        if (self::LEVEL_FULL === $level) {
            $checks = array_merge($checks, [
                'checkVisitInvariants',
                'checkNoOrphanActions',
                'checkConversionsReferenceRealGoals',
                'checkVisitorHistory',
                'checkDistributions',
                'checkUrlPopularity',
                'checkEveryDayPopulated',
                'checkUniqueVisitorWindows',
                'checkColumnsPopulated',
                'checkMegaVisits',
            ]);
        }

        foreach ($checks as $check) {
            try {
                $this->$check();
            } catch (\Throwable $e) {
                // A check that cannot run is a failure, but it must not cost the report the
                // other twenty - the whole point is to see everything that is wrong at once.
                $this->record($check . ' (errored)', false, $e->getMessage());
            }
        }

        $passed = true;
        foreach ($this->results as $result) {
            if (!$result['passed']) {
                $passed = false;
            }
        }

        return ['passed' => $passed, 'checks' => $this->results];
    }

    private function record(string $name, bool $passed, string $detail): void
    {
        $this->results[] = ['name' => $name, 'passed' => $passed, 'detail' => $detail];
    }

    private function table(string $name): string
    {
        return Common::prefixTable($name);
    }

    /**
     * The id ranges this run owns.
     *
     * Every distribution check is scoped to them rather than to the whole table. The corpus is
     * usually not the only thing in the log tables by the time anyone verifies it - a churn run
     * appends live traffic, and --allow-non-empty permits generating on top of existing data - and
     * a check that measured all of it would report failures that say nothing about the corpus.
     */
    private function bounds(): array
    {
        if (null !== $this->bounds) {
            return $this->bounds;
        }

        $table = ChunkQueue::chunkTable();

        $row = Db::fetchRow(
            "SELECT MIN(`idvisit_start`) AS `visit_from`,
                    MAX(`idvisit_start` + `visit_count` - 1) AS `visit_to`,
                    MIN(`idlink_va_start`) AS `link_from`,
                    MAX(`idlink_va_start` + `action_count` - 1) AS `link_to`,
                    MAX(`idaction_tail_start` + `idaction_tail_count` - 1) AS `tail_to`
               FROM `$table` WHERE `idrun` = ? AND `phase` = ? AND `idvisit_start` IS NOT NULL",
            [$this->context->getRunId(), ChunkQueue::PHASE_LOAD]
        );

        $this->bounds = [
            'visitFrom' => (int) ($row['visit_from'] ?? 0),
            'visitTo' => (int) ($row['visit_to'] ?? 0),
            'linkFrom' => (int) ($row['link_from'] ?? 0),
            'linkTo' => (int) ($row['link_to'] ?? 0),
            'tailTo' => (int) ($row['tail_to'] ?? 0),
        ];

        return $this->bounds;
    }

    /** SQL fragment restricting a table to this run's visits. */
    private function visitScope(string $alias = ''): string
    {
        $bounds = $this->bounds();
        $prefix = '' === $alias ? '' : '`' . $alias . '`.';

        return sprintf('%s`idvisit` BETWEEN %d AND %d', $prefix, $bounds['visitFrom'], $bounds['visitTo']);
    }

    /** SQL fragment restricting log_link_visit_action to this run's actions. */
    private function actionScope(string $alias = ''): string
    {
        $bounds = $this->bounds();
        $prefix = '' === $alias ? '' : '`' . $alias . '`.';

        return sprintf('%s`idlink_va` BETWEEN %d AND %d', $prefix, $bounds['linkFrom'], $bounds['linkTo']);
    }

    // ------------------------------------------------------------------ fast

    private function checkChunksFinished(): void
    {
        $table = ChunkQueue::chunkTable();

        $rows = Db::fetchAll(
            "SELECT `phase`, `status`, COUNT(*) AS `c` FROM `$table` WHERE `idrun` = ? GROUP BY `phase`, `status`",
            [$this->context->getRunId()]
        );

        $unfinished = 0;
        $total = 0;

        foreach ($rows as $row) {
            $total += (int) $row['c'];
            if (ChunkQueue::STATUS_DONE !== (int) $row['status']) {
                $unfinished += (int) $row['c'];
            }
        }

        $this->record(
            'every chunk finished',
            0 === $unfinished && $total > 0,
            sprintf('%d of %d chunks done', $total - $unfinished, $total)
        );
    }

    /**
     * Gaps would waste ids harmlessly, but an overlap would mean two chunks writing the same
     * primary keys - the one failure mode that would silently corrupt the corpus.
     */
    private function checkIdRangesContiguous(): void
    {
        $table = ChunkQueue::chunkTable();

        $overlaps = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM (
                SELECT `idvisit_start`,
                       LAG(`idvisit_start` + `visit_count`) OVER (ORDER BY `day_index`, `shard`) AS `prev_end`
                  FROM `$table`
                 WHERE `idrun` = ? AND `phase` = ?
             ) `z` WHERE `prev_end` IS NOT NULL AND `prev_end` > `idvisit_start`",
            [$this->context->getRunId(), ChunkQueue::PHASE_LOAD]
        );

        $linkOverlaps = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM (
                SELECT `idlink_va_start`,
                       LAG(`idlink_va_start` + `action_count`) OVER (ORDER BY `day_index`, `shard`) AS `prev_end`
                  FROM `$table`
                 WHERE `idrun` = ? AND `phase` = ?
             ) `z` WHERE `prev_end` IS NOT NULL AND `prev_end` > `idlink_va_start`",
            [$this->context->getRunId(), ChunkQueue::PHASE_LOAD]
        );

        $this->record(
            'id ranges do not overlap',
            0 === $overlaps && 0 === $linkOverlaps,
            sprintf('%d idvisit overlaps, %d idlink_va overlaps', $overlaps, $linkOverlaps)
        );
    }

    private function checkPlannedRowsPresent(): void
    {
        $table = ChunkQueue::chunkTable();

        $planned = Db::fetchRow(
            "SELECT SUM(`visit_count`) AS `visits`, SUM(`action_count`) AS `actions`,
                    SUM(`conversion_count`) AS `conversions`, SUM(`item_count`) AS `items`,
                    MIN(`idvisit_start`) AS `visit_from`,
                    MAX(`idvisit_start` + `visit_count` - 1) AS `visit_to`,
                    MIN(`idlink_va_start`) AS `link_from`,
                    MAX(`idlink_va_start` + `action_count` - 1) AS `link_to`
               FROM `$table` WHERE `idrun` = ? AND `phase` = ?",
            [$this->context->getRunId(), ChunkQueue::PHASE_LOAD]
        );

        $checks = [
            ['log_visit', 'idvisit', $planned['visit_from'], $planned['visit_to'], (int) $planned['visits'], 0.0],
            ['log_link_visit_action', 'idlink_va', $planned['link_from'], $planned['link_to'], (int) $planned['actions'], 0.0],
            ['log_conversion', 'idvisit', $planned['visit_from'], $planned['visit_to'], (int) $planned['conversions'], 0.0],
            // Items are deduplicated on (idvisit, idorder, idaction_sku): the same product twice
            // in one basket becomes one row, so the plan is a small over-estimate by design.
            ['log_conversion_item', 'idvisit', $planned['visit_from'], $planned['visit_to'], (int) $planned['items'], 0.05],
        ];

        foreach ($checks as [$name, $column, $from, $to, $expected, $tolerance]) {
            $prefixed = $this->table($name);
            $actual = (int) Db::fetchOne(
                "SELECT COUNT(*) FROM `$prefixed` WHERE `$column` BETWEEN ? AND ?",
                [(int) $from, (int) $to]
            );

            $delta = $expected > 0 ? abs($actual - $expected) / $expected : 0.0;

            $this->record(
                sprintf('%s rows match the plan', $name),
                $delta <= $tolerance,
                sprintf('%s present, %s planned', number_format($actual), number_format($expected))
            );
        }
    }

    /**
     * Rows were written with explicit primary keys, which does bump the counter - but if it were
     * ever left behind, the next real tracking request would collide with the corpus.
     */
    private function checkAutoIncrementAboveCorpus(): void
    {
        // information_schema.tables caches AUTO_INCREMENT for information_schema_stats_expiry
        // seconds, which defaults to a day on MySQL 8. Without this the check reads whatever the
        // counter was before the corpus was written and reports a failure that is not real.
        try {
            Db::exec('SET SESSION information_schema_stats_expiry = 0');
        } catch (\Exception $e) {
            // MariaDB has no such variable and does not cache these values either.
        }

        foreach (['log_visit' => 'idvisit', 'log_link_visit_action' => 'idlink_va', 'log_action' => 'idaction'] as $name => $column) {
            $prefixed = $this->table($name);

            $max = (int) Db::fetchOne("SELECT IFNULL(MAX(`$column`), 0) FROM `$prefixed`");
            $next = (int) Db::fetchOne(
                'SELECT IFNULL(AUTO_INCREMENT, 0) FROM information_schema.tables
                  WHERE table_schema = DATABASE() AND table_name = ?',
                [$prefixed]
            );

            $this->record(
                sprintf('%s auto_increment is past the corpus', $name),
                $next > $max,
                sprintf('auto_increment %s, max %s', number_format($next), number_format($max))
            );
        }
    }

    // ------------------------------------------------------------------ full

    private function checkVisitInvariants(): void
    {
        $visits = $this->table('log_visit');
        $actions = $this->table('log_link_visit_action');

        $wrongCount = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM (
                SELECT `v`.`idvisit`
                  FROM `$visits` `v` JOIN `$actions` `a` ON `a`.`idvisit` = `v`.`idvisit`
                 WHERE {$this->visitScope('v')}
                 GROUP BY `v`.`idvisit`, `v`.`visit_total_actions`
                HAVING COUNT(*) <> `v`.`visit_total_actions`
             ) `z`"
        );

        $this->record(
            'visit_total_actions equals the visit\'s action rows',
            0 === $wrongCount,
            sprintf('%d visits disagree', $wrongCount)
        );

        $badBracket = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$visits` `v`
               JOIN (SELECT `idvisit`, MIN(`server_time`) `mn`, MAX(`server_time`) `mx`
                       FROM `$actions` WHERE {$this->actionScope()} GROUP BY `idvisit`) `a`
                 ON `a`.`idvisit` = `v`.`idvisit`
              WHERE {$this->visitScope('v')}
                AND (`v`.`visit_first_action_time` <> `a`.`mn` OR `v`.`visit_last_action_time` <> `a`.`mx`)"
        );

        $this->record(
            'visit times bracket their actions exactly',
            0 === $badBracket,
            sprintf('%d visits disagree', $badBracket)
        );

        $crossing = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$visits`
              WHERE {$this->visitScope()}
                AND DATE(`visit_first_action_time`) <> DATE(`visit_last_action_time`)"
        );

        $this->record(
            'no visit crosses midnight',
            0 === $crossing,
            sprintf('%d visits cross a day boundary', $crossing)
        );
    }

    private function checkNoOrphanActions(): void
    {
        $actions = $this->table('log_link_visit_action');
        $dictionary = $this->table('log_action');

        // Sampled: the full anti-join over a billion rows is a different kind of query from the
        // one this check is meant to be.
        $orphans = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM (
                SELECT `a`.`idaction_url` FROM `$actions` `a`
                 WHERE {$this->actionScope('a')} LIMIT 100000
             ) `s`
             LEFT JOIN `$dictionary` `d` ON `d`.`idaction` = `s`.`idaction_url`
             WHERE `s`.`idaction_url` IS NOT NULL AND `d`.`idaction` IS NULL"
        );

        $this->record(
            'no orphaned idaction references (100k sample)',
            0 === $orphans,
            sprintf('%d orphans', $orphans)
        );
    }

    /**
     * A conversion whose goal does not exist for its site archives into nothing, silently. The
     * churn test found this the hard way: goals were created on site 1 only, while conversions
     * were spread over all five sites.
     */
    private function checkConversionsReferenceRealGoals(): void
    {
        $conversions = $this->table('log_conversion');
        $goals = $this->table('goal');

        $orphans = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$conversions` `c`
               LEFT JOIN `$goals` `g` ON `g`.`idsite` = `c`.`idsite` AND `g`.`idgoal` = `c`.`idgoal`
              WHERE {$this->visitScope('c')} AND `c`.`idgoal` > 0 AND `g`.`idgoal` IS NULL"
        );

        $this->record(
            'every conversion points at a goal that exists for its site',
            0 === $orphans,
            sprintf('%d conversions reference a missing goal', $orphans)
        );

        // Ecommerce orders on a site without ecommerce enabled are equally invisible.
        $sites = $this->table('site');
        $badOrders = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$conversions` `c`
               JOIN `$sites` `s` ON `s`.`idsite` = `c`.`idsite`
              WHERE {$this->visitScope('c')} AND `c`.`idgoal` = 0 AND `s`.`ecommerce` = 0"
        );

        $this->record(
            'ecommerce orders only exist on sites with ecommerce enabled',
            0 === $badOrders,
            sprintf('%d orders on non-ecommerce sites', $badOrders)
        );
    }

    private function checkVisitorHistory(): void
    {
        $visits = $this->table('log_visit');

        // Per visitor, visit_count_visits must strictly increase with time; a visitor whose
        // history is not monotonic would make every returning-visitor metric wrong.
        $broken = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM (
                SELECT `idvisitor`, `visitor_count_visits`,
                       LAG(`visitor_count_visits`) OVER (PARTITION BY `idvisitor` ORDER BY `visit_first_action_time`) AS `prev`
                  FROM `$visits`
                 WHERE {$this->visitScope()} AND `idvisitor` IN (
                     -- The extra derived table is not redundant: MySQL rejects LIMIT directly
                     -- inside IN (...).
                     SELECT `s`.`idvisitor` FROM (
                         SELECT `idvisitor` FROM `$visits`
                          WHERE {$this->visitScope()} AND `visitor_count_visits` > 3 LIMIT 2000
                     ) `s`
                 )
             ) `z` WHERE `prev` IS NOT NULL AND `visitor_count_visits` <= `prev`"
        );

        $this->record(
            'visitor_count_visits increases per visitor (2k returning sample)',
            0 === $broken,
            sprintf('%d non-monotonic steps', $broken)
        );
    }

    private function checkDistributions(): void
    {
        $visits = $this->table('log_visit');

        $stats = Db::fetchRow(
            "SELECT COUNT(*) `visits`,
                    AVG(`visit_total_actions`) `mean_actions`,
                    SUM(CASE WHEN `visit_total_actions` = 1 THEN 1 ELSE 0 END) / COUNT(*) `bounce`,
                    SUM(CASE WHEN `visitor_returning` = 1 THEN 1 ELSE 0 END) / COUNT(*) `returning`,
                    COUNT(DISTINCT `idvisitor`) / COUNT(*) `distinct_ratio`
               FROM `$visits` WHERE {$this->visitScope()}"
        );

        $this->record(
            'bounce rate 42-48%',
            $stats['bounce'] >= 0.42 && $stats['bounce'] <= 0.48,
            sprintf('%.1f%%', $stats['bounce'] * 100)
        );

        $this->record(
            'mean actions per visit 4.8-6.2',
            $stats['mean_actions'] >= 4.8 && $stats['mean_actions'] <= 6.2,
            sprintf('%.2f', $stats['mean_actions'])
        );

        // The returning share is censored by the corpus window: a 3-day corpus cannot show many
        // return visits when the median gap between them is four days.
        $minReturning = $this->profile->getDayCount() >= 90 ? 0.25 : 0.0;

        $this->record(
            'returning visits above the window-adjusted floor',
            $stats['returning'] >= $minReturning,
            sprintf('%.1f%% (floor %.0f%% for a %d-day corpus)', $stats['returning'] * 100, $minReturning * 100, $this->profile->getDayCount())
        );

        // Like the returning share, this is a lifetime figure. Over three days nearly every
        // visitor is seen once, so the band only means something on a corpus long enough for
        // repeat visits to land inside it.
        $ratioFloor = $this->profile->getDayCount() >= 90 ? 0.55 : 0.0;

        $this->record(
            'distinct visitors within the window-adjusted band',
            $stats['distinct_ratio'] >= $ratioFloor && $stats['distinct_ratio'] <= 1.0,
            sprintf(
                '%.1f%% (band %.0f-100%% for a %d-day corpus)',
                $stats['distinct_ratio'] * 100,
                $ratioFloor * 100,
                $this->profile->getDayCount()
            )
        );
    }

    /**
     * The check an earlier generator would have failed: it produced uniqueness without a hot
     * head, so every page was viewed about twice and Transitions had nothing to work with.
     */
    private function checkUrlPopularity(): void
    {
        $actions = $this->table('log_link_visit_action');

        $totalPageviews = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$actions`
              WHERE {$this->actionScope()} AND `idaction_url` IS NOT NULL AND `pageview_position` IS NOT NULL"
        );

        if ($totalPageviews < 1000) {
            $this->record('top URLs carry a real share of pageviews', true, 'corpus too small to judge');

            return;
        }

        $top = Db::fetchAll(
            "SELECT COUNT(*) `c` FROM `$actions`
              WHERE {$this->actionScope()} AND `idaction_url` IS NOT NULL AND `pageview_position` IS NOT NULL
              GROUP BY `idaction_url` ORDER BY `c` DESC LIMIT 100"
        );

        $topShare = array_sum(array_column($top, 'c')) / $totalPageviews;

        $this->record(
            'top 100 URLs take at least 10% of pageviews',
            $topShare >= 0.10,
            sprintf('%.1f%%', $topShare * 100)
        );

        // Counted by id range, not by "viewed exactly once". A hot-pool URL can easily pick up
        // exactly one view by chance on a small corpus - 30k of them did on the smoke profile -
        // so single-view counting measures corpus size as much as it measures the tail.
        $dictionaryTable = $this->table('log_action');
        $firstTailId = $this->context->buildDictionary()->getFirstTailId();

        // Bounded above as well as below: a churn run creates its own log_action rows beyond
        // the corpus's allocation, and counting those would make the tail look bigger than it is.
        $tailUrls = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$dictionaryTable` WHERE `idaction` BETWEEN ? AND ?",
            [$firstTailId, $this->bounds()['tailTo']]
        );

        $expectedTail = $totalPageviews * $this->profile->getUniqueUrlShare();

        $this->record(
            'the unique-URL tail is the size it was configured to be',
            $expectedTail < 1 || abs($tailUrls - $expectedTail) / max(1, $expectedTail) <= 0.20,
            sprintf(
                '%s unique URLs minted, %s expected (%.1f%% of %s pageviews)',
                number_format($tailUrls),
                number_format((int) $expectedTail),
                $this->profile->getUniqueUrlShare() * 100,
                number_format($totalPageviews)
            )
        );

        $tailViews = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$actions`
              WHERE {$this->actionScope()} AND `idaction_url` BETWEEN ? AND ?",
            [$firstTailId, $this->bounds()['tailTo']]
        );

        $this->record(
            'unique-tail URLs really are viewed once each',
            $tailUrls < 100 || abs($tailViews - $tailUrls) / max(1, $tailUrls) <= 0.02,
            sprintf('%s views over %s URLs', number_format($tailViews), number_format($tailUrls))
        );
    }

    private function checkEveryDayPopulated(): void
    {
        $visits = $this->table('log_visit');

        $days = Db::fetchAll(
            "SELECT DATE(`visit_first_action_time`) `d`, COUNT(*) `c` FROM `$visits`
              WHERE {$this->visitScope()} GROUP BY `d` ORDER BY `d`"
        );

        $counts = array_map('intval', array_column($days, 'c'));
        $expected = $this->profile->getDayCount();

        $this->record(
            'every day of the corpus has visits',
            count($days) === $expected,
            sprintf('%d of %d days populated', count($days), $expected)
        );

        if (count($counts) > 7) {
            $ratio = max($counts) / max(1, min($counts));
            $this->record(
                'day-to-day volume varies (weekends and spikes visible)',
                $ratio >= 1.15,
                sprintf('peak:trough %.1fx', $ratio)
            );
        }
    }

    /**
     * Visits per visitor must strictly increase as the window widens. This is a structural truth,
     * not a calibration target - if it fails, the returning-visitor model is broken.
     */
    private function checkUniqueVisitorWindows(): void
    {
        $visits = $this->table('log_visit');

        $end = $this->profile->getEndDate();
        $windows = [
            'day' => date('Y-m-d', strtotime($end . ' -1 day')),
            'week' => date('Y-m-d', strtotime($end . ' -7 day')),
            'month' => date('Y-m-d', strtotime($end . ' -30 day')),
        ];

        $ratios = [];

        foreach ($windows as $label => $from) {
            $row = Db::fetchRow(
                "SELECT COUNT(*) `v`, COUNT(DISTINCT `idvisitor`) `u` FROM `$visits`
                  WHERE {$this->visitScope()} AND `visit_first_action_time` >= ?",
                [$from . ' 00:00:00']
            );

            $ratios[$label] = $row['u'] > 0 ? $row['v'] / $row['u'] : 0;
        }

        $monotonic = $ratios['day'] <= $ratios['week'] && $ratios['week'] <= $ratios['month'];

        $this->record(
            'visits per visitor increases with the window',
            $monotonic,
            sprintf('day %.3f, week %.3f, month %.3f', $ratios['day'], $ratios['week'], $ratios['month'])
        );
    }

    /**
     * The columns the old generator left empty. An empty dimension does not fail anything - it
     * just quietly removes its GROUP BY cost from every archiving query.
     */
    private function checkColumnsPopulated(): void
    {
        $visits = $this->table('log_visit');
        $actions = $this->table('log_link_visit_action');

        $required = [
            'config_browser_name', 'config_browser_engine', 'config_os', 'config_device_type',
            'location_country', 'location_city', 'location_region', 'location_latitude',
            'referer_type', 'custom_dimension_1', 'custom_dimension_4', 'config_resolution',
            'location_browser_lang', 'visit_entry_idaction_url', 'visit_exit_idaction_url',
        ];

        $selects = [];
        foreach ($required as $column) {
            // CAST to CHAR first: MySQL compares `numeric_column = ''` numerically, so a
            // perfectly good config_device_type of 0 (desktop) would count as empty.
            $selects[] = sprintf(
                "SUM(CASE WHEN `%s` IS NULL OR CAST(`%s` AS CHAR) = '' THEN 1 ELSE 0 END) AS `%s`",
                $column,
                $column,
                $column
            );
        }

        $row = Db::fetchRow(
            'SELECT COUNT(*) AS `total`, ' . implode(', ', $selects) . " FROM `$visits` WHERE {$this->visitScope()}"
        );

        $empty = [];
        foreach ($required as $column) {
            // custom_dimension_5 is deliberately sparse; everything listed here should be filled
            // on essentially every row.
            if ((int) $row[$column] > (int) $row['total'] * 0.05) {
                $empty[] = $column;
            }
        }

        $this->record(
            'every segmentable log_visit column is populated',
            empty($empty),
            empty($empty) ? 'all filled' : 'mostly empty: ' . implode(', ', $empty)
        );

        $refs = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$actions`
              WHERE {$this->actionScope()}
                AND `pageview_position` > 1 AND (`idaction_url_ref` IS NULL OR `idaction_url_ref` = 0)"
        );

        $selfRef = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$actions` WHERE {$this->actionScope()} AND `idaction_url_ref` = `idaction_url`"
        );

        $totalActions = max(1, (int) Db::fetchOne("SELECT COUNT(*) FROM `$actions` WHERE {$this->actionScope()}"));

        $this->record(
            'navigation references point at the previous page, not the current one',
            $selfRef / $totalActions < 0.05,
            sprintf('%d self-referential rows, %d later pageviews with no referrer', $selfRef, $refs)
        );

        $timings = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$actions`
              WHERE {$this->actionScope()} AND `pageview_position` IS NOT NULL AND `time_server` IS NOT NULL"
        );
        $pageviews = max(1, (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$actions` WHERE {$this->actionScope()} AND `pageview_position` IS NOT NULL"
        ));

        $this->record(
            'pageviews carry PagePerformance timings',
            $timings / $pageviews > 0.85,
            sprintf('%.1f%% of pageviews timed', 100 * $timings / $pageviews)
        );
    }

    private function checkMegaVisits(): void
    {
        $visits = $this->table('log_visit');

        $mega = (int) Db::fetchOne(
            "SELECT COUNT(*) FROM `$visits` WHERE {$this->visitScope()} AND `visit_total_actions` >= ?",
            [Profile::MEGA_VISIT_MIN_ACTIONS]
        );

        $expected = $this->profile->getMegaVisitCount();

        $this->record(
            'the Visits Log pathology is present',
            $mega >= (int) floor($expected * 0.5),
            sprintf('%d visits with %d+ actions, %d planned', $mega, Profile::MEGA_VISIT_MIN_ACTIONS, $expected)
        );
    }
}
