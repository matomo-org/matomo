<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\tests\Unit\ClickhouseBench;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreConsole\ClickhouseBench\BenchCase;
use Piwik\Plugins\CoreConsole\ClickhouseBench\SuiteBuilder;

/**
 * @group CoreConsole
 * @group ClickhouseBench
 * @group Plugins
 */
class SuiteBuilderTest extends TestCase
{
    public function testCompoundSegmentUsesTwoDifferentActionDimensions(): void
    {
        $segments = SuiteBuilder::defaultSegments(SuiteBuilder::defaultNeedles());

        // pageUrl and pageTitle are different dimensions, so Matomo compiles them to two
        // separate log_action joins rather than sharing one. That is the shape the benchmark is
        // for; collapsing them to one dimension would quietly measure something cheaper.
        self::assertSame(
            'pageUrl=@/news/;pageTitle=@Budget;countryCode==de;deviceType==desktop',
            $segments['compound']
        );
    }

    public function testNegatedSegmentIsTheCompoundOnePlusExactlyOneComponent(): void
    {
        $segments = SuiteBuilder::defaultSegments(SuiteBuilder::defaultNeedles());

        self::assertSame($segments['compound'] . ';pageUrl!@/sport/', $segments['negated']);
    }

    public function testNeedlesAreSubstitutedIntoEverySegment(): void
    {
        $needles = SuiteBuilder::defaultNeedles();
        $needles['url'] = '/blog/';
        $needles['country'] = 'nz';

        $segments = SuiteBuilder::defaultSegments($needles);

        self::assertStringContainsString('pageUrl=@/blog/', $segments['compound']);
        self::assertStringContainsString('countryCode==nz', $segments['compound']);
        self::assertStringContainsString('pageUrl=@/blog/', $segments['ecommerce']);
    }

    /**
     * The ids match the standalone SQL benchmark's file names on purpose. That is what makes a
     * number from this harness comparable to a number from there.
     */
    public function testCaseIdsMatchTheSqlBenchmarkNaming(): void
    {
        $cases = $this->build(['none', 'compound', 'negated', 'conversion', 'ecommerce']);

        self::assertSame(
            ['v1', 'a1', 'v1s', 'a1s', 'v1n', 'a1n', 'v1c', 'a1c', 'v1e', 'a1e'],
            array_map(static fn(BenchCase $case): string => $case->getId(), $cases)
        );
    }

    public function testSuiteCanBeNarrowedToOneGroup(): void
    {
        $cases = $this->build(['none', 'compound'], ['groups' => [BenchCase::GROUP_ARCHIVE]]);

        self::assertSame(['a1', 'a1s'], array_map(static fn(BenchCase $case): string => $case->getId(), $cases));
    }

    public function testTransitionsCasesAreSkippedWithoutAPageUrl(): void
    {
        $cases = $this->build(['none']);

        self::assertSame(['v1', 'a1'], array_map(static fn(BenchCase $case): string => $case->getId(), $cases));
    }

    /**
     * Transitions evaluates its action-scope segment components against the single page the
     * report is pinned to, so the title needle has to match that page or the report is empty.
     * The suite swaps in a needle chosen for that, and getting this wrong measures an empty
     * result on both engines and looks like a fast case.
     */
    public function testTransitionsSegmentUsesItsOwnTitleNeedle(): void
    {
        $cases = $this->build(['compound'], ['transitionsPageUrl' => 'https://example.org/city']);

        $transitions = array_values(array_filter(
            $cases,
            static fn(BenchCase $case): bool => $case->getId() === 't1s'
        ));

        self::assertCount(1, $transitions);
        self::assertStringContainsString('pageTitle=@City', $transitions[0]->getSegment());
        self::assertStringNotContainsString('pageTitle=@Budget', $transitions[0]->getSegment());
    }

    public function testUnknownSegmentIsRejectedRatherThanSilentlySkipped(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown segment "nope"');

        $this->build(['nope']);
    }

