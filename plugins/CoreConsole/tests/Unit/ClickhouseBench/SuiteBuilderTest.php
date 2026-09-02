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
     * @param string[] $segmentKeys
     * @param array<string, mixed> $overrides
     * @return BenchCase[]
     */
    private function build(array $segmentKeys, array $overrides = []): array
    {
        $needles = SuiteBuilder::defaultNeedles();

        return (new SuiteBuilder())->build($overrides + [
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
        ]);
    }
}
