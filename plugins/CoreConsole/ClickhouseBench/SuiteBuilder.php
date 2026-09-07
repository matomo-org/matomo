<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

use InvalidArgumentException;
use Piwik\Date;

/**
 * Builds the case list.
 *
 * The segment set and the case ids match the standalone SQL benchmark this harness exists to
 * reproduce through Matomo rather than against hand-written queries: v1/v1s/v1n/v1c/v1e for
 * the Visits Log, a1/a1s/... for archiving, t1/t1s for Transitions. Keeping the ids identical
 * is the point - it is what makes a number from here comparable to a number from there, and
 * a divergence between the two is then a finding about the adapter rather than a mystery.
 *
 * The needles are options rather than constants because they only mean anything against a
 * corpus that contains them. A segment that matches nothing measures an empty result set very
 * quickly on both engines and looks like a win.
 *
 * The live cases run over a RAMP of windows rather than one day, because a single-day timing
 * does not generalise: on the standalone benchmark Transitions loses to MySQL at one day and
 * wins from seven onwards, so a one-day number answers a narrower question than the one being
 * asked. Archiving is deliberately left out of the ramp - Matomo archives days from the logs
 * and aggregates longer periods from those day archives, so a range archive would measure
 * aggregation, not the log queries the engines are being compared on.
 */
final class SuiteBuilder
{
    /**
     * The window ramp for the live cases, in days, shortest first.
     *
     * Matches the standalone benchmark's sweep, which is the point: 1d is the window every
     * published A/B number so far was measured in, and the longer ones are where the shape of
     * the difference shows up. 365d is inside the corpus - the p200 data spans ~420 days and
     * ends 2026-08-31 - but only when the windows grow BACKWARDS from the anchor.
     */
    public const DEFAULT_LIVE_WINDOWS = [1, 7, 30, 365];

    /** Suffix per segment, so ids line up with the SQL benchmark's file names. */
    private const SEGMENT_SUFFIX = [
        'none' => '1',
        'compound' => '1s',
        'negated' => '1n',
        'conversion' => '1c',
        'ecommerce' => '1e',
    ];

    /**
     * Segments in the shape a customer actually builds, from cheapest to most expensive.
     *
     * - compound: the two action-scope components name DIFFERENT dimensions, so they compile
     *   to two separate log_action joins rather than sharing one, and the two visit-scope
     *   components are what make the result set small enough that MySQL loses its early exit.
     * - negated: compound plus one excluded URL. "This visit never touched X" is a statement
     *   about the whole visit, so it compiles to NOT IN wrapping a second copy of the join.
     * - conversion / ecommerce: reach log_conversion and log_conversion_item, the tables
     *   nothing else in the set touches.
     *
     * @param array<string, string> $needles
     * @return array<string, string> segment label => segment condition
     */
    public static function defaultSegments(array $needles): array
    {
        $url = $needles['url'];
        $excludedUrl = $needles['excludedUrl'];
        $title = $needles['title'];
        $country = $needles['country'];
        $product = $needles['product'];
        $idGoal = $needles['idGoal'];

        $compound = sprintf(
            'pageUrl=@%s;pageTitle=@%s;countryCode==%s;deviceType==desktop',
            $url,
            $title,
            $country
        );

        return [
            'none' => '',
            'compound' => $compound,
            'negated' => $compound . ';pageUrl!@' . $excludedUrl,
            'conversion' => $compound . ';visitConvertedGoalId==' . $idGoal,
            'ecommerce' => $compound . ';productName=@' . $product,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function defaultNeedles(): array
    {
        return [
            'url' => '/news/',
            'excludedUrl' => '/sport/',
            'title' => 'Budget',
            'country' => 'de',
            'product' => 'Daily',
            'idGoal' => '1',
            // Transitions evaluates action-scope segment components against the single page the
            // report is already pinned to, so its title needle has to MATCH that page or the
            // report comes back empty. Its selectivity is irrelevant - it cannot filter anything.
            'transitionsTitle' => 'City',
        ];
    }

    /**
     * @param array<string, mixed> $options
     * @return BenchCase[]
     */
    public function build(array $options): array
    {
        $idSite = (int) $options['idSite'];
        $period = (string) $options['period'];
        $date = (string) $options['date'];
        $groups = $options['groups'];
        $segments = $options['segments'];
        $segmentKeys = $options['segmentKeys'];
        $liveLimit = (int) $options['liveLimit'];
        $archivePlugin = (string) $options['archivePlugin'];
        $transitionsPageUrl = (string) $options['transitionsPageUrl'];
        $needles = $options['needles'];
        $liveWindows = self::windows(
            array_key_exists('liveWindows', $options) ? (array) $options['liveWindows'] : self::DEFAULT_LIVE_WINDOWS,
            $period,
            $date
        );

        foreach ($segmentKeys as $key) {
            if (!array_key_exists($key, $segments)) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown segment "%s". Known segments: %s.',
                    $key,
                    implode(', ', array_keys($segments))
                ));
            }
        }

