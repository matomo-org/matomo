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
 * Triggers when an entry page of the website received at least 200 visits last week and
 * at least 55% of them bounced.
 *
 * Reads last week's entry pages report from the existing archive only, and caches the
 * outcome for the day.
 */
class BounceRateTrigger implements PromotionTrigger
{
    public const NAME = 'bounce_rate';

    public const MINIMUM_ENTRY_VISITS = 200;

    public const MINIMUM_BOUNCE_RATE = 0.55;

    /**
     * Rows come back ordered by entry visits, so the qualifying page with the most visits
     * is always found well within this many rows.
     */
    private const ROWS_TO_INSPECT = 50;

    private ArchivedReportReader $reader;

    private ReportPeriod $reportPeriod;

    private DailyTriggerCache $cache;

    public function __construct(ArchivedReportReader $reader, ReportPeriod $reportPeriod, DailyTriggerCache $cache)
    {
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
            return $this->evaluateFromReport($idSite);
        });
    }

    private function evaluateFromReport(int $idSite): TriggerResult
    {
        $period = $this->reportPeriod->forSite($idSite);
        $periodStart = $period->getDateStart()->toString();
        $periodEnd = $period->getDateEnd()->toString();

        // Entry pages are only reachable through the Actions API, which builds its own
        // archive. Reading them is therefore only safe once one already exists.
        if (!$this->reader->hasCompletedArchive($idSite, 'Actions', $period)) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        $entryPages = Request::processRequest('Actions.getEntryPageUrls', [
            'idSite' => $idSite,
            'period' => ReportPeriod::PERIOD,
            'date' => ReportPeriod::DATE,
            'flat' => 1,
            'format_metrics' => 0,
            'filter_sort_column' => 'entry_nb_visits',
            'filter_sort_order' => 'desc',
            'filter_limit' => self::ROWS_TO_INSPECT,
        ], []);

        if (!$entryPages instanceof DataTable) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        $entryPage = $this->findQualifyingEntryPage($entryPages);

        if (null === $entryPage) {
            return TriggerResult::notTriggered($periodStart, $periodEnd);
        }

        return TriggerResult::triggered($entryPage, $periodStart, $periodEnd);
    }

    /**
     * Returns the entry page with the most visits that also bounced often enough, or null
     * when no page qualifies.
     *
     * The rows are expected to be ordered by entry visits, descending, which is how the
     * report is requested: the first qualifying row is therefore also the one with the
     * most visits, and everything below the visit threshold can be skipped.
     *
     * @return array{url: string, entryVisits: int, bounceRate: float}|null
     */
    public function findQualifyingEntryPage(DataTable $entryPages): ?array
    {
        foreach ($entryPages->getRows() as $row) {
            $entryVisits = (int) $row->getColumn('entry_nb_visits');

            if ($entryVisits < self::MINIMUM_ENTRY_VISITS) {
                break;
            }

            // Derived from the raw counts rather than read from the `bounce_rate` column,
            // which is a processed metric the API renders as a localised string by default.
            $bounceRate = (float) (((int) $row->getColumn('entry_bounce_count')) / $entryVisits);

            if ($bounceRate < self::MINIMUM_BOUNCE_RATE) {
                continue;
            }

            return [
                // The report's label is the page path, eg. `/pricing`. That is what the
                // copy shows: a full URL with its scheme and host is far too long for a
                // headline, and the host adds nothing when the website is already known.
                'url' => (string) ($row->getColumn('label') ?: $row->getMetadata('url')),
                'entryVisits' => $entryVisits,
                'bounceRate' => $bounceRate,
            ];
        }

        return null;
    }
}
