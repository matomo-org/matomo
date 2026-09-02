<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\ClickhouseBench;

use Piwik\Config;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorApi;
use RuntimeException;

/**
 * Resolves the benchmark's segments to stored segments, and creates them on request.
 *
 * Only the cron archive driver needs this. core:archive works from archive_invalidations, whose
 * rows identify a segment by the hash in the done flag, and it turns a hash back into a segment
 * definition by looking through the STORED segments (QueueConsumer::findSegmentForArchive). An
 * ad-hoc segment string has no stored row, so core:archive logs "Could not find stored segment
 * for done flag hash" and moves on - the case would appear to pass having archived nothing.
 *
 * Creating them is a deliberate, separate step rather than something a benchmark run does on
 * the way past, because of what it costs. A new segment with auto-archiving on schedules
 * re-archiving from [General] process_new_segments_from, which defaults to "beginning_of_time".
 * On a corpus of any size that is not a benchmark, it is a multi-day archiving run that starts
 * on the next core:archive and that nothing in the benchmark's output would explain.
 */
final class SegmentRegistrar
{
    public const NAME_PREFIX = 'Bench: ';

    /**
     * @param string[] $definitions
     * @return array<string, array{idsegment: ?int, name: string, autoArchive: bool}> keyed by definition
     */
    public function resolve(int $idSite, array $definitions): array
    {
        $stored = SegmentEditorApi::getInstance()->getAll($idSite);

        $byDefinition = [];
        foreach ($stored as $segment) {
            $byDefinition[urldecode((string) $segment['definition'])] = $segment;
        }

        $resolved = [];
        foreach ($definitions as $definition) {
            if ($definition === '') {
                continue;
            }

            $match = $byDefinition[$definition] ?? null;
            $resolved[$definition] = [
                'idsegment' => $match === null ? null : (int) $match['idsegment'],
                'name' => $match === null ? '' : (string) $match['name'],
                'autoArchive' => $match !== null && !empty($match['auto_archive']),
            ];
        }

        return $resolved;
    }

    /**
     * @param string[] $definitions
     * @return array<string, int> definition => idsegment, for the ones created
     */
    public function create(int $idSite, array $definitions, bool $allowFullReArchive): array
    {
        $processFrom = (string) (Config::getInstance()->General['process_new_segments_from'] ?? 'beginning_of_time');

        if ($processFrom === 'beginning_of_time' && !$allowFullReArchive) {
            throw new RuntimeException(
                'Refusing to create auto-archived segments while [General]'
                . ' process_new_segments_from = "beginning_of_time". Every segment created that way'
                . ' schedules re-archiving of the whole history, which the next core:archive will'
                . ' start working through - on a large corpus that is days of archiving, and none of'
                . ' it is the benchmark. Set process_new_segments_from = "segment_creation_time"'
                . ' first, or pass --allow-full-rearchive if that is genuinely what you want.'
            );
        }

        $existing = $this->resolve($idSite, $definitions);
        $created = [];
        $index = 0;

        foreach ($definitions as $definition) {
            $index++;
            if ($definition === '' || !empty($existing[$definition]['idsegment'])) {
                continue;
            }

            $created[$definition] = SegmentEditorApi::getInstance()->add(
                self::NAME_PREFIX . $index,
                $definition,
                $idSite,
                true,
                true
            );
        }

        return $created;
    }

    /**
     * Definition => idsegment for the segments that are usable by the cron driver, plus the
     * reasons the rest are not.
     *
     * @param string[] $definitions
     * @return array{usable: array<string, int>, problems: string[]}
     */
    public function audit(int $idSite, array $definitions): array
    {
        $usable = [];
        $problems = [];

        foreach ($this->resolve($idSite, $definitions) as $definition => $info) {
            if (empty($info['idsegment'])) {
                $problems[] = 'not stored: ' . $definition;
                continue;
            }

            if (!$info['autoArchive']) {
                $problems[] = 'stored as "' . $info['name'] . '" but auto-archiving is off: ' . $definition;
                continue;
            }

            $usable[$definition] = (int) $info['idsegment'];
        }

        return ['usable' => $usable, 'problems' => $problems];
    }
}
