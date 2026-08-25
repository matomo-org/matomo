<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\PerfCorpus;

/**
 * Phase 1: decides who visits when, and writes it to the plan spool.
 *
 * The unit of work is a shard of the visitor universe. A visitor belongs to shard
 * (ordinal % shards) for its whole life, so one process owns everything about that visitor and
 * no two processes ever write the same spool file.
 *
 * Days are walked in order. On each day the planner already knows how many visits earlier days
 * scheduled into it - because when a visitor is created, its entire future is scheduled there and
 * then - and mints exactly enough new visitors to reach that day's target. That is what produces
 * a realistic mix of new and returning visitors without any cross-process coordination, and it is
 * what the current VisitorGenerator gets structurally wrong: its returning-visitor lookup uses
 * wall-clock time, so on backfilled data the returning branch never fires at all and every visit
 * looks like a bounce.
 *
 * Only shape is drawn here - how many visits, how many actions, which day, which second. What
 * those actions actually are comes from a separate RNG stream in the load phase, so planning
 * stays cheap and the two phases cannot drift apart.
 *
 * Known limitation: the first days of the corpus have an unrealistically high share of new
 * visitors, because nobody existed before day 0. The ramp means those days are also the
 * smallest, and the archiving and uniques tests target the final months, so this is left alone.
 */
class Planner
{
    private RunContext $context;
    private Profile $profile;

    public function __construct(RunContext $context)
    {
        $this->context = $context;
        $this->profile = $context->getProfile();
    }

    /**
     * Plans one shard end to end and records the exact counts it produced.
     *
     * @param callable|null $onProgress called every few days with (dayIndex, dayCount)
     * @return array{visits: int, actions: int, conversions: int, items: int}
     */
    public function planShard(int $shard, ?callable $onProgress = null): array
    {
        $days = $this->profile->getDayCount();
        $shards = $this->profile->getShardCount();
        $seed = $this->profile->getSeed();
        $siteCount = $this->profile->getSiteCount();
        $diurnalCdf = Profile::getDiurnalCdf();

        $dates = [];
        for ($day = 0; $day < $days; $day++) {
            $dates[$day] = $this->profile->getDateForDay($day);
        }

        $spool = new PlanSpool($this->context->getSpoolDir(), $shard, $dates);
        // A previous attempt may have died halfway; start from nothing rather than appending.
        $spool->discard();

        $scheduled = array_fill(0, $days, 0);
        $perDay = [];
        for ($day = 0; $day < $days; $day++) {
            $perDay[$day] = ['visits' => 0, 'actions' => 0, 'conversions' => 0, 'items' => 0];
        }

        $megaRemaining = $this->megaVisitsPerDay($shard, $days);
        $visitorCounter = 0;

        for ($day = 0; $day < $days; $day++) {
            $target = $this->profile->getVisitsForChunk($day, $shard);

            // Visits earlier days already booked into this one count towards the target, and so
            // do the same-day return visits a brand new visitor brings with it - hence a loop
            // rather than a subtraction.
            $placedToday = $scheduled[$day];

            while ($placedToday < $target) {
                $ordinal = $visitorCounter * $shards + $shard;
                $visitorCounter++;

                $placedToday += $this->planVisitor(
                    $spool,
                    $ordinal,
                    $day,
                    $days,
                    $seed,
                    $siteCount,
                    $diurnalCdf,
                    $megaRemaining,
                    $scheduled,
                    $perDay
                );
            }

            if (null !== $onProgress && 0 === $day % 16) {
                $onProgress($day, $days);
            }
        }

        $spool->finish();

        $this->recordCounts($shard, $dates, $perDay);

        return [
            'visits' => array_sum(array_column($perDay, 'visits')),
            'actions' => array_sum(array_column($perDay, 'actions')),
            'conversions' => array_sum(array_column($perDay, 'conversions')),
            'items' => array_sum(array_column($perDay, 'items')),
            'visitors' => $visitorCounter,
        ];
    }

