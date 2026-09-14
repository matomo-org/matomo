<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

use Piwik\Common;

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
 * - an archiving result        -> every metric and every report the archive wrote, read back
 *                                 out of the archive tables by ArchiveReader. Those tables are
 *                                 MySQL on both legs, so what they hold is directly comparable
 *                                 and a difference is a difference in what was computed.
 * - an archiving result whose
 *   archives could not be read -> nb_visits, marked WEAK. A visit count is not evidence that
 *                                 two engines built the same reports, and labelling it strong
 *                                 is how this harness used to overstate what it had checked.
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

        // Reached only when the archive tables could not be read - ArchiveReader is what an
        // archive case is fingerprinted on. WEAK, because a visit count says the two engines
        // agree about how many visits the day had and nothing at all about the few hundred
        // reports they each built from those visits. It was labelled strong for a fortnight
        // and the A/B write-ups inherited that overstatement.
        $nbVisits = self::extractNbVisits($decoded);
        if ($nbVisits !== null) {
            return self::result(
                self::WEAK,
                null,
                'nb_visits:' . $nbVisits,
                $nbVisits . ' visits archived, reports not compared'
            );
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
     * A fingerprint over what an archive actually wrote, from ArchiveReader::read().
     *
     * This is the archive equivalent of the ordered visit id list: it compares the answer,
     * not a count of the inputs to it. Every metric in archive_numeric and every report in
     * archive_blob goes in, so the two engines have to agree on all of them.
     *
     * Three normalisations, each one covering a way the same answer can be written down
     * differently, and no more than that - the point of the fingerprint is to notice a real
     * disagreement, so anything normalised away has to be provably not one:
     *
     * - done flags are dropped. Their value is the archive's status, not a report value.
     * - numbers are compared as numbers to 6 decimal places. The engines return metrics with
     *   different scale and type ('15' against 15, 1.0 against '1'), and a sum of floats
     *   accumulated in a different order can differ in the last bits. Six places is finer
     *   than a hundredth of a cent, so a real difference in a revenue total still shows.
     * - report rows are compared as a set, not a sequence. Both engines emit the same rows in
     *   a different order whenever a metric ties - and a report is sorted again when it is
     *   read, so the stored order is an artifact of the aggregation rather than the answer.
     *   A difference that matters still shows, because it changes which rows are present.
     *
     * @param array{
     *     numeric: array<int, array{name: string, value: mixed}>,
     *     blob: array<int, array{name: string, value: mixed}>
     * } $archived
     * @return array{strength: string, rows: ?int, digest: string, summary: string}
     */
    public static function ofArchivedReports(array $archived): array
    {
        $metrics = [];
        foreach ($archived['numeric'] ?? [] as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '' || strpos($name, 'done') === 0) {
                continue;
            }
            $metrics[] = $name . '=' . self::canonicalScalar($row['value'] ?? null);
        }

        $reports = [];
        foreach ($archived['blob'] ?? [] as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $reports[] = $name . '=' . self::canonicalBlob((string) ($row['value'] ?? ''));
        }

        if (empty($metrics) && empty($reports)) {
            return self::result(self::WEAK, 0, '', 'no archive rows found');
        }

        // Sorted because the row order the two databases hand back is not part of the answer;
        // the order WITHIN a report blob is, and that is preserved inside canonicalBlob().
        sort($metrics);
        sort($reports);

        return self::result(
            self::STRONG,
            count($metrics) + count($reports),
            md5(implode("\n", $metrics) . "\n--\n" . implode("\n", $reports)),
            count($metrics) . ' metrics, ' . count($reports) . ' reports'
        );
    }

    /**
     * One report blob, reduced to a digest of its contents.
     *
     * Blobs are gzcompress(serialize($rows)). A blob that will not uncompress or unserialize
     * is digested as raw bytes rather than skipped - that still compares equal when the two
     * engines wrote the same bytes, and it never silently drops a report from the comparison.
     */
    private static function canonicalBlob(string $value): string
    {
        $raw = @gzuncompress($value);
        if ($raw === false) {
            $raw = $value;
        }

        $decoded = self::unserializeOrNull($raw);
        if ($decoded === null) {
            return 'raw:' . md5($raw);
        }

        return md5((string) json_encode(self::canonicalValue($decoded)));
    }

    /**
     * @return mixed|null null when $value is not a serialized PHP value
     */
    private static function unserializeOrNull(string $value)
    {
        // Shape check before unserialising, for two reasons: a blob that did not decode is a
        // case this class handles rather than an error, and unserialize() warns on input that
        // was never serialized at all. A report label can quite legitimately begin "a:" or
        // "i:", so the marker is matched in full rather than by its first letter.
        if (!preg_match('~^(a:\d+:\{|s:\d+:"|i:-?\d+;|d:[-\d.eE+]+;|b:[01];|N;)~', $value)) {
            return null;
        }

        // safe_unserialize() rather than unserialize(): a blob is data out of the database and
        // plain unserialize() would build objects out of whatever it names.
        $decoded = Common::safe_unserialize($value);
        if ($decoded === false && $value !== 'b:0;') {
            return null;
        }

        return $decoded;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function canonicalValue($value)
    {
        // A report blob nests: the outer value is a map of subtable id to a STRING that is
        // itself a serialized row set. Without recursing into those strings the whole subtable
        // is compared as raw text, and every formatting difference between the two engines -
        // s:1:"1" against i:1 - comes back as a difference in the answer.
        if (is_string($value)) {
            $inner = self::unserializeOrNull($value);
            if ($inner !== null) {
                return self::canonicalValue($inner);
            }
        }

        if (!is_array($value)) {
            return self::canonicalScalar($value);
        }

        $out = [];
        foreach ($value as $key => $item) {
            $out[(string) $key] = self::canonicalValue($item);
        }

        if (self::isList($value)) {
            $rows = array_values($out);
            usort($rows, static function ($left, $right): int {
                return strcmp((string) json_encode($left), (string) json_encode($right));
            });

            return $rows;
        }

        // Keys here are meaningful - subtable ids, column ids, 'label' - so they are kept and
        // only their order is normalised.
        ksort($out);

        return $out;
    }

    /**
     * @param array<mixed> $value
     */
    private static function isList(array $value): bool
    {
        return $value === [] || array_keys($value) === range(0, count($value) - 1);
    }

    /**
     * @param mixed $value
     */
    private static function canonicalScalar($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
            $number = sprintf('%.6F', (float) $value);
            $number = rtrim(rtrim($number, '0'), '.');
            // sprintf('%.6F', -0.0) is '-0.000000', which trims to '-0'.
            return $number === '' || $number === '-0' ? '0' : $number;
        }

        return (string) $value;
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
     * Whether the case returned nothing at all.
     *
     * A segment whose needles do not occur in the data matches no visits, and an empty result
     * set is fast on both engines - so every segmented case looks quick and the ratio between
     * the engines looks spectacular. It is the most likely way for a whole run to be wrong
     * while looking entirely healthy, and it is invisible in a table of timings.
     *
     * @param array{strength: string, rows: ?int, digest: string, summary: string} $fingerprint
     */
    public static function isEmpty(array $fingerprint): bool
    {
        if ($fingerprint['rows'] === 0) {
            return true;
        }

        if ($fingerprint['digest'] === 'nb_visits:0') {
            return true;
        }

        return (bool) preg_match('~^visits:0(,0)*$~', $fingerprint['digest']);
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
