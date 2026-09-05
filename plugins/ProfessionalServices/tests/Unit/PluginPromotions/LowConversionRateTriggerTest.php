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
use Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger\LowConversionRateTrigger;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\WeeklyGoalMetrics;

/**
 * @group ProfessionalServices
 * @group PluginPromotions
 */
class LowConversionRateTriggerTest extends TestCase
{
    private LowConversionRateTrigger $trigger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trigger = new LowConversionRateTrigger(
            $this->createMock(WeeklyGoalMetrics::class),
            $this->createMock(ReportPeriod::class),
            $this->createMock(DailyTriggerCache::class)
        );
    }

    /**
     * @dataProvider getThresholdCases
     */
    public function testAppliesTheConversionRateCeilingAndTheConversionFloor(
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
            'at both thresholds' => [0.03, 100, true],
            'just above the conversion rate ceiling' => [0.0301, 100, false],
            'just below the conversion floor' => [0.02, 99, false],
            'well inside both thresholds' => [0.005, 500, true],
            'converts rarely but far too seldom to judge' => [0.001, 3, false],
            'no conversions' => [0.0, 0, false],
        ];
    }

    public function testPicksTheQualifyingGoalWithTheLowestConversionRate(): void
    {
        $metrics = $this->makeMetrics([
            ['idGoal' => 1, 'name' => 'Newsletter', 'nbConversions' => 500, 'conversionRate' => 0.024],
            ['idGoal' => 2, 'name' => 'Contact form', 'nbConversions' => 120, 'conversionRate' => 0.008],
            ['idGoal' => 3, 'name' => 'Free trial', 'nbConversions' => 900, 'conversionRate' => 0.06],
            ['idGoal' => 4, 'name' => 'Demo request', 'nbConversions' => 50, 'conversionRate' => 0.002],
        ]);

        $goal = $this->trigger->findQualifyingGoal($metrics);

        // Free trial is above the rate ceiling and Demo request is below the conversion
        // floor, so the lowest rate among the goals that actually qualify wins.
        $this->assertSame(2, $goal['goalId']);
        $this->assertSame('Contact form', $goal['goalName']);
        $this->assertSame(5000, $goal['nbVisits']);
    }

    public function testBreaksATieOnTheConversionRateWithTheMostConversions(): void
    {
        // A goal's conversion rate is measured against the website's visits, so two goals
        // can report the same rate and only conversions can separate them.
        $metrics = $this->makeMetrics([
            ['idGoal' => 1, 'name' => 'Newsletter', 'nbConversions' => 120, 'conversionRate' => 0.01],
            ['idGoal' => 2, 'name' => 'Contact form', 'nbConversions' => 480, 'conversionRate' => 0.01],
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
        $siteVisits = 5000;

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
