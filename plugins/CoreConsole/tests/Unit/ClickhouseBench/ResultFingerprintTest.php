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

    public function testAnArchivingResultIsFingerprintedOnItsVisitCount(): void
    {
        $first = ResultFingerprint::of(['idarchives' => [11], 'nb_visits' => 3069]);
        $second = ResultFingerprint::of(['idarchives' => [4096], 'nb_visits' => 3069]);

        self::assertSame(ResultFingerprint::STRONG, $first['strength']);
        self::assertSame($first['digest'], $second['digest'], 'a new archive id is not a new answer');
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
