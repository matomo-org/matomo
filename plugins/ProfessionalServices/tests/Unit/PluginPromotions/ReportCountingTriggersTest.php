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
            $rows[] = ['label' => 'type' . $i, 'goal_1_nb_conversions' => $conversions];
        }

        $counted = $this->multipleConversionChannels()->countChannelsOverShare(
            $this->makeReport($rows),
            1,
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
        $report = $this->makeReport([['label' => 'direct', 'goal_1_nb_conversions' => 0]]);

        $this->assertSame(0, $this->multipleConversionChannels()->countChannelsOverShare($report, 1, 0));
    }

    /**
     * On a site with more than one goal the row's plain `nb_conversions` is the total
     * across every goal, while the goal being promoted has a column of its own. Reading
     * the plain one made every channel look far bigger than it was, and the promotion
     * fired for sites where no channel carried a tenth of *this* goal.
     */
    public function testChannelShareIgnoresConversionsBelongingToOtherGoals(): void
    {
        // Goal 1 took 300 conversions, spread 40/30/30 - no channel reaches a tenth of the
        // 3000 the site's goals took together, but two comfortably clear a tenth of 300.
        $report = $this->makeReport([
            ['label' => 'direct', 'nb_conversions' => 1500, 'goal_1_nb_conversions' => 150],
            ['label' => 'search', 'nb_conversions' => 1000, 'goal_1_nb_conversions' => 120],
            ['label' => 'social', 'nb_conversions' => 500, 'goal_1_nb_conversions' => 30],
        ]);

        $this->assertSame(3, $this->multipleConversionChannels()->countChannelsOverShare($report, 1, 300));
    }

    /**
     * The other half of the same mistake: the all-goals total made thin channels look like
     * contributors. Goal 2 barely converted, and only one channel carried it.
     */
    public function testAChannelIsNotCountedOnAnotherGoalsConversions(): void
    {
        $report = $this->makeReport([
            ['label' => 'direct', 'nb_conversions' => 900, 'goal_2_nb_conversions' => 95],
            ['label' => 'search', 'nb_conversions' => 900, 'goal_2_nb_conversions' => 3],
            ['label' => 'social', 'nb_conversions' => 900, 'goal_2_nb_conversions' => 2],
        ]);

        // Reading the plain column would have counted all three.
        $this->assertSame(1, $this->multipleConversionChannels()->countChannelsOverShare($report, 2, 100));
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