    /**
     * Draws one visitor's whole life and spools every visit of it that lands inside the corpus.
     *
     * @param int[] $scheduled by reference: visits this visitor books into later days
     * @param array[] $perDay  by reference: the exact per-day counts for this shard
     * @return int how many of this visitor's visits landed on $firstDay - which can be more than
     *             one, because return visits within the same day are allowed
     */
    private function planVisitor(
        PlanSpool $spool,
        int $ordinal,
        int $firstDay,
        int $dayCount,
        int $seed,
        int $siteCount,
        array $diurnalCdf,
        array &$megaRemaining,
        array &$scheduled,
        array &$perDay
    ): int {
        $rng = Rng::forStream($seed, Rng::S_VISITOR_SHAPE, $ordinal);

        $visitCount = $rng->nextBool(Profile::ONE_TIME_VISITOR_SHARE)
            ? 1
            : 1 + $rng->nextParetoInt(
                Profile::VISIT_COUNT_PARETO_EXPONENT,
                1,
                Profile::MAX_VISITS_PER_VISITOR - 1
            );

        // Fixed for life, so segments on site and userId behave the way they do in production.
        $idSite = $siteCount > 1 && !$rng->nextBool(Profile::SITE_1_SHARE)
            ? $rng->nextInt(2, $siteCount)
            : 1;
        $flagsForVisitor = $rng->nextBool(Profile::USER_ID_SHARE) ? PlanSpool::FLAG_HAS_USER_ID : 0;

        // Visits are placed on an absolute second-of-corpus timeline, so a return visit can land
        // later the same day and simply roll into the next day if it runs past midnight.
        $hour = $rng->pickFromCdf($diurnalCdf);
        $timestamp = $firstDay * 86400 + $hour * 3600 + $rng->nextInt(0, 3599);

        $firstTimestamp = null;
        $previousTimestamp = null;
        $placedOnFirstDay = 0;

        for ($visitIndex = 1; $visitIndex <= $visitCount; $visitIndex++) {
            $day = intdiv($timestamp, 86400);

            if ($day >= $dayCount) {
                // The rest of this visitor's life falls after the corpus ends. Real datasets are
                // censored at the right-hand edge in exactly this way.
                break;
            }

            // Mega-visits are placed, not drawn. Drawing them per visit makes the count a Poisson
            // variable, and on a small corpus expecting two that means none at all about one run
            // in seven - a flaky check, and a corpus that does not contain what it says it does.
            $isMega = ($megaRemaining[$day] ?? 0) > 0;

            if ($isMega) {
                $megaRemaining[$day]--;
            }

            $flags = $flagsForVisitor;
            $actionCount = $this->drawActionCount($rng, $isMega);
            $startSecond = $timestamp % 86400;

            if ($isMega) {
                $flags |= PlanSpool::FLAG_MEGA_VISIT;
                // Thousands of actions need room before midnight: visits must not cross the day
                // boundary, so a mega visit is pulled back into the first half of the day.
                $startSecond = min($startSecond, $rng->nextInt(0, 43200));
                $timestamp = $day * 86400 + $startSecond;
            }

            $itemCount = 0;
            if ($rng->nextBool(Profile::GOAL_CONVERSION_SHARE)) {
                $flags |= PlanSpool::FLAG_GOAL_CONVERSION;
            }
            // Only site 1 has ecommerce enabled - not every site is a shop, and an order on a
            // site without ecommerce archives into nothing.
            if ($rng->nextBool(Profile::ECOMMERCE_ORDER_SHARE) && 1 === $idSite) {
                $flags |= PlanSpool::FLAG_ECOMMERCE_ORDER;
                $itemCount = $rng->pickFromCdf(Profile::ECOMMERCE_ITEMS_CDF) + 1;
            }

            if (null === $firstTimestamp) {
                $firstTimestamp = $timestamp;
            }

            $spool->append(
                $day,
                $ordinal,
                $visitIndex,
                $actionCount,
                $timestamp - $firstTimestamp,
                null === $previousTimestamp ? 0 : $timestamp - $previousTimestamp,
                $startSecond,
                $idSite,
                $flags,
                $itemCount
            );

            $perDay[$day]['visits']++;
            $perDay[$day]['actions'] += $actionCount;
            $perDay[$day]['conversions'] += ($flags & PlanSpool::FLAG_GOAL_CONVERSION ? 1 : 0)
                + ($flags & PlanSpool::FLAG_ECOMMERCE_ORDER ? 1 : 0);
            $perDay[$day]['items'] += $itemCount;

            if ($day === $firstDay) {
                $placedOnFirstDay++;
            }

            $previousTimestamp = $timestamp;

            if ($rng->nextBool(Profile::SAME_DAY_RETURN_SHARE)) {
                $timestamp += $rng->nextInt(
                    Profile::SAME_DAY_RETURN_MIN_SECONDS,
                    Profile::SAME_DAY_RETURN_MAX_SECONDS
                );
            } else {
                $gapDays = max(1, (int) round($rng->nextLogNormal(
                    Profile::INTER_VISIT_GAP_MEDIAN_DAYS,
                    Profile::INTER_VISIT_GAP_SIGMA
                )));
                $nextHour = $rng->pickFromCdf($diurnalCdf);
                $timestamp = ($day + $gapDays) * 86400 + $nextHour * 3600 + $rng->nextInt(0, 3599);
            }

            $nextDay = intdiv($timestamp, 86400);

            if ($visitIndex < $visitCount && $nextDay > $firstDay && $nextDay < $dayCount) {
                $scheduled[$nextDay]++;
            }
        }

        return $placedOnFirstDay;
    }

