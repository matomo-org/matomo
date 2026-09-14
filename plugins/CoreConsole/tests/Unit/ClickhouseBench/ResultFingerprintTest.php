<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\tests\Unit\ClickhouseBench;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreConsole\ClickhouseBench\ResultFingerprint;

/**
 * @group CoreConsole
 * @group ClickhouseBench
 * @group Plugins
 */
class ResultFingerprintTest extends TestCase
{
    public function testAListOfVisitsIsFingerprintedOnItsVisitIdsInOrder(): void
    {
        $first = ResultFingerprint::of([['idVisit' => '3'], ['idVisit' => '1']]);
        $same = ResultFingerprint::of([['idVisit' => 3], ['idVisit' => 1]]);
        $reordered = ResultFingerprint::of([['idVisit' => '1'], ['idVisit' => '3']]);

        self::assertSame(ResultFingerprint::STRONG, $first['strength']);
        self::assertSame(2, $first['rows']);
        self::assertSame($first['digest'], $same['digest']);
        self::assertNotSame($first['digest'], $reordered['digest'], 'order is part of the answer');
    }

    /**
     * The two engines disagree about the formatting of most columns and always will: the
     * replicated copy flattens NULL to '' and to 0, maps tinyint to Bool, and renders DECIMAL
     * and DATETIME differently. Digesting whole rows would mismatch on every run and say
     * nothing, so the visit ids alone are what gets compared.
     */
    public function testVisitDigestIgnoresColumnsTheEnginesFormatDifferently(): void
    {
        $mysql = ResultFingerprint::of([
            ['idVisit' => '7', 'userId' => null, 'visitorType' => 0, 'revenue' => '1.00'],
        ]);
        $clickhouse = ResultFingerprint::of([
            ['idVisit' => '7', 'userId' => '', 'visitorType' => false, 'revenue' => '1'],
        ]);

        self::assertSame($mysql['digest'], $clickhouse['digest']);
    }

    /**
     * The fallback for an archive whose archives could not be read. It is WEAK: a visit count
     * is not evidence that two engines built the same reports out of those visits.
     */
    public function testAnArchivingResponseWithoutReadableArchivesIsOnlyItsVisitCountAndIsWeak(): void
    {
        $first = ResultFingerprint::of(['idarchives' => [11], 'nb_visits' => 3069]);
        $second = ResultFingerprint::of(['idarchives' => [4096], 'nb_visits' => 3069]);

        self::assertSame(ResultFingerprint::WEAK, $first['strength']);
        self::assertSame($first['digest'], $second['digest'], 'a new archive id is not a new answer');
    }

    public function testAnArchiveIsFingerprintedOnEveryMetricAndReportItWrote(): void
    {
        $fingerprint = ResultFingerprint::ofArchivedReports([
            'numeric' => [
                ['name' => 'nb_visits', 'value' => '15'],
                ['name' => 'nb_actions', 'value' => '61'],
            ],
            'blob' => [
                ['name' => 'Actions_actions', 'value' => gzcompress(serialize([['label' => '/news/', 2 => 9]]))],
            ],
        ]);

        self::assertSame(ResultFingerprint::STRONG, $fingerprint['strength']);
        self::assertSame(3, $fingerprint['rows']);
        self::assertSame('2 metrics, 1 reports', $fingerprint['summary']);
    }

    /**
     * The row order the two databases hand the archive rows back in is not part of the answer -
     * neither query has an ORDER BY - so it must not move the digest.
     */
    public function testArchiveDigestDoesNotDependOnTheOrderRowsComeBackIn(): void
    {
        $blob = gzcompress(serialize([['label' => 'x']]));

        $one = ResultFingerprint::ofArchivedReports([
            'numeric' => [['name' => 'nb_visits', 'value' => '15'], ['name' => 'nb_actions', 'value' => '61']],
            'blob' => [['name' => 'A', 'value' => $blob], ['name' => 'B', 'value' => $blob]],
        ]);
        $other = ResultFingerprint::ofArchivedReports([
            'numeric' => [['name' => 'nb_actions', 'value' => '61'], ['name' => 'nb_visits', 'value' => '15']],
            'blob' => [['name' => 'B', 'value' => $blob], ['name' => 'A', 'value' => $blob]],
        ]);

        self::assertSame($one['digest'], $other['digest']);
    }

