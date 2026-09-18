<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ArchivedReportReader;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;

/**
 * Shared by every promotion that answers one question of one archived report.
 *
 * The order of operations is the same for all of them and matters in every case: the
 * outcome is cached for the day, the report is only read once its archive already exists,
 * and the period is reported alongside the result whether or not the trigger fired. A
 * subclass says which archives it needs, which report to ask for and what to make of the
 * answer, and inherits the rest.
 *
 * A promotion that needs more than one report, or something other than a report, does not
 * belong here: {@see MultipleConversionChannelsTrigger} reads a goal and then a report per
 * goal, and {@see MultipleActiveSitesTrigger} reads one archive per website and cannot be
 * cached at all.
 */
abstract class ReportBackedTrigger implements PromotionTrigger
{
    private ArchivedReportReader $reader;

    private ReportPeriod $reportPeriod;

    private DailyTriggerCache $cache;

    public function __construct(
        ArchivedReportReader $reader,
        ReportPeriod $reportPeriod,
        DailyTriggerCache $cache
    ) {
        $this->reader = $reader;
        $this->reportPeriod = $reportPeriod;
        $this->cache = $cache;
    }

    /**
     * The plugins whose archives have to exist before the report may be read.
     *
     * Reports reached through an API build their own archive, so asking for one before it
     * exists would make opening a dashboard the reason archiving runs. Naming the plugins
     * here is what prevents that.
     *
     * @return string[]
     */
    abstract protected function getRequiredArchives(): array;

    /**
     * The API method to read, for example `Actions.getPageUrls`.
     */
    abstract protected function getApiMethod(): string;

    /**
     * Parameters merged over the defaults every promotion shares: the website, the period
     * and `format_metrics`, which keeps processed metrics as numbers rather than as
     * localised strings.
     *
     * @return array<string, mixed>
     */
    protected function getApiParameters(): array
    {
        return [];
    }

    /**
     * What the promotion wants to say about this report, or null when the report does not
     * satisfy the trigger.
     *
     * @return array<string, mixed>|null
     */
    abstract protected function deriveContext(DataTable $report): ?array;

    /**
     * Not final only because {@see \Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionRegistry}
     * is typed on concrete triggers so the container can wire them, which means the tests
     * that cover the selector have to double those classes and stub this method. Do not
     * override it: the caching and the archive gate below are the reason this base exists.
     */
    public function evaluate(int $idSite): TriggerResult
    {
        return $this->cache->getOrEvaluate($this->getName(), $idSite, function () use ($idSite) {
            return $this->evaluateFromReport($idSite);
        });
    }

    private function evaluateFromReport(int $idSite): TriggerResult
    {
        $period = $this->reportPeriod->forSite($idSite);
        $periodStart = $period->getDateStart()->toString();
        $periodEnd = $period->getDateEnd()->toString();

        foreach ($this->getRequiredArchives() as $pluginName) {
            if (!$this->reader->hasCompletedArchive($idSite, $pluginName, $period)) {
                return TriggerResult::notTriggered($periodStart, $periodEnd);
            }
        }

        $report = Request::processRequest($this->getApiMethod(), array_merge([
            'idSite' => $idSite,
            'period' => ReportPeriod::PERIOD,
            'date' => ReportPeriod::DATE,
            'format_metrics' => 0,
        ], $this->getApiParameters()), []);

        if (!$report instanceof DataTable) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        $context = $this->deriveContext($report);

        if (null === $context) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        return TriggerResult::triggered($context, $periodStart, $periodEnd);
    }
}
