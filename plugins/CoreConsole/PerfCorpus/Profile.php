<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

/**
 * Every distribution constant the performance corpus uses, in one place, each tagged with the
 * slow path it exists to stress:
 *
 *   B1  COUNT(DISTINCT idvisitor), which gets slow enough at week and month level to be disabled
 *   B2  the unsegmented archive blocking every segment archive queued behind it
 *   B3  archiving falling behind real time; Pages reports over very large URL sets
 *   B4  Transitions timing out (self-join of log_link_visit_action on idaction)
 *   B5  the visits log on a session with thousands of actions
 *   B6  retention purge (DELETE ... LIMIT 25000 loops) causing CPU spikes
 *   B7  sizing a change-data-capture pipeline needs realistic peak write rates
 *
 * A profile fixes the size; the constants fix the shape. Volumes are anchored to tracked
 * actions ("hits") in the final 30 days.
 */
class Profile
{
    // ---------------------------------------------------------------- traffic shape over time (B7)

    /** Saturday and Sunday run lighter than weekdays. */
    public const WEEKEND_FACTOR = 0.8;

    /** Campaign/news spike days, so peak-hour and peak-day write rates fall out of the data. */
    public const SPIKE_DAYS_PER_YEAR = 3;
    public const SPIKE_FACTOR = 2.75;

    /** Relative visit volume per hour of the day, midnight first. Night trough ~20% of midday. */
    public const DIURNAL_WEIGHTS = [
        0.22, 0.16, 0.12, 0.10, 0.10, 0.14, 0.26, 0.48,
        0.72, 0.90, 0.98, 1.00, 0.97, 0.95, 0.96, 0.94,
        0.90, 0.86, 0.82, 0.78, 0.70, 0.58, 0.42, 0.30,
    ];

    /** Earliest month runs at this share of the final month; real sites grow into their volume. */
    public const DEFAULT_RAMP_START = 0.30;

    // ---------------------------------------------------------------- visitor model (B1)

    /**
     * Lifetime visit count per visitor: 65% visit exactly once, the rest follow a bounded
     * Pareto. The exponent is calibrated (not guessed) so that E[k] = 1.61, which puts 38% of
     * all visits on returning visitors - the target band is 35-40% - while still producing
     * ~0.02% of visitors with more than 50 visits, the regulars that create week/month
     * unique-visitor overlap.
     */
    public const ONE_TIME_VISITOR_SHARE = 0.65;
    public const VISIT_COUNT_PARETO_EXPONENT = 2.9;
    public const MAX_VISITS_PER_VISITOR = 400;

    /** Gap between consecutive visits by the same visitor. Lognormal: median 4 days, long tail. */
    public const INTER_VISIT_GAP_MEDIAN_DAYS = 4.0;
    public const INTER_VISIT_GAP_SIGMA = 1.2;

    /**
     * Share of return visits that happen later the same day rather than days later. Without this
     * a visitor could never have two visits on one day, and daily unique visitors would be
     * exactly equal to daily visits - which is both unrealistic and makes the day-level
     * COUNT(DISTINCT idvisitor) trivially cheap.
     */
    public const SAME_DAY_RETURN_SHARE = 0.25;
    public const SAME_DAY_RETURN_MIN_SECONDS = 1800;
    public const SAME_DAY_RETURN_MAX_SECONDS = 21600;

    /** Share of visitors carrying a stable user_id, so userId segments have something to match. */
    public const USER_ID_SHARE = 0.15;

    // ---------------------------------------------------------------- visit content (B3, B5)

    /**
     * Actions per visit: 45% single-action bounces, lognormal tail elsewhere, capped at 1000.
     * Calibrated so the overall mean is 5.2 actions per visit.
     */
    public const BOUNCE_SHARE = 0.45;
    public const ACTIONS_LOGNORMAL_MEDIAN = 4.6;
    public const ACTIONS_LOGNORMAL_SIGMA = 1.02;
    public const MAX_ACTIONS_PER_VISIT = 1000;
    public const MEAN_ACTIONS_PER_VISIT = 5.2;