    /**
     * How many mega-visits each day of this shard should contain.
     *
     * Spread evenly over every (day, shard) chunk in the corpus, so the total comes out exactly
     * right without any shard having to know what the others are doing.
     *
     * @return int[] day index => mega-visits to place
     */
    private function megaVisitsPerDay(int $shard, int $days): array
    {
        $shards = $this->profile->getShardCount();
        $totalChunks = max(1, $days * $shards);
        $megaTotal = $this->profile->getMegaVisitCount();

        $perDay = array_fill(0, $days, 0);

        for ($day = 0; $day < $days; $day++) {
            $chunk = $day * $shards + $shard;
            $before = (int) floor($chunk * $megaTotal / $totalChunks);
            $after = (int) floor(($chunk + 1) * $megaTotal / $totalChunks);
            $perDay[$day] = $after - $before;
        }

        return $perDay;
    }

    private function drawActionCount(Rng $rng, bool $isMega): int
    {
        if ($isMega) {
            return $rng->nextInt(Profile::MEGA_VISIT_MIN_ACTIONS, Profile::MEGA_VISIT_MAX_ACTIONS);
        }

        if ($rng->nextBool(Profile::BOUNCE_SHARE)) {
            return 1;
        }

        $drawn = 1 + max(1, (int) round($rng->nextLogNormal(
            Profile::ACTIONS_LOGNORMAL_MEDIAN,
            Profile::ACTIONS_LOGNORMAL_SIGMA
        )));

        return min($drawn, Profile::MAX_ACTIONS_PER_VISIT);
    }

    /**
     * Writes the exact counts onto the load chunks. From here on the queue holds what the plan
     * actually drew, not the estimate the chunks were seeded with - which is what lets id ranges
     * be sized exactly and progress be reported honestly.
     */
    private function recordCounts(int $shard, array $dates, array $perDay): void
    {
        $rows = [];

        foreach ($perDay as $dayIndex => $counts) {
            $rows[] = [
                'dayIndex' => $dayIndex,
                'shard' => $shard,
                'date' => $dates[$dayIndex],
                'visits' => $counts['visits'],
                'actions' => $counts['actions'],
                'conversions' => $counts['conversions'],
                'items' => $counts['items'],
            ];
        }

        $this->context->getQueue()->setPlannedCountsBatch($rows);
    }
}