        $cases = [];

        foreach ($segmentKeys as $segmentKey) {
            $segment = $segments[$segmentKey];
            $suffix = self::SEGMENT_SUFFIX[$segmentKey] ?? ('1' . substr($segmentKey, 0, 1));

            if (in_array(BenchCase::GROUP_API, $groups, true)) {
                // Windows of one query run consecutively rather than interleaved with the other
                // queries, so the ramp for one report reads down the table as a ramp.
                foreach ($liveWindows as $window) {
                    $cases[] = BenchCase::api(
                        'v' . $suffix . $window['suffix'],
                        'Visits Log over ' . $window['phrase'] . ', segment: ' . $segmentKey,
                        $idSite,
                        $window['period'],
                        $window['date'],
                        $segment,
                        $segmentKey,
                        'Live.getLastVisitsDetails',
                        [
                            'filter_limit' => $liveLimit,
                            // The Visits Log is the one report where the enriched-visitor pass costs
                            // more than the query, and it is not what is being compared. Left ON so
                            // the number is the report a customer waits for, not a subset of it.
                            'doNotFetchActions' => '0',
                        ]
                    );
                }

                // Transitions is pinned to a single page, so it needs one that exists in the
                // corpus. No URL, no case - a made up URL would measure an empty result.
                if ($transitionsPageUrl !== '' && in_array($segmentKey, ['none', 'compound'], true)) {
                    $transitionsSegment = $segment;
                    if ($segmentKey === 'compound') {
                        $transitionsSegment = str_replace(
                            'pageTitle=@' . $needles['title'],
                            'pageTitle=@' . $needles['transitionsTitle'],
                            $segment
                        );
                    }

                    // Transitions is the case the ramp matters most for: its cost grows with the
                    // window on both engines and it changes which engine wins. Note that an
                    // instance with [Transitions] max_period_allowed set will refuse the longer
                    // windows with "PeriodNotAllowed" - which arrives as a failed case, not as a
                    // fast one, because an API error in the payload is checked for.
                    foreach ($liveWindows as $window) {
                        $cases[] = BenchCase::api(
                            't' . $suffix . $window['suffix'],
                            'Transitions for one page over ' . $window['phrase'] . ', segment: ' . $segmentKey,
                            $idSite,
                            $window['period'],
                            $window['date'],
                            $transitionsSegment,
                            $segmentKey,
                            'Transitions.getTransitionsForPageUrl',
                            ['pageUrl' => $transitionsPageUrl]
                        );
                    }
                }
            }

            if (in_array(BenchCase::GROUP_ARCHIVE, $groups, true)) {
                $cases[] = BenchCase::archive(
                    'a' . $suffix,
                    'Archive one ' . $period . ', segment: ' . $segmentKey,
                    $idSite,
                    $period,
                    $date,
                    $segment,
                    $segmentKey,
                    $archivePlugin
                );
            }
        }

