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
 * A comparable summary of what a case returned, so a timing can be checked against the answer
 * that produced it. Two engines that disagree about the result are not two engines to compare
 * timings between.
 *
 * The hard part is that the two legs disagree about formatting on almost every column, and
 * always will: the replicated copy flattens NULL to '' on strings and 0 on integers, maps
 * tinyint to Bool, and renders DECIMAL and DATETIME differently. Digesting a whole Visits Log
 * row therefore mismatches on every run and says nothing. So the fingerprint is taken on the
 * stable identity of the answer instead:
 *
 * - a list of visits           -> the ordered idVisit values. This is the real check: same
 *                                 visits, same order, and it held on the corpus.
 * - an archiving result        -> nb_visits. idarchives are new row ids on every run.
 * - anything else              -> row count plus a digest of the payload, marked weak, because
 *                                 a mismatch there is as likely to be formatting as a defect.
 */
final class ResultFingerprint
{
    public const STRONG = 'strong';
    public const WEAK = 'weak';

    /**
     * Keys whose values change between two runs of the same case on the same engine, so they
     * would break the digest without indicating anything.
     */
    private const VOLATILE_KEYS = [
        'idarchives',
        'idarchive',
        'ts_archived',
        'peakMemory',
        'peak_memory',
    ];

    /**
     * @param mixed $decoded the decoded API response
     * @return array{strength: string, rows: ?int, digest: string, summary: string}
     */
    public static function of($decoded): array
    {
        if ($decoded === null) {
            return self::result(self::WEAK, null, '', 'no parsable response');
        }

        $visitIds = self::extractVisitIds($decoded);
        if ($visitIds !== null) {
            return self::result(
                self::STRONG,
                count($visitIds),
                md5(implode(',', $visitIds)),
                count($visitIds) . ' visits'
            );
        }

        $nbVisits = self::extractNbVisits($decoded);
        if ($nbVisits !== null) {
            return self::result(self::STRONG, null, 'nb_visits:' . $nbVisits, $nbVisits . ' visits archived');
        }

        $normalised = self::stripVolatile($decoded);
        $rows = is_array($normalised) ? count($normalised) : null;

        return self::result(
            self::WEAK,
            $rows,
            md5((string) json_encode($normalised)),
            $rows === null ? 'scalar response' : $rows . ' rows'
        );
    }

    /**
     * @param mixed $decoded
     * @return string[]|null
     */
    private static function extractVisitIds($decoded): ?array
    {
        if (!is_array($decoded) || empty($decoded)) {
            return null;
        }

        $ids = [];
        foreach ($decoded as $row) {
            if (!is_array($row) || !array_key_exists('idVisit', $row)) {
                return null;
            }
            $ids[] = (string) $row['idVisit'];
        }

        return $ids;
    }

    /**
     * @param mixed $decoded
     */
    private static function extractNbVisits($decoded): ?int
    {
        if (is_array($decoded) && array_key_exists('nb_visits', $decoded) && is_scalar($decoded['nb_visits'])) {
            return (int) $decoded['nb_visits'];
        }

        return null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function stripVolatile($value)
    {
        if (!is_array($value)) {
            return $value;
        }

        $out = [];
        foreach ($value as $key => $item) {
            if (is_string($key) && in_array($key, self::VOLATILE_KEYS, true)) {
                continue;
            }
            $out[$key] = self::stripVolatile($item);
        }

        return $out;
    }

    /**
     * @return array{strength: string, rows: ?int, digest: string, summary: string}
     */
    private static function result(string $strength, ?int $rows, string $digest, string $summary): array
    {
        return [
            'strength' => $strength,
            'rows' => $rows,
            'digest' => $digest,
            'summary' => $summary,
        ];
    }

    /**
     * The same check for core:archive, which logs rather than returning JSON.
     *
     * CronArchive::logArchiveJobFinished() writes one "N visits found" line per archive it
     * builds, so the visit counts are recoverable from the log and the cron driver gets the
     * same cross-engine result check the request driver gets. Without this the cron driver
     * reports timings with no evidence the two engines archived the same thing.
     *
     * @return array{strength: string, rows: ?int, digest: string, summary: string}|null
     */
    public static function ofArchiveLog(string $output): ?array
    {
        if (!preg_match_all('~(\d+) visits found~', $output, $matches)) {
            return null;
        }

        $counts = array_map('intval', $matches[1]);
        sort($counts);

        return self::result(
            self::STRONG,
            count($counts),
            'visits:' . implode(',', $counts),
            array_sum($counts) . ' visits across ' . count($counts) . ' archive(s)'
        );
    }

    /**
     * Pulls the API response out of a climulti:request child's output.
     *
     * The child writes the API response to stdout, but log handlers can write to the same
     * stream, so the response is not reliably the whole of it. The response is JSON and is
     * emitted last, so the last balanced JSON value in the output is the one to take.
     *
     * @return mixed|null null when nothing in the output parses
     */
    public static function decodeApiOutput(string $output)
    {
        $trimmed = trim($output);
        if ($trimmed === '') {
            return null;
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        foreach (['{', '['] as $opening) {
            $start = strpos($trimmed, $opening);
            while ($start !== false) {
                $candidate = substr($trimmed, $start);
                $decoded = json_decode($candidate, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
                $start = strpos($trimmed, $opening, $start + 1);
            }
        }

        return null;
    }
}