    /**
     * The visits-log pathology: a handful of sessions per month with thousands of actions.
     *
     * 50 a month is the figure for a high-traffic site, and at p200 it is a rounding error - 700
     * visits out of 366M. On a small profile it is not: 200 mega visits among 233k would carry
     * 45% of all actions and wreck every other distribution. So it scales with the traffic level,
     * with a floor so the code path is still exercised on the smoke profile.
     */
    public const MEGA_VISITS_PER_MONTH = 50;
    public const MEGA_VISITS_FLOOR = 2;
    public const MEGA_VISIT_MIN_ACTIONS = 2000;
    public const MEGA_VISIT_MAX_ACTIONS = 8000;

    /** Action type mix, cumulative: pageview, event, site search, download, outlink. */
    public const ACTION_TYPE_CDF = [0.80, 0.95, 0.98, 0.99, 1.00];
    public const ACTION_TYPE_PAGEVIEW_SHARE = 0.80;

    // ---------------------------------------------------------------- url popularity (B3, B4)

    /**
     * Hot pool: pages that get revisited, drawn Zipf so Transitions and the Pages tree have
     * genuinely hot pages. At exponent 1.0 over a 5M pool the top 100 URLs take ~30% of hot-pool
     * pageviews, which is what a real content site looks like.
     */
    public const ZIPF_EXPONENT = 1.0;
    public const HOT_POOL_MAX = 5000000;
    public const HOT_POOL_ACTIONS_PER_URL = 40;

    /**
     * Share of pageviews landing on an effectively unique URL (an id in the path or query
     * string). This is what makes log_action grow without bound on real sites, and it is
     * the single biggest dial on corpus cost, so it is a run-time flag.
     *
     * Note the earlier spec quoted "15% of pageviews => log_action ~46M at p200". That figure
     * priced the tail off the final month only. Unique URLs never repeat, so over the whole
     * 14-month corpus a 15% tail is ~216M rows, not 35M.
     *
     * 4% is a placeholder until it is measured, not a judgement. The right number is whatever a
     * real high-traffic site shows, and that is measurable rather than arguable: log_action rows
     * divided by pageviews over the same retention window. Set it to whatever that measurement
     * gives, even if the answer is inconvenient - a corpus tuned to suit a storage engine cannot
     * say anything useful about that engine.
     */
    public const DEFAULT_UNIQUE_URL_SHARE = 0.04;

    /** Unique-tail pageviews reuse a small set of page titles ("Order confirmation", ...). */
    public const TAIL_TITLE_TEMPLATES = 10;

    // ---------------------------------------------------------------- conversions (B2)

    public const GOAL_COUNT = 10;
    public const GOAL_CONVERSION_SHARE = 0.04;
    public const ECOMMERCE_ORDER_SHARE = 0.008;
    /** Basket size, cumulative over 1..8 items. Mean 2.77, which is a realistic basket. */
    public const ECOMMERCE_ITEMS_CDF = [0.30, 0.55, 0.72, 0.83, 0.90, 0.95, 0.98, 1.00];
    public const ECOMMERCE_MEAN_ITEMS = 2.77;
    public const ECOMMERCE_SKU_POOL = 30000;

    // ---------------------------------------------------------------- sites

    /**
     * City pool size. 2k gives location_city production-like cardinality; the 20k the earlier
     * spec asked for is authoring effort for no measurable difference in report size.
     */
    public const CITY_POOL_SIZE = 2000;

    /** Site 1 carries most of the traffic; the rest exist so idsite is a real filter. */
    public const SITE_1_SHARE = 0.92;
    public const DEFAULT_SITES = 5;

    // ---------------------------------------------------------------- work partitioning

    /**
     * Chunks are (day, shard). 64 shards puts a p200 final-month chunk at ~20k visits /
     * ~105k rows: small enough that a crash costs seconds, large enough that per-chunk
     * overhead is noise.
     */
    public const DEFAULT_SHARDS = 64;

    // ---------------------------------------------------------------- storage estimates