    public function testFilterAcceptsGlobs(): void
    {
        $cases = $this->build(['none', 'compound', 'negated']);

        $filtered = SuiteBuilder::filter($cases, ['v1*']);

        self::assertSame(['v1', 'v1s', 'v1n'], array_map(static fn(BenchCase $case): string => $case->getId(), $filtered));
    }

    public function testFilterWithNoPatternsKeepsEverything(): void
    {
        $cases = $this->build(['none']);

        self::assertCount(count($cases), SuiteBuilder::filter($cases, []));
    }

    /**
     * The live cases ramp; archiving does not. Matomo archives days from the logs and
     * aggregates longer periods from those day archives, so a range archive would measure
     * aggregation rather than the log queries the engines are being compared on.
     */
    public function testLiveCasesRunOncePerWindowAndArchivingStaysOnTheGivenPeriod(): void
    {
        $cases = $this->build(['none'], [
            'liveWindows' => SuiteBuilder::DEFAULT_LIVE_WINDOWS,
            'transitionsPageUrl' => 'https://example.org/city',
        ]);

        self::assertSame(
            [
                'v1', 'v1-7d', 'v1-30d', 'v1-365d',
                't1', 't1-7d', 't1-30d', 't1-365d',
                'a1',
            ],
            array_map(static fn(BenchCase $case): string => $case->getId(), $cases)
        );

        $archive = $this->case($cases, 'a1');
        self::assertSame('day', $archive->getPeriod());
        self::assertSame('2026-08-03', $archive->getDate());
    }

    public function testTheDefaultRampIsTheOneTheStandaloneBenchmarkSweeps(): void
    {
        self::assertSame([1, 7, 30, 365], SuiteBuilder::DEFAULT_LIVE_WINDOWS);

        // Nothing passed at all must reach the same ramp, not silently fall back to one day.
        $cases = (new SuiteBuilder())->build($this->options(['none'], []));

        self::assertSame(
            ['v1', 'v1-7d', 'v1-30d', 'v1-365d', 'a1'],
            array_map(static fn(BenchCase $case): string => $case->getId(), $cases)
        );
    }

    /**
     * Windows END on the anchor and grow backwards. Forwards from a mid-corpus anchor, a long
     * window runs off the end of the data and measures a half-empty window at full price -
     * which reads as a fast engine rather than as missing data.
     */
    public function testWindowsGrowBackwardsFromTheAnchorDay(): void
    {
        $cases = $this->build(['none'], ['liveWindows' => [1, 7, 30, 365]]);

        self::assertSame(['day', '2026-08-03'], $this->window($cases, 'v1'));
        self::assertSame(['range', '2026-07-28,2026-08-03'], $this->window($cases, 'v1-7d'));
        self::assertSame(['range', '2026-07-05,2026-08-03'], $this->window($cases, 'v1-30d'));
        self::assertSame(['range', '2025-08-04,2026-08-03'], $this->window($cases, 'v1-365d'));
    }

    /**
     * v1 has to stay the request every published number so far was measured with, which is a
     * day - not a one-day range that happens to cover the same visits.
     */
    public function testTheOneDayWindowKeepsTheIdAndThePeriodItAlwaysHad(): void
    {
        $cases = $this->build(['compound'], ['liveWindows' => [1, 7]]);

        self::assertSame(['v1s', 'v1s-7d', 'a1s'], array_map(
            static fn(BenchCase $case): string => $case->getId(),
            $cases
        ));
        self::assertSame(['day', '2026-08-03'], $this->window($cases, 'v1s'));
    }

    public function testWindowLabelsAreReportedPerCase(): void
    {
        $cases = $this->build(['none'], ['liveWindows' => [1, 30]]);

        self::assertSame('1d', $this->case($cases, 'v1')->getWindowLabel());
        self::assertSame('30d', $this->case($cases, 'v1-30d')->getWindowLabel());
        self::assertSame('1d', $this->case($cases, 'a1')->getWindowLabel());
    }