    /**
     * The two engines return metrics with different types and scales, and a float sum
     * accumulated in a different order can differ in the last bits. Neither is a disagreement
     * about the answer.
     */
    public function testArchiveDigestIgnoresNumericTypeAndScale(): void
    {
        $mysql = ResultFingerprint::ofArchivedReports([
            'numeric' => [
                ['name' => 'nb_visits', 'value' => '15'],
                ['name' => 'revenue', 'value' => '99.900000000000006'],
            ],
            'blob' => [],
        ]);
        $clickhouse = ResultFingerprint::ofArchivedReports([
            'numeric' => [
                ['name' => 'nb_visits', 'value' => 15],
                ['name' => 'revenue', 'value' => 99.9],
            ],
            'blob' => [],
        ]);

        self::assertSame($mysql['digest'], $clickhouse['digest']);
    }

    public function testArchiveDigestChangesWhenAMetricChanges(): void
    {
        $one = ResultFingerprint::ofArchivedReports([
            'numeric' => [['name' => 'nb_visits', 'value' => '15']],
            'blob' => [],
        ]);
        $other = ResultFingerprint::ofArchivedReports([
            'numeric' => [['name' => 'nb_visits', 'value' => '16']],
            'blob' => [],
        ]);

        self::assertNotSame($one['digest'], $other['digest']);
    }

    /**
     * A difference of a hundredth of a cent is still a difference in a revenue total.
     */
    public function testArchiveDigestStillSeesASmallRealDifference(): void
    {
        $one = ResultFingerprint::ofArchivedReports([
            'numeric' => [['name' => 'revenue', 'value' => '10.0001']],
            'blob' => [],
        ]);
        $other = ResultFingerprint::ofArchivedReports([
            'numeric' => [['name' => 'revenue', 'value' => '10.0002']],
            'blob' => [],
        ]);

        self::assertNotSame($one['digest'], $other['digest']);
    }