    /**
     * Bytes per row including indexes. An earlier generator measured log_link_visit_action at
     * 166 B and log_visit at 318 B; +25% covers the columns it left empty and this one fills.
     */
    private const BYTES_PER_ROW = [
        'log_visit' => 398,
        'log_link_visit_action' => 208,
        'log_action' => 180,
        'log_conversion' => 300,
        'log_conversion_item' => 120,
    ];

    /** One fixed-width plan record per visit on local disk. */
    public const SPOOL_BYTES_PER_VISIT = 24;

    /**
     * Rows per second the load phase is assumed to sustain across all workers, used only for the
     * --dry-run estimate. An earlier generator managed ~11k/s with one INSERT per row; this
     * design uses LOAD DATA LOCAL INFILE across N workers. Measure and correct on the first run.
     */
    public const ASSUMED_LOAD_ROWS_PER_SECOND = 150000;

    /**
     * Visits per second per worker for the plan phase, which only makes shape draws.
     *
     * Measured at 20-50k in ddev on a laptop, so 40k is the conservative end of that. The earlier
     * value of 400,000 was a guess and made the --dry-run plan estimate about ten times too
     * optimistic. Like the load rate, correct it from the first real run rather than trusting it.
     */
    public const ASSUMED_PLAN_VISITS_PER_SECOND = 40000;

    /**
     * The built-in profiles: days of history, and tracked actions in the final 30 days.
     */
    private const PROFILES = [
        // Minutes in ddev. The loop used for every development step.
        'smoke' => ['days' => 3, 'finalMonthActions' => 4500000, 'rampStart' => 1.0],
        // ~24M actions. First profile big enough to measure a real rows/s figure.
        'small' => ['days' => 90, 'finalMonthActions' => 10000000, 'rampStart' => 0.60],
        // 200M hits in the final month, over 14 months of history.
        'p200' => ['days' => 420, 'finalMonthActions' => 200000000, 'rampStart' => self::DEFAULT_RAMP_START],
        // Two and a half times p200, for measuring how cost scales with volume.
        'p500' => ['days' => 420, 'finalMonthActions' => 500000000, 'rampStart' => self::DEFAULT_RAMP_START],
    ];

    private string $name;
    private int $seed;
    private int $days;
    private int $finalMonthActions;
    private float $rampStart;
    private string $endDate;
    private int $sites;
    private float $uniqueUrlShare;
    private int $shards;

    /** @var int[]|null day index => planned visits */
    private ?array $dailyVisits = null;

    /** @var int[]|null */
    private ?array $spikeDays = null;

    private function __construct(array $config)
    {
        $this->name = $config['name'];
        $this->seed = $config['seed'];
        $this->days = $config['days'];
        $this->finalMonthActions = $config['finalMonthActions'];
        $this->rampStart = $config['rampStart'];
        $this->endDate = $config['endDate'];
        $this->sites = $config['sites'];
        $this->uniqueUrlShare = $config['uniqueUrlShare'];
        $this->shards = $config['shards'];
    }

    /**
     * @param array $overrides endDate, sites, uniqueUrlShare, shards, days, finalMonthActions
     */
    public static function make(string $name, int $seed, array $overrides = []): self
    {
        if (!isset(self::PROFILES[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown profile "%s". Available: %s',
                $name,
                implode(', ', self::getNames())
            ));
        }

        $config = self::PROFILES[$name];
        $config['name'] = $name;
        $config['seed'] = $seed;
        $config['endDate'] = $overrides['endDate'] ?? date('Y-m-d', strtotime('yesterday'));
        $config['sites'] = (int) ($overrides['sites'] ?? self::DEFAULT_SITES);
        $config['uniqueUrlShare'] = (float) ($overrides['uniqueUrlShare'] ?? self::DEFAULT_UNIQUE_URL_SHARE);
        $config['shards'] = (int) ($overrides['shards'] ?? self::DEFAULT_SHARDS);

        if (!empty($overrides['days'])) {
            $config['days'] = (int) $overrides['days'];
        }
        if (!empty($overrides['finalMonthActions'])) {
            $config['finalMonthActions'] = (int) $overrides['finalMonthActions'];
        }

        self::validate($config);

        return new self($config);
    }

