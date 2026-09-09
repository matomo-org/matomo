<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\DataTable;

/**
 * Triggers when an entry page of the website received at least 200 visits last week and
 * at least 55% of them bounced.
 */
class BounceRateTrigger extends ReportBackedTrigger
{
    public const NAME = 'bounce_rate';

    public const MINIMUM_ENTRY_VISITS = 200;

    public const MINIMUM_BOUNCE_RATE = 0.55;

    /**
     * The qualifying page is the busiest one, and the report is ordered by entry visits,
     * so only the head of it can matter.
     */
    private const ROWS_TO_INSPECT = 50;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getRequiredArchives(): array
    {
        return ['Actions'];
    }

    protected function getApiMethod(): string
    {
        return 'Actions.getEntryPageUrls';
    }

    protected function getApiParameters(): array
    {
        return [
            'flat' => 1,
            'filter_sort_column' => 'entry_nb_visits',
            'filter_sort_order' => 'desc',
            'filter_limit' => self::ROWS_TO_INSPECT,
        ];
    }

    protected function deriveContext(DataTable $report): ?array
    {
        return $this->findQualifyingEntryPage($report);
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