    public function testArchiveDigestSeesReportContents(): void
    {
        $rows = [['label' => '/news/', 2 => 9], ['label' => '/sport/', 2 => 4]];

        $base = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Actions_actions', 'value' => gzcompress(serialize($rows))]],
        ]);
        $changedValue = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Actions_actions', 'value' => gzcompress(serialize(
                [['label' => '/news/', 2 => 8], ['label' => '/sport/', 2 => 4]]
            ))]],
        ]);
        $changedMembership = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Actions_actions', 'value' => gzcompress(serialize(
                [['label' => '/news/', 2 => 9], ['label' => '/weather/', 2 => 4]]
            ))]],
        ]);

        self::assertNotSame($base['digest'], $changedValue['digest'], 'a changed metric is a changed report');
        self::assertNotSame($base['digest'], $changedMembership['digest'], 'a changed row is a changed report');
    }

    /**
     * Both engines emit the same rows in a different order whenever a metric ties, and a report
     * is sorted again when it is read, so the stored order is an artifact of the aggregation.
     * Comparing it would report every Events report as a disagreement and mean nothing - the
     * same failure mode as the visit count it replaced, in the other direction.
     */
    public function testArchiveDigestIgnoresTheOrderRowsWereStoredIn(): void
    {
        $rows = [['label' => 'category-108', 2 => 1], ['label' => 'category-6', 2 => 1]];

        $one = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Events_name_category', 'value' => gzcompress(serialize($rows))]],
        ]);
        $reordered = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Events_name_category', 'value' => gzcompress(serialize(array_reverse($rows)))]],
        ]);

        self::assertSame($one['digest'], $reordered['digest']);
    }

    /**
     * A report blob nests: the outer value maps subtable id to a STRING that is itself a
     * serialized row set. Comparing those strings as text makes every scalar the two engines
     * render differently look like a disagreement - MySQL writes s:1:"1" where ClickHouse
     * writes i:1 - which is what the whole normalisation exists to absorb. Taken from the bytes
     * the two engines actually wrote for Events_name_category on 2026-08-03.
     */
    public function testArchiveDigestRecursesIntoNestedSerializedSubtables(): void
    {
        $subtable = static function (string $columnValue): string {
            return serialize([
                400 => serialize([
                    [[ 'label' => 'category-108', 1 => 1, 2 => $columnValue ], [], null],
                ]),
            ]);
        };

        $mysql = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Events_name_category', 'value' => gzcompress($subtable('1'))]],
        ]);
        $clickhouse = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Events_name_category', 'value' => gzcompress(serialize([
                400 => serialize([
                    [[ 'label' => 'category-108', 1 => 1, 2 => 1 ], [], null],
                ]),
            ]))]],
        ]);

        self::assertSame($mysql['digest'], $clickhouse['digest']);
    }

    /**
     * A label can legitimately start with a serialization marker. It must stay a label.
     */
    public function testALabelThatLooksLikeSerializedDataIsNotUnserialized(): void
    {
        $one = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Actions_actions', 'value' => gzcompress(serialize(
                [['label' => 'a:1 ratio', 2 => 4]]
            ))]],
        ]);
        $other = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Actions_actions', 'value' => gzcompress(serialize(
                [['label' => 'i:2 ratio', 2 => 4]]
            ))]],
        ]);

        self::assertNotSame($one['digest'], $other['digest']);
    }

    /**
     * Subtable ids are handed out in the order rows happen to be walked, so two engines that
     * agree on every label and every metric still number their subtables differently - and
     * therefore pack them into different _chunk_ blobs. Measured on 2026-08-03: all 200
     * event-category labels matched, every nb_events and nb_visits matched, and the subtable
     * id was the only difference across six "differing" reports.
     */
    public function testSubtableRenumberingIsNotAReportDifference(): void
    {
        $subtable = static function (int $id, string $child): array {
            return [$id => serialize([[['label' => $child, 2 => 7], [], null]])];
        };

        $mysql = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [
                ['name' => 'Events_category_action', 'value' => gzcompress(serialize([
                    [['label' => 'category-10', 2 => 244], [], 24],
                    [['label' => 'category-72', 2 => 242], [], 25],
                ]))],
                ['name' => 'Events_category_action_chunk_0_99', 'value' => gzcompress(serialize(
                    $subtable(24, 'download') + $subtable(25, 'play')
                ))],
            ],
        ]);

        $clickhouse = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [
                ['name' => 'Events_category_action', 'value' => gzcompress(serialize([
                    [['label' => 'category-72', 2 => 242], [], 23],
                    [['label' => 'category-10', 2 => 244], [], 24],
                ]))],
                ['name' => 'Events_category_action_chunk_0_99', 'value' => gzcompress(serialize(
                    $subtable(23, 'play') + $subtable(24, 'download')
                ))],
            ],
        ]);

        self::assertSame($mysql['digest'], $clickhouse['digest']);
    }

    /**
     * The flattening must not make everything equal. A subtable whose contents changed is a
     * changed report even when the ids line up.
     */
    public function testAChangedSubtableIsStillAReportDifference(): void
    {
        $build = static function (string $child, int $events): array {
            return [
                'numeric' => [],
                'blob' => [
                    ['name' => 'Events_category_action', 'value' => gzcompress(serialize([
                        [['label' => 'category-10', 2 => 244], [], 24],
                    ]))],
                    ['name' => 'Events_category_action_chunk_0_99', 'value' => gzcompress(serialize([
                        24 => serialize([[['label' => $child, 2 => $events], [], null]]),
                    ]))],
                ],
            ];
        };

        $base = ResultFingerprint::ofArchivedReports($build('download', 7));
        $changedChild = ResultFingerprint::ofArchivedReports($build('stream', 7));
        $changedMetric = ResultFingerprint::ofArchivedReports($build('download', 8));

        self::assertNotSame($base['digest'], $changedChild['digest']);
        self::assertNotSame($base['digest'], $changedMetric['digest']);
    }

    /**
     * A subtable nothing points at cannot be given a label path, but dropping it would let a
     * report quietly gain or lose one. It is compared by content instead.
     */
    public function testAnUnreferencedSubtableStillCounts(): void
    {
        $root = ['name' => 'Events_category_action', 'value' => gzcompress(serialize([
            [['label' => 'category-10', 2 => 244], [], 24],
        ]))];

        $withOrphan = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [$root, ['name' => 'Events_category_action_chunk_0_99', 'value' => gzcompress(serialize([
                24 => serialize([[['label' => 'download', 2 => 7], [], null]]),
                99 => serialize([[['label' => 'orphan', 2 => 1], [], null]]),
            ]))]],
        ]);
        $withoutOrphan = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [$root, ['name' => 'Events_category_action_chunk_0_99', 'value' => gzcompress(serialize([
                24 => serialize([[['label' => 'download', 2 => 7], [], null]]),
            ]))]],
        ]);

        self::assertNotSame($withOrphan['digest'], $withoutOrphan['digest']);
    }

    /**
     * done flags carry the archive's status, not a report value, and the status can differ
     * legitimately between two runs of the same case.
     */
    public function testArchiveDigestIgnoresDoneFlags(): void
    {
        $withFlag = ResultFingerprint::ofArchivedReports([
            'numeric' => [['name' => 'nb_visits', 'value' => '15'], ['name' => 'done3afbfb51', 'value' => '1']],
            'blob' => [],
        ]);
        $withOtherFlag = ResultFingerprint::ofArchivedReports([
            'numeric' => [['name' => 'nb_visits', 'value' => '15'], ['name' => 'done3afbfb51', 'value' => '5']],
            'blob' => [],
        ]);

        self::assertSame($withFlag['digest'], $withOtherFlag['digest']);
        self::assertSame(1, $withFlag['rows'], 'the flag is not counted as a metric');
    }

    /**
     * A blob that will not uncompress is still compared, as bytes. Skipping it would quietly
     * drop a report out of the comparison while the fingerprint still claimed to be strong.
     */
    public function testAnUnreadableBlobIsComparedAsBytes(): void
    {
        $one = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Actions_actions', 'value' => 'not gzip at all']],
        ]);
        $same = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Actions_actions', 'value' => 'not gzip at all']],
        ]);
        $different = ResultFingerprint::ofArchivedReports([
            'numeric' => [],
            'blob' => [['name' => 'Actions_actions', 'value' => 'not gzip either']],
        ]);

        self::assertSame(ResultFingerprint::STRONG, $one['strength']);
        self::assertSame($one['digest'], $same['digest']);
        self::assertNotSame($one['digest'], $different['digest']);
    }

    /**
     * Nothing read is not agreement. It has to be weak, so the caller falls back rather than
     * reporting two engines as matching because neither wrote anything the reader could find.
     */
    public function testAnArchiveWithNoReadableRowsIsWeak(): void
    {
        $fingerprint = ResultFingerprint::ofArchivedReports(['numeric' => [], 'blob' => []]);

        self::assertSame(ResultFingerprint::WEAK, $fingerprint['strength']);
        self::assertSame('no archive rows found', $fingerprint['summary']);
    }

    public function testAnythingElseIsMarkedWeak(): void
    {
        $fingerprint = ResultFingerprint::of([['label' => 'a', 'nb_hits' => 1]]);

        self::assertSame(ResultFingerprint::WEAK, $fingerprint['strength']);
        self::assertSame(1, $fingerprint['rows']);
    }

    public function testArchiveLogIsFingerprintedOnTheVisitCountsItReports(): void
    {
        $log = "Archived website id 1, period = day, date = 2026-08-03, segment = '', 3069 visits found.\n"
            . "Archived website id 1, period = week, date = 2026-08-03, segment = '', 21000 visits found.\n";

        $fingerprint = ResultFingerprint::ofArchiveLog($log);

        self::assertNotNull($fingerprint);
        self::assertSame(ResultFingerprint::STRONG, $fingerprint['strength']);
        self::assertSame(2, $fingerprint['rows']);
        self::assertSame('visits:3069,21000', $fingerprint['digest']);
    }

    public function testArchiveLogWithNothingArchivedHasNoFingerprint(): void
    {
        self::assertNull(ResultFingerprint::ofArchiveLog('Nothing to do.'));
    }

    /**
     * The most likely way for a whole run to be wrong while looking healthy: needles that do not
     * occur in the data match nothing, an empty result set is fast on both engines, and every
     * segmented case comes out quick with a spectacular ratio.
     *
     * @dataProvider emptyResultProvider
     */
    public function testAnEmptyResultIsRecognised(bool $expected, array $decoded): void
    {
        self::assertSame($expected, ResultFingerprint::isEmpty(ResultFingerprint::of($decoded)));
    }

    public function emptyResultProvider(): array
    {
        return [
            'no visits in the log' => [true, []],
            'archived nothing' => [true, ['nb_visits' => 0]],
            'one visit' => [false, [['idVisit' => '1']]],
            'archived something' => [false, ['nb_visits' => 1]],
        ];
    }

    public function testAnArchiveLogThatFoundNoVisitsCountsAsEmpty(): void
    {
        $fingerprint = ResultFingerprint::ofArchiveLog("segment = 'x', 0 visits found.");

        self::assertNotNull($fingerprint);
        self::assertTrue(ResultFingerprint::isEmpty($fingerprint));
    }

    public function testApiResponseIsFoundEvenWhenLogLinesPrecedeIt(): void
    {
        $output = "WARNING: something\nNOTICE: something else\n" . '{"nb_visits":42}';

        self::assertSame(['nb_visits' => 42], ResultFingerprint::decodeApiOutput($output));
    }

    public function testUnparsableOutputDecodesToNull(): void
    {
        self::assertNull(ResultFingerprint::decodeApiOutput("Fatal error: nope\n"));
        self::assertNull(ResultFingerprint::decodeApiOutput('   '));
    }
}
