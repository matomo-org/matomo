<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\Plugins\ProfessionalServices\PluginPromotions\ArchivedReportReader;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\WeeklyGoalMetrics;

/**
 * Shared by the promotions that are pitched on a website's goals.
 *
 * The counterpart of {@see ReportBackedTrigger} for the promotions that start from last
 * week's goal metrics instead of from a single report. The caching, the period and the
 * archive gate work the same way; what differs is that the subclass is handed the goal
 * figures rather than a report, and may go on to read a report of its own.
 */
abstract class GoalBackedTrigger implements PromotionTrigger
{
    private WeeklyGoalMetrics $goalMetrics;

    private ArchivedReportReader $reader;

    private ReportPeriod $reportPeriod;

    private DailyTriggerCache $cache;

    public function __construct(
        WeeklyGoalMetrics $goalMetrics,
        ArchivedReportReader $reader,
        ReportPeriod $reportPeriod,
        DailyTriggerCache $cache
    ) {
        $this->goalMetrics = $goalMetrics;
        $this->reader = $reader;
        $this->reportPeriod = $reportPeriod;
        $this->cache = $cache;
    }

    /**
     * The plugins whose archives have to exist first.
     *
     * Empty for a promotion that only needs the goal metrics: those are read straight from
     * the numeric archive, which never builds anything. A promotion that goes on to read a
     * report has to name that report's plugin here.
     *
     * @return string[]
     */
    protected function getRequiredArchives(): array
    {
        return [];
    }

    /**
     * What the promotion wants to say about these goals, or null when none of them
     * satisfies the trigger.
     *
     * @param array{siteVisits: int, goals: array<int, array{idGoal: int, name: string, nbConversions: int, nbVisitsConverted: int, conversionRate: float}>} $metrics
     * @return array<string, mixed>|null
     */
    abstract protected function deriveContext(array $metrics, int $idSite): ?array;

    /**
     * Not final only because {@see \Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionRegistry}
     * is typed on concrete triggers so the container can wire them, which means the tests
     * that cover the selector have to double those classes and stub this method. Do not
     * override it: the caching and the archive gate below are the reason this base exists.
     */
    public function evaluate(int $idSite): TriggerResult
    {
        return $this->cache->getOrEvaluate($this->getName(), $idSite, function () use ($idSite) {
            return $this->evaluateFromGoals($idSite);
        });
    }

    private function evaluateFromGoals(int $idSite): TriggerResult
    {
        $period = $this->reportPeriod->forSite($idSite);
        $periodStart = $period->getDateStart()->toString();
        $periodEnd = $period->getDateEnd()->toString();

        foreach ($this->getRequiredArchives() as $pluginName) {
            if (!$this->reader->hasCompletedArchive($idSite, $pluginName, $period)) {
                return TriggerResult::notTriggered($periodStart, $periodEnd);
            }
        }

        $context = $this->deriveContext($this->goalMetrics->read($idSite), $idSite);

        if (null === $context) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        return TriggerResult::triggered($context, $periodStart, $periodEnd);
    }
}
