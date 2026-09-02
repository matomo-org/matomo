<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

/**
 * Turns runs into the comparison table, and into JSON for anything downstream.
 *
 * Medians, not means: a single slow iteration on a shared instance is a scheduling artefact and
 * a mean carries it into the published number.
 *
 * The spread column is not decoration. A cell whose slowest iteration is more than twice its
 * fastest has not converged - on ClickHouse Cloud that is usually cold marks, and the fix is
 * more warmups, not a footnote. Publishing such a cell as a measurement is the specific mistake
 * this column exists to prevent.
 */
final class Reporter
{
    /** Above this, a cell's iterations disagree enough that the median is not a measurement. */
    public const SPREAD_WARNING = 2.0;

    /**
     * @param array<int, float|null> $values nulls are dropped, so a partly failed set of
     *                                       iterations still yields a median of what worked
     */
    public static function median(array $values): ?float
    {
        $values = array_values(array_filter($values, static fn($value): bool => $value !== null));
        if (empty($values)) {
            return null;
        }

        sort($values);
        $count = count($values);
        $middle = (int) floor(($count - 1) / 2);

        if ($count % 2) {
            return (float) $values[$middle];
        }

        return ((float) $values[$middle] + (float) $values[$middle + 1]) / 2.0;
    }

    public static function formatMs(?float $ms): string
    {
        if ($ms === null) {
            return '-';
        }

        if ($ms < 1000) {
            return round($ms) . ' ms';
        }

        if ($ms < 60000) {
            return round($ms / 1000, 2) . ' s';
        }

        return round($ms / 60000, 2) . ' min';
    }

    /**
     * @param RunResult[] $results
     * @param Engine[] $engines
     * @return array<int, array<string, mixed>> one entry per case, in case order
     */
    public function summarise(array $results, array $engines): array
    {
        $byCase = [];
        foreach ($results as $result) {
            if ($result->isWarmup()) {
                continue;
            }
            $byCase[$result->getCaseId()][$result->getEngineKey()][] = $result;
        }

        $summary = [];
        foreach ($byCase as $caseId => $byEngine) {
            $anyResult = reset($byEngine)[0];
            $row = [
                'case' => $caseId,
                'group' => $anyResult->getCase()->getGroup(),
                'segmentLabel' => $anyResult->getCase()->getSegmentLabel(),
                'title' => $anyResult->getCase()->getTitle(),
                'engines' => [],
            ];

            foreach ($engines as $engine) {
                $row['engines'][$engine->getKey()] = $this->summariseEngine($byEngine[$engine->getKey()] ?? []);
            }

            $row += $this->compare($row['engines'], $engines);
            $row['agreement'] = $this->agreement($byEngine);
            $row['empty'] = $this->isEmpty($byEngine);

            $summary[] = $row;
        }

        return $summary;
    }

