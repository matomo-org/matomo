<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ArchivedReportReader;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\CampaignConversionsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\ManyPagesTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MediaOutlinksTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultipleConversionChannelsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\MultiplePageVisitsTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\SlowPageTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\WeeklyGoalMetrics;

/**
 * The row picking and counting each report driven promotion does, separated from the
 * report requests that feed it.
 *
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class ReportCountingTriggersTest extends TestCase
{
    public function testActivePagesAreCountedUntilTheVisitFloorIsCrossed(): void
    {
        // Ordered by visits descending, the way the report is requested.
        $pages = $this->makeReport([
            ['label' => '/a', 'nb_visits' => 400],
            ['label' => '/b', 'nb_visits' => 120],
            ['label' => '/c', 'nb_visits' => 100],
            ['label' => '/d', 'nb_visits' => 99],
            ['label' => '/e', 'nb_visits' => 500],
        ]);

        // Counting stops at /d, so the out of order /e is never reached: the report is
        // sorted, and a row below the floor means every later row is too.
        $this->assertSame(3, $this->manyPages()->countActivePages($pages));
    }

    public function testOnlyMediaHostsCountTowardsOutlinkClicks(): void
    {
        $outlinks = $this->makeReport([
            ['label' => 'youtube.com/watch?v=1', 'nb_hits' => 300],
            ['label' => 'youtu.be/abc', 'nb_hits' => 250],
            ['label' => 'example.org/partners', 'nb_hits' => 900],
        ]);

        $this->assertSame(550, $this->mediaOutlinks()->countMediaClicks($outlinks));
    }

    /**
     * The bucket's lower bound is read from its segment metadata, because by the time the
     * report is returned the label has been translated for display and carries no number.
     *
     * @dataProvider getPageBucketCases
     */
    public function testVisitsAreCountedFromTheBucketLowerBound(string $segment, bool $expectedToCount): void
    {
        $report = $this->makeSegmentedReport([[$segment, 70]]);

        $counted = $this->multiplePageVisits()->countVisitsOverPageThreshold($report);

        $this->assertSame($expectedToCount ? 70 : 0, $counted);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public function getPageBucketCases(): array
    {
        return [
            'one page' => ['actions==1', false],
            'three pages' => ['actions==3', false],
            'four pages, exactly the threshold' => ['actions==4', true],
            'a five page bucket' => ['actions==5', true],
            'the six to seven bucket' => ['actions>=6;actions<=7', true],
            'the eleven to fourteen bucket' => ['actions>=11;actions<=14', true],
            'the open ended bucket' => ['actions>=21', true],
            // A row with no segment cannot be placed, so it is left out rather than guessed at.
            'no segment at all' => ['', false],
        ];
    }

    public function testEveryBucketOverTheThresholdIsAddedUp(): void
    {
        $report = $this->makeSegmentedReport([
            ['actions==1', 4000],
            ['actions==3', 900],
            ['actions==4', 200],
            ['actions>=6;actions<=7', 180],
            ['actions>=21', 120],
        ]);

        $this->assertSame(500, $this->multiplePageVisits()->countVisitsOverPageThreshold($report));
    }

    /**
     * @param array<int, array{string, int}> $rows segment metadata and visits
     */
    private function makeSegmentedReport(array $rows): DataTable
    {
        $report = new DataTable();

        foreach ($rows as [$segment, $visits]) {
            $row = new Row([Row::COLUMNS => ['label' => 'VisitorInterest_NPages', 'nb_visits' => $visits]]);
            $row->setMetadata('segment', $segment);
            $report->addRow($row);
        }

        return $report;
    }

    public function testTheBestConvertingCampaignWinsRegardlessOfItsVisits(): void
    {
        // The report is ordered by visits, so the best converting campaign can be anywhere.
        $campaigns = $this->makeReport([
            ['label' => 'summer-sale', 'nb_visits' => 9000, 'nb_conversions' => 210],
            ['label' => 'newsletter', 'nb_visits' => 400, 'nb_conversions' => 460],
            ['label' => 'affiliates', 'nb_visits' => 2000, 'nb_conversions' => 150],
        ]);

        $campaign = $this->campaignConversions()->findBestConvertingCampaign($campaigns);

        $this->assertSame('newsletter', $campaign['name']);
        $this->assertSame(460, $campaign['count']);
    }

    public function testACampaignBelowTheConversionFloorNeverWins(): void
    {
        $campaigns = $this->makeReport([['label' => 'summer-sale', 'nb_visits' => 9000, 'nb_conversions' => 199]]);

        $this->assertNull($this->campaignConversions()->findBestConvertingCampaign($campaigns));
    }

    /**
     * @dataProvider getChannelShareCases
     */
    public function testOnlyChannelsCarryingARealShareAreCounted(array $conversionsPerType, int $expected): void
    {
        $rows = [];
        foreach ($conversionsPerType as $i => $conversions) {
            $rows[] = ['label' => 'type' . $i, 'nb_conversions' => $conversions];
        }

        $counted = $this->multipleConversionChannels()->countChannelsOverShare(
            $this->makeReport($rows),
            (int) array_sum($conversionsPerType)
        );

        $this->assertSame($expected, $counted);
    }

    /**
     * @return array<string, array{array<int, int>, int}>
     */
    public function getChannelShareCases(): array
    {
        return [
            'three even channels' => [[200, 200, 200], 3],
            // 10% exactly counts; the long tail below it does not.
            'one at exactly a tenth' => [[500, 400, 100], 3],
            'a dominant channel and two slivers' => [[900, 60, 40], 1],
            'two channels only' => [[500, 500], 2],
        ];
    }

    public function testNoChannelIsCountedWhenAGoalHasNoConversions(): void
    {
        $report = $this->makeReport([['label' => 'direct', 'nb_conversions' => 0]]);

        $this->assertSame(0, $this->multipleConversionChannels()->countChannelsOverShare($report, 0));
    }

    public function testTheBusiestSlowPageWins(): void
    {
        $pages = $this->makeReport([
            ['label' => '/fast', 'nb_hits' => 9000, 'avg_page_load_time' => 0.8],
            ['label' => '/slow', 'nb_hits' => 4000, 'avg_page_load_time' => 4.2],
            ['label' => '/slower', 'nb_hits' => 600, 'avg_page_load_time' => 9.0],
        ]);

        $page = $this->slowPage()->findSlowestBusyPage($pages);

        // /fast is busiest but quick, and /slower is slowest but quieter: the promotion
        // names the busiest page that is also slow.
        $this->assertSame('/slow', $page['url']);
        $this->assertSame(4000, $page['count']);
        $this->assertSame(4.2, $page['loadTime']);
    }

    /**
     * @dataProvider getLoadTimeCases
     */
    public function testTheLoadTimeFloorApplies(float $loadTime, bool $expectedToQualify): void
    {
        $pages = $this->makeReport([['label' => '/p', 'nb_hits' => 800, 'avg_page_load_time' => $loadTime]]);

        $this->assertSame($expectedToQualify, null !== $this->slowPage()->findSlowestBusyPage($pages));
    }

    /**
     * @return array<string, array{float, bool}>
     */
    public function getLoadTimeCases(): array
    {
        return [
            'just below three seconds' => [2.9, false],
            'exactly three seconds' => [3.0, true],
            'well over three seconds' => [7.5, true],
            // A website that sends no performance metrics reports nothing here.
            'no measurement at all' => [0.0, false],
        ];
    }

    public function testAPageBelowThePageviewFloorIsNeverNamed(): void
    {
        $pages = $this->makeReport([['label' => '/p', 'nb_hits' => 499, 'avg_page_load_time' => 9.0]]);

        $this->assertNull($this->slowPage()->findSlowestBusyPage($pages));
    }

    private function manyPages(): ManyPagesTrigger
    {
        return new ManyPagesTrigger(...$this->reportDependencies());
    }

    private function mediaOutlinks(): MediaOutlinksTrigger
    {
        return new MediaOutlinksTrigger(...$this->reportDependencies());
    }

    private function multiplePageVisits(): MultiplePageVisitsTrigger
    {
        return new MultiplePageVisitsTrigger(...$this->reportDependencies());
    }

    private function campaignConversions(): CampaignConversionsTrigger
    {
        return new CampaignConversionsTrigger(...$this->reportDependencies());
    }

    private function slowPage(): SlowPageTrigger
    {
        return new SlowPageTrigger(...$this->reportDependencies());
    }

    private function multipleConversionChannels(): MultipleConversionChannelsTrigger
    {
        return new MultipleConversionChannelsTrigger(
            $this->createMock(WeeklyGoalMetrics::class),
            ...$this->reportDependencies()
        );
    }

    /**
     * @return array{ArchivedReportReader, ReportPeriod, DailyTriggerCache}
     */
    private function reportDependencies(): array
    {
        return [
            $this->createMock(ArchivedReportReader::class),
            $this->createMock(ReportPeriod::class),
            $this->createMock(DailyTriggerCache::class),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function makeReport(array $rows): DataTable
    {
        $report = new DataTable();

        foreach ($rows as $columns) {
            $report->addRow(new Row([Row::COLUMNS => $columns]));
        }

        return $report;
    }
}
