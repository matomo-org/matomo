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
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyApi;

/**
 * Triggers when enough visitors are coming back for groups of them to be worth comparing
 * over time.
 *
 * This one does not extend {@see ReportBackedTrigger}, even though it reads a single
 * figure for a single website, and the reason is worth stating because it is easy to
 * undo. The obvious source is `VisitFrequency.get`, but that method answers by issuing two
 * `VisitsSummary.get` sub-requests carrying the `visitorType` segments, and each of those
 * has an archive and a done flag of its own. The gate in `ReportBackedTrigger` checks the
 * unsegmented archive, sees it present, and lets the request through - whereupon the two
 * segment archives are built, on a dashboard render, by a promotion nobody asked for.
 *
 * So the returning half is read here directly, through the archive query that cannot
 * launch archiving. A missing segment archive then yields no value and the promotion
 * stays silent for the day, which is the correct outcome: a promotion is never a reason
 * to archive.
 */
class ReturningVisitorsTrigger implements PromotionTrigger
{
    public const NAME = 'returning_visitors';

    public const MINIMUM_RETURNING_VISITORS = 500;

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

    public function getName(): string
    {
        return self::NAME;
    }

    public function evaluate(int $idSite): TriggerResult
    {
        return $this->cache->getOrEvaluate(self::NAME, $idSite, function () use ($idSite) {
            return $this->evaluateFromArchive($idSite);
        });
    }

    private function evaluateFromArchive(int $idSite): TriggerResult
    {
        $period = $this->reportPeriod->forSite($idSite);
        $periodStart = $period->getDateStart()->toString();
        $periodEnd = $period->getDateEnd()->toString();

        $archive = $this->reader->buildArchive(
            $idSite,
            ReportPeriod::PERIOD,
            ReportPeriod::DATE,
            // The same segment VisitFrequency uses for its returning half, so this reads
            // the archive that method would have read rather than one of our own.
            urldecode(VisitFrequencyApi::RETURNING_VISITOR_SEGMENT)
        );

        // Distinct visitors rather than visits: the promotion compares groups of people
        // over time and its copy says "returning visitors", so someone coming back three
        // times is one of them and not three.
        //
        // getNumeric() only collapses to a plain number when it has nothing else to
        // report; alongside a value it returns a map that also carries a `_metadata`
        // entry, and casting that to int quietly yields 1. Read the metric by name.
        $value = $archive->getNumeric('nb_uniq_visitors');
        $returning = is_array($value) ? (int) ($value['nb_uniq_visitors'] ?? 0) : (int) $value;

        if ($returning < self::MINIMUM_RETURNING_VISITORS) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        return TriggerResult::triggered(['count' => $returning], $periodStart, $periodEnd);
    }
}
