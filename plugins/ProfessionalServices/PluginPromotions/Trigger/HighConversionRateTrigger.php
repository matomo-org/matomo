<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\WeeklyGoalMetrics;

/**
 * Triggers when a configured goal converted for more than 3% of last week's visits and
 * did so often enough for an experiment on it to reach a conclusion.
 *
 * The counterpart of {@see LowConversionRateTrigger}: the two split the goals of a
 * website at the same 3% rate, so no goal can ever satisfy both. The conversion floor is
 * the higher of the two, because an A/B test needs volume to be worth running, where a
 * funnel only needs a drop off to investigate.
 */
class HighConversionRateTrigger extends GoalBackedTrigger
{
    public const NAME = 'conversion_rate_ABtesting';

    public const MINIMUM_CONVERSION_RATE = 0.03;

    public const MINIMUM_CONVERSIONS = 500;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function deriveContext(array $metrics, int $idSite): ?array
    {
        return $this->findQualifyingGoal($metrics);
    }

    /**
     * Returns the goal that is worth experimenting on, or null when none qualifies.
     *
     * The rate is compared strictly, so a goal converting at exactly 3% belongs to
     * {@see LowConversionRateTrigger} and not here.
     *
     * @param array{siteVisits: int, goals: array<int, array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}>} $metrics
     * @return array{goalId: int, goalName: string, nbVisits: int, nbConversions: int, conversionRate: float}|null
     */
    public function findQualifyingGoal(array $metrics): ?array
    {
        $candidates = array_filter($metrics['goals'], static function (array $goal): bool {
            return $goal['conversionRate'] > self::MINIMUM_CONVERSION_RATE
                && $goal['nbConversions'] >= self::MINIMUM_CONVERSIONS;
        });

        $goal = WeeklyGoalMetrics::pickGoalWithHighestConversionRate($candidates);

        if (null === $goal) {
            return null;
        }

        return [
            'goalId' => $goal['idGoal'],
            'goalName' => $goal['name'],
            'nbVisits' => $metrics['siteVisits'],
            'nbConversions' => $goal['nbConversions'],
            'conversionRate' => $goal['conversionRate'],
        ];
    }
}
