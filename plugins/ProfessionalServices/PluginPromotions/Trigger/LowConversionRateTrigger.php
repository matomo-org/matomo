<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\WeeklyGoalMetrics;

/**
 * Triggers when a configured goal converted for at most 3% of last week's visits, having
 * converted often enough for that rate to mean something.
 *
 * The counterpart of {@see HighConversionRateTrigger}: the two split the goals of a
 * website at the same 3% rate, so no goal can ever satisfy both.
 *
 * Reads last week's goal metrics from the existing archive only, and caches the outcome
 * for the day.
 */
class LowConversionRateTrigger implements PromotionTrigger
{
    public const NAME = 'conversion_rate_funnels';

    public const MAXIMUM_CONVERSION_RATE = 0.03;

    public const MINIMUM_CONVERSIONS = 100;

    private WeeklyGoalMetrics $goalMetrics;

    private ReportPeriod $reportPeriod;

    private DailyTriggerCache $cache;

    public function __construct(WeeklyGoalMetrics $goalMetrics, ReportPeriod $reportPeriod, DailyTriggerCache $cache)
    {
        $this->goalMetrics = $goalMetrics;
        $this->reportPeriod = $reportPeriod;
        $this->cache = $cache;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function evaluate(int $idSite): TriggerResult
    {
        return $this->cache->getOrEvaluate(self::NAME, $idSite, function () use ($idSite) {
            return $this->evaluateFromReport($idSite);
        });
    }

    private function evaluateFromReport(int $idSite): TriggerResult
    {
        $period = $this->reportPeriod->forSite($idSite);
        $periodStart = $period->getDateStart()->toString();
        $periodEnd = $period->getDateEnd()->toString();

        $goal = $this->findQualifyingGoal($this->goalMetrics->read($idSite));

        if (null === $goal) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        return TriggerResult::triggered($goal, $periodStart, $periodEnd);
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