    private static function validate(array $config): void
    {
        if ($config['days'] < 1) {
            throw new \InvalidArgumentException('--days must be at least 1.');
        }
        if ($config['finalMonthActions'] < 1000) {
            throw new \InvalidArgumentException('--final-month-hits must be at least 1000.');
        }
        if ($config['sites'] < 1 || $config['sites'] > 1000) {
            throw new \InvalidArgumentException('--sites must be between 1 and 1000.');
        }
        if ($config['uniqueUrlShare'] < 0.0 || $config['uniqueUrlShare'] > 1.0) {
            throw new \InvalidArgumentException('--unique-url-share must be between 0 and 1.');
        }
        if ($config['shards'] < 1 || $config['shards'] > 4096) {
            throw new \InvalidArgumentException('--shards must be between 1 and 4096.');
        }
        if (false === strtotime($config['endDate'])) {
            throw new \InvalidArgumentException('--end-date is not a valid date.');
        }
    }

    /**
     * Rebuilds a profile from what was stored on the run row, so status, verify and the workers
     * all see exactly the configuration the corpus was planned with.
     */
    public static function fromArray(array $config): self
    {
        return self::make($config['profile'], (int) $config['seed'], [
            'endDate' => $config['endDate'],
            'sites' => $config['sites'],
            'uniqueUrlShare' => $config['uniqueUrlShare'],
            'shards' => $config['shards'],
            'days' => $config['days'],
            'finalMonthActions' => $config['finalMonthActions'],
        ]);
    }

    /**
     * The diurnal curve as a cumulative distribution over the 24 hours, so a start hour is one
     * draw. Computed rather than hard-coded so DIURNAL_WEIGHTS stays the single place to edit.
     */
    public static function getDiurnalCdf(): array
    {
        static $cdf = null;

        if (null !== $cdf) {
            return $cdf;
        }

        $total = array_sum(self::DIURNAL_WEIGHTS);
        $running = 0.0;
        $cdf = [];

        foreach (self::DIURNAL_WEIGHTS as $weight) {
            $running += $weight / $total;
            $cdf[] = $running;
        }

        $cdf[count($cdf) - 1] = 1.0;

        return $cdf;
    }