    /**
     * @param RunResult[] $runs
     * @return array<string, mixed>
     */
    private function summariseEngine(array $runs): array
    {
        $ok = array_values(array_filter($runs, static fn(RunResult $run): bool => $run->isOk()));

        if (empty($ok)) {
            $errors = array_map(static fn(RunResult $run): string => $run->getError(), $runs);
            return [
                'n' => 0,
                'median' => null,
                'min' => null,
                'max' => null,
                'spread' => null,
                'source' => 'none',
                'error' => empty($errors) ? 'not run' : $errors[0],
            ];
        }

        $values = array_map(static fn(RunResult $run): float => $run->getComparableMs(), $ok);
        $min = min($values);
        $max = max($values);

        // Which timing the numbers are: the archive build as ArchivingMetrics recorded it, or
        // the child's wall clock. Reported rather than hidden - a table that silently mixes the
        // two is comparing different things in the same column.
        $fromMetrics = array_filter($ok, static fn(RunResult $run): bool => $run->hasArchiveMetrics());
        $source = count($fromMetrics) === count($ok)
            ? 'archiving_metrics'
            : (empty($fromMetrics) ? 'wall' : 'mixed');

        return [
            'n' => count($ok),
            'median' => self::median($values),
            'min' => $min,
            'max' => $max,
            'spread' => $min > 0 ? $max / $min : null,
            'source' => $source,
            'error' => count($ok) === count($runs) ? '' : 'some iterations failed',
            'otherArchives' => array_sum(array_map(
                static fn(RunResult $run): int => $run->getOtherArchiveCount(),
                $ok
            )),
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $engineSummaries
     * @param Engine[] $engines
     * @return array{ratio: ?float, faster: string}
     */
    private function compare(array $engineSummaries, array $engines): array
    {
        $medians = [];
        foreach ($engines as $engine) {
            $median = $engineSummaries[$engine->getKey()]['median'] ?? null;
            if ($median !== null && $median > 0) {
                $medians[$engine->getKey()] = $median;
            }
        }

        if (count($medians) < 2) {
            return ['ratio' => null, 'faster' => ''];
        }

        asort($medians);
        $keys = array_keys($medians);
        $values = array_values($medians);

        return [
            'ratio' => $values[count($values) - 1] / $values[0],
            'faster' => $keys[0],
        ];
    }

    /**
     * Whether the engines returned the same answer.
     *
     * A strong digest is the ordered list of visit ids, or the archived visit count - things
     * the two engines must agree on. A weak digest is a hash of the whole payload, which the
     * two engines are expected to disagree on for reasons that are not defects (the replicated
     * copy flattens NULL, maps tinyint to Bool, and formats DECIMAL and DATETIME differently),
     * so a weak mismatch is reported as unverified rather than as a disagreement.
     *
     * @param array<string, RunResult[]> $byEngine
     */
    private function agreement(array $byEngine): string
    {
        $digests = [];
        $strength = ResultFingerprint::STRONG;

        foreach ($byEngine as $runs) {
            foreach ($runs as $run) {
                $fingerprint = $run->getFingerprint();
                if ($fingerprint === null || $fingerprint['digest'] === '') {
                    continue;
                }
                if ($fingerprint['strength'] === ResultFingerprint::WEAK) {
                    $strength = ResultFingerprint::WEAK;
                }
                $digests[$fingerprint['digest']] = true;
            }
        }

        if (count($digests) === 0) {
            return 'n/a';
        }

        if (count($digests) > 1) {
            return $strength === ResultFingerprint::WEAK ? 'unverified' : 'DIFFERENT';
        }

        return $strength === ResultFingerprint::WEAK ? 'same (weak)' : 'same';
    }

    /**
     * @param array<string, RunResult[]> $byEngine
     */
    private function isEmpty(array $byEngine): bool
    {
        $sawFingerprint = false;

        foreach ($byEngine as $runs) {
            foreach ($runs as $run) {
                $fingerprint = $run->getFingerprint();
                if ($fingerprint === null) {
                    continue;
                }
                $sawFingerprint = true;
                if (!ResultFingerprint::isEmpty($fingerprint)) {
                    return false;
                }
            }
        }

        return $sawFingerprint;
    }

    /**
     * @param Engine[] $engines
     * @return string[]
     */
    public function tableHeader(array $engines): array
    {
        $header = ['Case', 'Segment', 'Group'];
        foreach ($engines as $engine) {
            $header[] = $engine->getLabel();
            $header[] = 'spread';
        }
        $header[] = 'Ratio';
        $header[] = 'Result';

        return $header;
    }

    /**
     * @param array<int, array<string, mixed>> $summary
     * @param Engine[] $engines
     * @return array<int, string[]>
     */
    public function tableRows(array $summary, array $engines): array
    {
        $rows = [];

        foreach ($summary as $case) {
            $row = [$case['case'], $case['segmentLabel'], $case['group']];

            foreach ($engines as $engine) {
                $engineSummary = $case['engines'][$engine->getKey()];
                if (($engineSummary['n'] ?? 0) === 0) {
                    $row[] = 'FAILED';
                    $row[] = '';
                    continue;
                }

                $row[] = self::formatMs($engineSummary['median']);
                $spread = $engineSummary['spread'];
                $row[] = $spread === null
                    ? ''
                    : (($spread > self::SPREAD_WARNING ? '! ' : '') . round($spread, 1) . 'x');
            }

            $row[] = $case['ratio'] === null
                ? '-'
                : round($case['ratio'], 2) . 'x ' . $case['faster'];
            $row[] = $case['agreement'];

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param RunResult[] $results
     * @param array<int, array<string, mixed>> $summary
     * @param array<string, mixed> $meta
     */
    public function toJson(array $results, array $summary, array $meta): string
    {
        return (string) json_encode(
            [
                'meta' => $meta,
                'summary' => $summary,
                'runs' => array_map(static fn(RunResult $run): array => $run->toArray(), $results),
            ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * @param array<int, array<string, mixed>> $summary
     * @return string[] things about the run that a reader needs before quoting a number from it
     */
    public function caveats(array $summary): array
    {
        $caveats = [];

        foreach ($summary as $case) {
            foreach ($case['engines'] as $engineKey => $engineSummary) {
                if (($engineSummary['n'] ?? 0) === 0) {
                    $caveats[] = sprintf('%s/%s: %s', $case['case'], $engineKey, $engineSummary['error']);
                    continue;
                }

                if (($engineSummary['spread'] ?? 0) > self::SPREAD_WARNING) {
                    $caveats[] = sprintf(
                        '%s/%s: iterations ranged %s to %s (%sx). Not converged - add warmups'
                        . ' before quoting this cell.',
                        $case['case'],
                        $engineKey,
                        self::formatMs($engineSummary['min']),
                        self::formatMs($engineSummary['max']),
                        round((float) $engineSummary['spread'], 1)
                    );
                }

                if (($engineSummary['source'] ?? '') === 'wall' && $case['group'] === BenchCase::GROUP_ARCHIVE) {
                    $caveats[] = sprintf(
                        '%s/%s: no ArchivingMetrics row, so the number is the child process wall'
                        . ' clock including bootstrap. Usually means the archive was reused, or the'
                        . ' case is plugin-scoped (ArchivingMetrics only records all-plugin runs).',
                        $case['case'],
                        $engineKey
                    );
                }

                if (($engineSummary['source'] ?? '') === 'mixed') {
                    $caveats[] = sprintf(
                        '%s/%s: some iterations produced an ArchivingMetrics row and some did not,'
                        . ' so the median mixes archive time with wall clock.',
                        $case['case'],
                        $engineKey
                    );
                }

                if (!empty($engineSummary['otherArchives'])) {
                    $caveats[] = sprintf(
                        '%s/%s: the run also built %d archive(s) the case did not ask for. Their'
                        . ' cost is in the wall clock but not in the archive time.',
                        $case['case'],
                        $engineKey,
                        $engineSummary['otherArchives']
                    );
                }
            }

            if (!empty($case['empty'])) {
                $caveats[] = sprintf(
                    '%s: every engine returned an empty result, so this row times an empty result'
                    . ' set rather than the report. Usually the segment needles do not occur in'
                    . ' this data - check --needle-* and --date against the corpus.',
                    $case['case']
                );
            }

            if ($case['agreement'] === 'DIFFERENT') {
                $caveats[] = sprintf(
                    '%s: the engines returned DIFFERENT results. The timings are not comparable'
                    . ' until that is explained.',
                    $case['case']
                );
            }
        }

        return array_values(array_unique($caveats));
    }
}
