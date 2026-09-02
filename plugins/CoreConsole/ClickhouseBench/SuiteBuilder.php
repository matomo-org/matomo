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
 */
final class SuiteBuilder
{
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
                $cases[] = BenchCase::api(
                    'v' . $suffix,
                    'Visits Log, segment: ' . $segmentKey,
                    $idSite,
                    $period,
                    $date,
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

                    $cases[] = BenchCase::api(
                        't' . $suffix,
                        'Transitions for one page, segment: ' . $segmentKey,
                        $idSite,
                        $period,
                        $date,
                        $transitionsSegment,
                        $segmentKey,
                        'Transitions.getTransitionsForPageUrl',
                        ['pageUrl' => $transitionsPageUrl]
                    );
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