        return $cases;
    }

    /**
     * Turns a ramp of day counts into the period/date pairs the live cases run under.
     *
     * Windows grow BACKWARDS from the anchor, and the anchor is the LAST day of --date. That
     * direction is not a preference: a 365-day window grown forwards from a mid-corpus anchor
     * runs off the end of the data and measures a half-empty window at full price, which reads
     * as a fast engine rather than as missing data. Backwards from the last day of data is also
     * what "the last year" means to whoever asked for the number.
     *
     * A one-day window stays period=day rather than a one-day range, so v1/t1 remain the exact
     * request every published number so far was measured with.
     *
     * @param array<int, int|string> $windowDays day counts; empty means "no ramp", and the
     *                                           live cases then run on $period/$date as given
     * @return array<int, array{suffix: string, phrase: string, period: string, date: string}>
     */
    public static function windows(array $windowDays, string $period, string $date): array
    {
        if (empty($windowDays)) {
            return [[
                'suffix' => '',
                'phrase' => $period === 'day' ? '1 day' : ('one ' . $period . ', ' . $date),
                'period' => $period,
                'date' => $date,
            ]];
        }

        $anchor = self::anchorDay($date);

        $windows = [];
        foreach ($windowDays as $days) {
            $days = self::readWindowDays($days);

            $windows[$days] = [
                'suffix' => $days === 1 ? '' : '-' . $days . 'd',
                'phrase' => $days === 1 ? '1 day' : ($days . ' days'),
                'period' => $days === 1 ? 'day' : 'range',
                'date' => $days === 1
                    ? $anchor
                    : Date::factory($anchor)->subDay($days - 1)->toString('Y-m-d') . ',' . $anchor,
            ];
        }

        // Keyed by day count, so a ramp given twice or out of order still produces one case per
        // window, shortest first.
        ksort($windows);

        return array_values($windows);
    }

    /**
     * @param int|string $days
     */
    private static function readWindowDays($days): int
    {
        // "7d" is accepted as well as "7": the standalone benchmark's --sweep takes the d suffix
        // and that is the spelling anyone coming from it will type.
        $value = trim((string) $days);
        if (substr($value, -1) === 'd') {
            $value = substr($value, 0, -1);
        }

        if (!preg_match('/^\d+$/', $value) || (int) $value < 1) {
            throw new InvalidArgumentException(sprintf(
                'A live window is a whole number of days, one or more, eg %s. Got "%s".',
                implode(',', self::DEFAULT_LIVE_WINDOWS),
                $days
            ));
        }

        return (int) $value;
    }

    private static function anchorDay(string $date): string
    {
        $parts = array_map('trim', explode(',', $date));
        $anchor = (string) end($parts);

        try {
            return Date::factory($anchor)->toString('Y-m-d');
        } catch (\Throwable $e) {
            // Deliberately not falling back to today. A window silently anchored somewhere the
            // operator did not ask for would still produce a full table of numbers.
            throw new InvalidArgumentException(sprintf(
                'The live windows need a concrete anchor day and --date=%s does not give one.'
                . ' Pass a date like 2026-08-03 - a range anchors on its last day - or'
                . ' --live-windows= to run the live cases on --period and --date as given.',
                $date
            ));
        }
    }

    /**
     * @param BenchCase[] $cases
     * @param string[] $filters case ids or glob patterns; empty keeps everything
     * @return BenchCase[]
     */
    public static function filter(array $cases, array $filters): array
    {
        if (empty($filters)) {
            return $cases;
        }

        return array_values(array_filter($cases, static function (BenchCase $case) use ($filters): bool {
            foreach ($filters as $filter) {
                if (fnmatch($filter, $case->getId())) {
                    return true;
                }
            }
            return false;
        }));
    }
}
