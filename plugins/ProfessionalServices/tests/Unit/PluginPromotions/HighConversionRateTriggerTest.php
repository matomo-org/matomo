<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Unit\PluginPromotions;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\HighConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\WeeklyGoalMetrics;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class HighConversionRateTriggerTest extends TestCase
{
    private HighConversionRateTrigger $trigger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trigger = new HighConversionRateTrigger(
            $this->createMock(WeeklyGoalMetrics::class),
            $this->createMock(ReportPeriod::class),
            $this->createMock(DailyTriggerCache::class)
        );
    }

    /**
     * @dataProvider getThresholdCases
     */
    public function testAppliesTheConversionRateFloorAndTheConversionFloor(
        float $conversionRate,
        int $nbConversions,
        bool $expectedToQualify
    ): void {
        $metrics = $this->makeMetrics([
            ['idGoal' => 1, 'name' => 'Newsletter', 'nbConversions' => $nbConversions, 'conversionRate' => $conversionRate],
        ]);

        $this->assertSame($expectedToQualify, null !== $this->trigger->findQualifyingGoal($metrics));
    }

    /**
     * @return array<string, array{float, int, bool}>
     */
    public function getThresholdCases(): array
    {
        return [
            // The rate is compared strictly, so exactly 3% belongs to the funnels promotion.
            'exactly at the conversion rate boundary' => [0.03, 500, false],
            'just above the conversion rate boundary' => [0.0301, 500, true],
            'just below the conversion floor' => [0.1, 499, false],
            'well above both thresholds' => [0.2, 2000, true],
            'converts often but too seldom to experiment on' => [0.08, 120, false],
            'no conversions' => [0.0, 0, false],
        ];
    }

    public function testPicksTheQualifyingGoalWithTheHighestConversionRate(): void
    {
        $metrics = $this->makeMetrics([
            ['idGoal' => 1, 'name' => 'Newsletter', 'nbConversions' => 2000, 'conversionRate' => 0.05],
            ['idGoal' => 2, 'name' => 'Contact form', 'nbConversions' => 600, 'conversionRate' => 0.12],
            ['idGoal' => 3, 'name' => 'Free trial', 'nbConversions' => 5000, 'conversionRate' => 0.02],
            ['idGoal' => 4, 'name' => 'Demo request', 'nbConversions' => 400, 'conversionRate' => 0.4],
        ]);

        $goal = $this->trigger->findQualifyingGoal($metrics);

        // Free trial is below the rate floor and Demo request is below the conversion
        // floor, so the highest rate among the goals that actually qualify wins.
        $this->assertSame(2, $goal['goalId']);
        $this->assertSame('Contact form', $goal['goalName']);
        $this->assertSame(600, $goal['nbConversions']);
    }

    public function testBreaksATieOnTheConversionRateWithTheMostConversions(): void
    {
        $metrics = $this->makeMetrics([
            ['idGoal' => 1, 'name' => 'Newsletter', 'nbConversions' => 700, 'conversionRate' => 0.09],
            ['idGoal' => 2, 'name' => 'Contact form', 'nbConversions' => 1300, 'conversionRate' => 0.09],
        ]);

        $this->assertSame(2, $this->trigger->findQualifyingGoal($metrics)['goalId']);
    }

    public function testReturnsNothingWhenNoGoalIsConfigured(): void
    {
        $this->assertNull($this->trigger->findQualifyingGoal($this->makeMetrics([])));
    }

    /**
     * @param array<int, array<string, mixed>> $goals
     * @return array{siteVisits: int, goals: array<int, array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}>}
     */
    private function makeMetrics(array $goals): array
    {
        $siteVisits = 25000;

        return [
            'siteVisits' => $siteVisits,
            'goals' => array_map(static function (array $goal) use ($siteVisits): array {
                return [
                    'idGoal' => $goal['idGoal'],
                    'name' => $goal['name'],
                    'nbConversions' => $goal['nbConversions'],
                    'nbVisitsConverted' => (int) round($goal['conversionRate'] * $siteVisits),
                    'conversionRate' => $goal['conversionRate'],
                ];
            }, $goals),
        ];
    }
}
