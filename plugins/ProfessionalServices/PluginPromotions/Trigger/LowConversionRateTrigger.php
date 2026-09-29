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
 * Triggers when a configured goal converted for at most 3% of last week's visits, having
 * converted often enough for that rate to mean something.
 *
 * The counterpart of {@see HighConversionRateTrigger}: the two split the goals of a
 * website at the same 3% rate, so no goal can ever satisfy both.
 */
class LowConversionRateTrigger extends GoalBackedTrigger
{
    public const NAME = 'conversion_rate_funnels';

    public const MAXIMUM_CONVERSION_RATE = 0.03;

    public const MINIMUM_CONVERSIONS = 100;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function deriveContext(array $metrics, int $idSite): ?array
    {
        return $this->findQualifyingGoal($metrics);
    }

    /**
     * Returns the goal a funnel would help most with, or null when none qualifies.
     *
     * The conversion floor keeps out goals whose rate is only low because they are barely
     * used: a goal that converted twice is not evidence of a drop off worth investigating.
     *
     * @param array{siteVisits: int, goals: array<int, array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}>} $metrics
     * @return array{goalId: int, goalName: string, nbVisits: int, nbConversions: int, conversionRate: float}|null
     */
    public function findQualifyingGoal(array $metrics): ?array
    {
        $candidates = array_filter($metrics['goals'], static function (array $goal): bool {
            return $goal['conversionRate'] <= self::MAXIMUM_CONVERSION_RATE
                && $goal['nbConversions'] >= self::MINIMUM_CONVERSIONS;
        });

        $goal = WeeklyGoalMetrics::pickGoalWithLowestConversionRate($candidates);

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
