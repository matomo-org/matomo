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
use Piwik\Plugins\CoreConsole\ClickhouseBench\BenchCase;
use Piwik\Plugins\CoreConsole\ClickhouseBench\Engine;
use Piwik\Plugins\CoreConsole\ClickhouseBench\Reporter;
use Piwik\Plugins\CoreConsole\ClickhouseBench\ResultFingerprint;
use Piwik\Plugins\CoreConsole\ClickhouseBench\RunResult;

/**
 * @group CoreConsole
 * @group ClickhouseBench
 * @group Plugins
 */
class ReporterTest extends TestCase
{
    public function testMedianOfOddCountIsTheMiddleValue(): void
    {
        self::assertSame(5.0, Reporter::median([9.0, 1.0, 5.0]));
    }

    public function testMedianOfEvenCountAveragesTheMiddlePair(): void
    {
        self::assertSame(3.0, Reporter::median([1.0, 2.0, 4.0, 8.0]));
    }

    public function testMedianOfNothingIsNull(): void
    {
        self::assertNull(Reporter::median([]));
    }

    /**
     * The median has to be resistant to one slow iteration, which is the whole reason it is used
     * instead of a mean. On a shared box one scheduling stall is normal and a mean carries it
     * into the published number.
     */
    public function testMedianIgnoresASingleOutlier(): void
    {
        self::assertSame(100.0, Reporter::median([100.0, 98.0, 102.0, 99.0, 60000.0]));
    }

    public function testFormatMsSwitchesUnitsAtSecondsAndMinutes(): void
    {
        self::assertSame('388 ms', Reporter::formatMs(388.0));
        self::assertSame('2.53 s', Reporter::formatMs(2530.0));
        self::assertSame('1.1 min', Reporter::formatMs(66000.0));
        self::assertSame('-', Reporter::formatMs(null));
    }

    public function testWarmupsAreExcludedFromTheSummary(): void
    {
        $case = $this->apiCase();
        $results = [
            new RunResult('mysql', $case, 1, true, true, 9999.0),
            new RunResult('mysql', $case, 1, false, true, 100.0),
            new RunResult('mysql', $case, 2, false, true, 120.0),
        ];

        $summary = (new Reporter())->summarise($results, [Engine::fromKey('mysql')]);

        self::assertSame(2, $summary[0]['engines']['mysql']['n']);
        self::assertSame(110.0, $summary[0]['engines']['mysql']['median']);
    }

    public function testRatioNamesTheFasterEngine(): void
    {
        $case = $this->apiCase();
        $results = [
            new RunResult('mysql', $case, 1, false, true, 400.0),
            new RunResult('clickhouse', $case, 1, false, true, 100.0),
        ];

        $summary = (new Reporter())->summarise($results, Engine::all());

        self::assertSame(4.0, $summary[0]['ratio']);
        self::assertSame('clickhouse', $summary[0]['faster']);
    }

    /**
     * The archive time from ArchivingMetrics is preferred over the wall clock, because the wall
     * clock also contains bootstrap and invalidation.
     */
    public function testArchiveTimeIsPreferredOverWallClock(): void
    {
        $case = $this->archiveCase();
        $results = [new RunResult('mysql', $case, 1, false, true, 5000.0, null, 1200.0, 1100.0, 1)];

        $summary = (new Reporter())->summarise($results, [Engine::fromKey('mysql')]);

        self::assertSame(1200.0, $summary[0]['engines']['mysql']['median']);
        self::assertSame('archiving_metrics', $summary[0]['engines']['mysql']['source']);
    }

    public function testMixedTimingSourcesAreFlagged(): void
    {
        $case = $this->archiveCase();
        $results = [
            new RunResult('mysql', $case, 1, false, true, 5000.0, null, 1200.0, 1100.0, 1),
            new RunResult('mysql', $case, 2, false, true, 5000.0),
        ];

        $summary = (new Reporter())->summarise($results, [Engine::fromKey('mysql')]);

        self::assertSame('mixed', $summary[0]['engines']['mysql']['source']);
        self::assertStringContainsString(
            'mixes archive time with wall clock',
            implode(' ', (new Reporter())->caveats($summary))
        );
    }

    public function testDisagreeingStrongResultsAreReportedAsDifferent(): void
    {
        $case = $this->apiCase();
        $results = [
            new RunResult('mysql', $case, 1, false, true, 100.0, $this->fingerprint('aaa', ResultFingerprint::STRONG)),
            new RunResult('clickhouse', $case, 1, false, true, 100.0, $this->fingerprint('bbb', ResultFingerprint::STRONG)),
        ];

        $summary = (new Reporter())->summarise($results, Engine::all());

        self::assertSame('DIFFERENT', $summary[0]['agreement']);
        self::assertStringContainsString('DIFFERENT results', implode(' ', (new Reporter())->caveats($summary)));
    }

    /**
     * A weak digest hashes the whole payload, and the two engines are expected to disagree there
     * for reasons that are not defects - the replicated copy flattens NULL, maps tinyint to Bool
     * and formats DECIMAL and DATETIME differently. Reporting that as a disagreement would cry
     * wolf on every single row.
     */
    public function testDisagreeingWeakResultsAreReportedAsUnverifiedNotAsADifference(): void
    {
        $case = $this->apiCase();
        $results = [
            new RunResult('mysql', $case, 1, false, true, 100.0, $this->fingerprint('aaa', ResultFingerprint::WEAK)),
            new RunResult('clickhouse', $case, 1, false, true, 100.0, $this->fingerprint('bbb', ResultFingerprint::WEAK)),
        ];

        $summary = (new Reporter())->summarise($results, Engine::all());

        self::assertSame('unverified', $summary[0]['agreement']);
    }

    public function testAnUnconvergedCellIsCalledOut(): void
    {
        $case = $this->apiCase();
        $results = [
            new RunResult('clickhouse', $case, 1, false, true, 6170.0),
            new RunResult('clickhouse', $case, 2, false, true, 9990.0),
            new RunResult('clickhouse', $case, 3, false, true, 20000.0),
        ];

        $summary = (new Reporter())->summarise($results, [Engine::fromKey('clickhouse')]);
        $caveats = (new Reporter())->caveats($summary);

        self::assertGreaterThan(Reporter::SPREAD_WARNING, $summary[0]['engines']['clickhouse']['spread']);
        self::assertStringContainsString('Not converged', implode(' ', $caveats));
    }

    public function testAnEngineWithNoSuccessfulRunReportsItsError(): void
    {
        $case = $this->apiCase();
        $results = [
            new RunResult('clickhouse', $case, 1, false, false, 10.0, null, null, null, 0, 0, 'API error: boom'),
        ];

        $summary = (new Reporter())->summarise($results, [Engine::fromKey('clickhouse')]);

        self::assertSame(0, $summary[0]['engines']['clickhouse']['n']);
        self::assertNull($summary[0]['ratio']);
        self::assertStringContainsString('API error: boom', implode(' ', (new Reporter())->caveats($summary)));
    }

    /**
     * @return array{strength: string, rows: ?int, digest: string, summary: string}
     */
    private function fingerprint(string $digest, string $strength): array
    {
        return ['strength' => $strength, 'rows' => 1, 'digest' => $digest, 'summary' => '1 row'];
    }

    private function apiCase(): BenchCase
    {
        return BenchCase::api('v1', 'Visits Log', 1, 'day', '2026-08-03', '', 'none', 'Live.getLastVisitsDetails');
    }

    private function archiveCase(): BenchCase
    {
        return BenchCase::archive('a1', 'Archive', 1, 'day', '2026-08-03', '', 'none');
    }
}