    public function testADateRangeAnchorsOnItsLastDay(): void
    {
        $cases = $this->build(['none'], [
            'liveWindows' => [1, 7],
            'date' => '2026-05-05,2026-08-31',
        ]);

        self::assertSame(['day', '2026-08-31'], $this->window($cases, 'v1'));
        self::assertSame(['range', '2026-08-25,2026-08-31'], $this->window($cases, 'v1-7d'));
    }

    public function testARampGivenTwiceOrOutOfOrderStillProducesOneCasePerWindowShortestFirst(): void
    {
        $cases = $this->build(['none'], ['liveWindows' => [365, 7, 7, 1]]);

        self::assertSame(['v1', 'v1-7d', 'v1-365d', 'a1'], array_map(
            static fn(BenchCase $case): string => $case->getId(),
            $cases
        ));
    }

    /**
     * The standalone benchmark's --sweep takes the d suffix, so it is what anyone coming from
     * it will type.
     */
    public function testWindowsMayBeSpeltWithTheDaySuffix(): void
    {
        $cases = $this->build(['none'], ['liveWindows' => ['1d', '7d']]);

        self::assertSame(['range', '2026-07-28,2026-08-03'], $this->window($cases, 'v1-7d'));
    }

    public function testAnEmptyRampRunsTheLiveCasesOnThePeriodAndDateAsGiven(): void
    {
        $cases = $this->build(['none'], [
            'liveWindows' => [],
            'period' => 'month',
        ]);

        self::assertSame(['v1', 'a1'], array_map(
            static fn(BenchCase $case): string => $case->getId(),
            $cases
        ));
        self::assertSame(['month', '2026-08-03'], $this->window($cases, 'v1'));
    }

    public function testAnUnusableWindowIsRejectedRatherThanSilentlySkipped(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A live window is a whole number of days');

        $this->build(['none'], ['liveWindows' => [7, 'banana']]);
    }

    public function testAZeroDayWindowIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->build(['none'], ['liveWindows' => [0]]);
    }

    /**
     * An anchor that is not a day cannot be grown backwards from. Falling back to today would
     * still produce a full table of numbers, measured somewhere nobody asked for.
     */
    public function testAnAnchorThatIsNotADateIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('concrete anchor day');

        $this->build(['none'], ['liveWindows' => [7], 'date' => 'last7']);
    }

    /**
     * @param BenchCase[] $cases
     * @return array{0: string, 1: string} period and date
     */
    private function window(array $cases, string $id): array
    {
        $case = $this->case($cases, $id);

        return [$case->getPeriod(), $case->getDate()];
    }

    /**
     * @param BenchCase[] $cases
     */
    private function case(array $cases, string $id): BenchCase
    {
        foreach ($cases as $case) {
            if ($case->getId() === $id) {
                return $case;
            }
        }

        self::fail(sprintf('No case "%s" was built. Built: %s.', $id, implode(', ', array_map(
            static fn(BenchCase $built): string => $built->getId(),
            $cases
        ))));
    }

    /**
     * @param string[] $segmentKeys
     * @param array<string, mixed> $overrides
     * @return BenchCase[]
     */
    private function build(array $segmentKeys, array $overrides = []): array
    {
        // One window unless the test says otherwise, so the tests about segments and groups
        // assert on segments and groups. The ramp has its own tests.
        return (new SuiteBuilder())->build(
            $this->options($segmentKeys, $overrides + ['liveWindows' => [1]])
        );
    }

    /**
     * @param string[] $segmentKeys
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function options(array $segmentKeys, array $overrides): array
    {
        $needles = SuiteBuilder::defaultNeedles();

        return $overrides + [
            'idSite' => 1,
            'period' => 'day',
            'date' => '2026-08-03',
            'groups' => [BenchCase::GROUP_API, BenchCase::GROUP_ARCHIVE],
            'segments' => SuiteBuilder::defaultSegments($needles),
            'segmentKeys' => $segmentKeys,
            'liveLimit' => 100,
            'archivePlugin' => '',
            'transitionsPageUrl' => '',
            'needles' => $needles,
        ];
    }
}
