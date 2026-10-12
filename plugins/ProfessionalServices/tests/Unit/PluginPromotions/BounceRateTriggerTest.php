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
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ArchivedReportReader;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\BounceRateTrigger;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class BounceRateTriggerTest extends TestCase
{
    private BounceRateTrigger $trigger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trigger = new BounceRateTrigger(
            $this->createMock(ArchivedReportReader::class),
            $this->createMock(ReportPeriod::class),
            $this->createMock(DailyTriggerCache::class)
        );
    }

    /**
     * @dataProvider getThresholdCases
     */
    public function testAppliesBothThresholds(int $entryVisits, int $bounceCount, bool $expectedToQualify): void
    {
        $entryPages = $this->makeReport([
            ['label' => 'Pricing', 'nb_visits' => $entryVisits, 'entry_nb_visits' => $entryVisits, 'entry_bounce_count' => $bounceCount],
        ]);

        $qualifying = $this->trigger->findQualifyingPage($entryPages);

        $this->assertSame($expectedToQualify, null !== $qualifying);
    }

    /**
     * @return array<string, array{int, int, bool}>
     */
    public function getThresholdCases(): array
    {
        return [
            'just below the visit threshold' => [199, 140, false],
            'at the visit threshold, below the bounce rate' => [200, 109, false],
            'at both thresholds' => [200, 110, true],
            'just below the bounce rate' => [1000, 549, false],
            'at the bounce rate' => [1000, 550, true],
            'above both thresholds' => [1000, 700, true],
            'no visits at all' => [0, 0, false],
        ];
    }

    public function testPicksTheQualifyingPageWithTheMostVisits(): void
    {
        // Ordered by entry visits descending, the way the report is requested.
        $entryPages = $this->makeReport([
            ['label' => 'Product', 'nb_visits' => 900, 'entry_nb_visits' => 900, 'entry_bounce_count' => 480],
            ['label' => 'Download', 'nb_visits' => 720, 'entry_nb_visits' => 720, 'entry_bounce_count' => 490],
            ['label' => 'Pricing', 'nb_visits' => 450, 'entry_nb_visits' => 450, 'entry_bounce_count' => 324],
        ]);

        $qualifying = $this->trigger->findQualifyingPage($entryPages);

        // /product has more visits but does not bounce often enough, /pricing bounces more
        // but has fewer visits.
        $this->assertSame('Download', $qualifying['title']);
        $this->assertSame(720, $qualifying['entryVisits']);
    }

    /**
     * The copy names the page, and a title is what reads there.
     */
    public function testReportsThePageTitle(): void
    {
        $pages = $this->makeReport([
            ['label' => 'Pricing and plans', 'nb_visits' => 500, 'entry_nb_visits' => 500, 'entry_bounce_count' => 400],
        ]);

        $this->assertSame('Pricing and plans', $this->trigger->findQualifyingPage($pages)['title']);
    }

    /**
     * A page title that was never an entry page carries no entry metrics at all: the
     * report gives false for both, not zero, so it must be skipped rather than divided by.
     */
    public function testAPageThatWasNeverAnEntryPageIsSkipped(): void
    {
        $pages = $this->makeReport([
            ['label' => 'Features', 'nb_visits' => 900, 'entry_nb_visits' => false, 'entry_bounce_count' => false],
            ['label' => 'Pricing', 'nb_visits' => 600, 'entry_nb_visits' => 600, 'entry_bounce_count' => 500],
        ]);

        $qualifying = $this->trigger->findQualifyingPage($pages);

        $this->assertSame('Pricing', $qualifying['title']);
    }

    public function testReturnsNothingForAnEmptyReport(): void
    {
        $this->assertNull($this->trigger->findQualifyingPage(new DataTable()));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function makeReport(array $rows): DataTable
    {
        $dataTable = new DataTable();
        $dataTable->addRowsFromSimpleArray($rows);

        return $dataTable;
    }
}