    public static function getNames(): array
    {
        return array_keys(self::PROFILES);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSeed(): int
    {
        return $this->seed;
    }

    public function getDayCount(): int
    {
        return $this->days;
    }

    public function getShardCount(): int
    {
        return $this->shards;
    }

    public function getSiteCount(): int
    {
        return $this->sites;
    }

    public function getUniqueUrlShare(): float
    {
        return $this->uniqueUrlShare;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function getStartDate(): string
    {
        return $this->getDateForDay(0);
    }

    public function getChunkCount(): int
    {
        return $this->days * $this->shards;
    }

    /**
     * Day 0 is the oldest day in the corpus; day (days - 1) is --end-date.
     */
    public function getDateForDay(int $dayIndex): string
    {
        return date('Y-m-d', strtotime($this->endDate . ' -' . ($this->days - 1 - $dayIndex) . ' day'));
    }

    /**
     * Day indices carrying a traffic spike, drawn from the seed so they are part of the corpus
     * identity rather than an accident of when it was generated.
     *
     * @return int[]
     */
    public function getSpikeDays(): array
    {
        if (null !== $this->spikeDays) {
            return $this->spikeDays;
        }

        $wanted = (int) floor($this->days * self::SPIKE_DAYS_PER_YEAR / 365);
        $rng = Rng::forStream($this->seed, Rng::S_SPIKE_DAYS);
        $days = [];

        // Bounded: a duplicate draw just means one fewer spike, which is fine.
        for ($i = 0; $i < $wanted; $i++) {
            $days[$rng->nextInt(0, $this->days - 1)] = true;
        }

        $this->spikeDays = array_keys($days);
        sort($this->spikeDays);

        return $this->spikeDays;
    }

    /**
     * Planned visits per day index. Visits, not actions, are the authoritative planned quantity:
     * action counts are drawn per visit and only converge on the target mean.
     *
     * @return int[]
     */
    public function getDailyVisits(): array
    {
        if (null !== $this->dailyVisits) {
            return $this->dailyVisits;
        }

        $spikes = array_fill_keys($this->getSpikeDays(), true);
        $shape = [];

        for ($d = 0; $d < $this->days; $d++) {
            $ramp = $this->days > 1
                ? $this->rampStart + (1.0 - $this->rampStart) * ($d / ($this->days - 1))
                : 1.0;

            $dayOfWeek = (int) date('N', strtotime($this->getDateForDay($d)));
            $weekday = $dayOfWeek >= 6 ? self::WEEKEND_FACTOR : 1.0;
            $spike = isset($spikes[$d]) ? self::SPIKE_FACTOR : 1.0;

            $shape[$d] = $ramp * $weekday * $spike;
        }

        // Scale so the trailing window really does carry --final-month-hits actions.
        $window = min(30, $this->days);
        $windowShape = array_sum(array_slice($shape, $this->days - $window, $window));
        $windowActions = $this->finalMonthActions * $window / 30.0;
        $actionsPerShapeUnit = $windowActions / $windowShape;

        $visits = [];
        for ($d = 0; $d < $this->days; $d++) {
            $visits[$d] = max(1, (int) round($shape[$d] * $actionsPerShapeUnit / self::MEAN_ACTIONS_PER_VISIT));
        }

        $this->dailyVisits = $visits;

        return $visits;
    }

    /**
     * Visits for one (day, shard) chunk. Split by remainder so the shards sum exactly to the day.
     */
    public function getVisitsForChunk(int $dayIndex, int $shard): int
    {
        $dayVisits = $this->getDailyVisits()[$dayIndex];
        $base = intdiv($dayVisits, $this->shards);
        $remainder = $dayVisits % $this->shards;

        return $base + ($shard < $remainder ? 1 : 0);
    }

    public function getTotalVisits(): int
    {
        return array_sum($this->getDailyVisits());
    }

    /**
     * Tracking requests per second averaged over the whole corpus period.
     *
     * Useful as a sanity check, and useless as a load target: real traffic is concentrated into
     * working hours, so a test run at the average never reaches the rate that actually decides
     * whether the write path keeps up.
     */
    public function getAverageActionsPerSecond(): float
    {
        return $this->finalMonthActions / (30 * 86400);
    }

    /**
     * Tracking requests per second in the busiest hour of a normal weekday - the number a live
     * write test should actually be run at.
     *
     * Derived from DIURNAL_WEIGHTS rather than guessed: the busiest hour takes about 7% of a
     * day's traffic against the 4.2% a flat day would give, so peak is roughly 1.7x average.
     * A campaign spike day multiplies it again.
     */
    public function getPeakActionsPerSecond(bool $onSpikeDay = false): float
    {
        $peakHourShare = max(self::DIURNAL_WEIGHTS) / array_sum(self::DIURNAL_WEIGHTS);
        $perDay = $this->finalMonthActions / 30;
        $peak = $perDay * $peakHourShare / 3600;

        return $onSpikeDay ? $peak * self::SPIKE_FACTOR : $peak;
    }

    public function getPeakVisitsPerSecond(bool $onSpikeDay = false): float
    {
        return $this->getPeakActionsPerSecond($onSpikeDay) / self::MEAN_ACTIONS_PER_VISIT;
    }

    /** Number of mega-visits (B5) planned across the whole corpus. */
    public function getMegaVisitCount(): int
    {
        $scale = min(1.0, $this->finalMonthActions / 200000000);
        $count = (int) round($this->days / 30.0 * self::MEGA_VISITS_PER_MONTH * $scale);

        return max(self::MEGA_VISITS_FLOOR, $count);
    }

    /** Size of the hot, revisited URL pool, scaled with the profile. */
    public function getHotPoolSize(): int
    {
        $size = (int) round($this->finalMonthActions / self::HOT_POOL_ACTIONS_PER_URL);

        return max(1000, min(self::HOT_POOL_MAX, $size));
    }

    /**
     * Dictionary pools other than page URLs, scaled relative to p200 so the smoke profile is not
     * dominated by a fixed 500k-name event dictionary.
     *
     * @return array<string,int>
     */
    public function getDictionaryPools(): array
    {
        $scale = min(1.0, $this->finalMonthActions / 200000000);

        return [
            'searchKeywords' => max(200, (int) round(300000 * $scale)),
            'eventCategories' => 200,
            'eventActions' => 2000,
            'eventNames' => max(200, (int) round(500000 * $scale)),
            'outlinks' => max(100, (int) round(50000 * $scale)),
            'downloads' => max(100, (int) round(50000 * $scale)),
            'ecommerceSkus' => max(100, (int) round(self::ECOMMERCE_SKU_POOL * $scale)),
        ];
    }

    /**
     * Estimated row counts and bytes per table, plus the derived disk and runtime figures that
     * --dry-run prints. Estimates: the exact counts only exist once the plan phase has run.
     */
    public function estimate(): array
    {
        $visits = $this->getTotalVisits();
        $megaVisits = $this->getMegaVisitCount();
        $megaMeanActions = (self::MEGA_VISIT_MIN_ACTIONS + self::MEGA_VISIT_MAX_ACTIONS) / 2;

        $actions = (int) round(
            ($visits - $megaVisits) * self::MEAN_ACTIONS_PER_VISIT + $megaVisits * $megaMeanActions
        );
        $pageviews = (int) round($actions * self::ACTION_TYPE_PAGEVIEW_SHARE);

        $hotPool = $this->getHotPoolSize();
        $tailUrls = (int) round($pageviews * $this->uniqueUrlShare);
        $pools = $this->getDictionaryPools();

        $logAction = $hotPool                    // type 1, hot page URLs
            + $tailUrls                          // type 1, unique-id URLs
            + $hotPool                           // type 4, one title per hot URL
            + self::TAIL_TITLE_TEMPLATES         // type 4, shared titles for the tail
            + $pools['searchKeywords']           // type 8
            + $pools['eventCategories'] + $pools['eventActions'] + $pools['eventNames']
            + $pools['outlinks'] + $pools['downloads']
            + $pools['ecommerceSkus'] * 2 + 500; // sku + name actions, plus category actions

        $orders = (int) round($visits * self::ECOMMERCE_ORDER_SHARE);
        $conversions = (int) round($visits * self::GOAL_CONVERSION_SHARE) + $orders;
        $items = (int) round($orders * self::ECOMMERCE_MEAN_ITEMS);

        $rows = [
            'log_visit' => $visits,
            'log_link_visit_action' => $actions,
            'log_action' => $logAction,
            'log_conversion' => $conversions,
            'log_conversion_item' => $items,
        ];

        $bytes = [];
        foreach ($rows as $table => $count) {
            $bytes[$table] = $count * self::BYTES_PER_ROW[$table];
        }

        $totalRows = array_sum($rows);

        return [
            'rows' => $rows,
            'bytes' => $bytes,
            'totalRows' => $totalRows,
            'totalBytes' => array_sum($bytes),
            'spoolBytes' => $visits * self::SPOOL_BYTES_PER_VISIT,
            'chunks' => $this->getChunkCount(),
            'megaVisits' => $megaVisits,
            'hotPool' => $hotPool,
            'tailUrls' => $tailUrls,
            'planSeconds' => (int) ceil($visits / self::ASSUMED_PLAN_VISITS_PER_SECOND),
            'loadSeconds' => (int) ceil($totalRows / self::ASSUMED_LOAD_ROWS_PER_SECOND),
        ];
    }

    /**
     * Everything that identifies this corpus, for the run manifest and the chunk table.
     */
    public function toArray(): array
    {
        return [
            'profile' => $this->name,
            'seed' => $this->seed,
            'days' => $this->days,
            'finalMonthActions' => $this->finalMonthActions,
            'rampStart' => $this->rampStart,
            'endDate' => $this->endDate,
            'startDate' => $this->getStartDate(),
            'sites' => $this->sites,
            'uniqueUrlShare' => $this->uniqueUrlShare,
            'shards' => $this->shards,
        ];
    }
}
