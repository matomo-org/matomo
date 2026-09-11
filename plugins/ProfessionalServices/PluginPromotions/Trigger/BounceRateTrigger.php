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
 * Triggers when a page of the website was entered at least 200 times last week and at
 * least 55% of those visits bounced.
 *
 * Reads page titles rather than page URLs, because the promotion names the page in its
 * copy and a title reads where a URL path does not.
 */
class BounceRateTrigger extends ReportBackedTrigger
{
    public const NAME = 'bounce_rate';

    public const MINIMUM_ENTRY_VISITS = 200;

    public const MINIMUM_BOUNCE_RATE = 0.55;

    /**
     * The qualifying page is the busiest one, and the report is ordered by visits, so
     * only the head of it can matter.
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
        return 'Actions.getPageTitles';
    }

    protected function getApiParameters(): array
    {
        return [
            'flat' => 1,
            'filter_sort_column' => 'nb_visits',
            'filter_sort_order' => 'desc',
            'filter_limit' => self::ROWS_TO_INSPECT,
        ];
    }

    protected function deriveContext(DataTable $report): ?array
    {
        return $this->findQualifyingPage($report);
    }

    /**
     * Returns the most visited page that was also entered often enough and bounced, or
     * null when no page qualifies.
     *
     * The rows are ordered by visits, descending, while the threshold is on *entry*
     * visits. Reading can still stop at the first row below the threshold: a visit that
     * entered on a page is also a visit to it, so entry visits never exceed visits, and
     * once visits fall under the threshold no later row can clear it either.
     *
     * @return array{title: string, entryVisits: int, bounceRate: float}|null
     */
    public function findQualifyingPage(DataTable $pages): ?array
    {
        foreach ($pages->getRows() as $row) {
            if ((int) $row->getColumn('nb_visits') < self::MINIMUM_ENTRY_VISITS) {
                break;
            }

            // A page title that was never an entry page carries no entry metrics at all,
            // so the report gives false rather than zero for both of these.
            $entryVisits = (int) $row->getColumn('entry_nb_visits');

            if ($entryVisits < self::MINIMUM_ENTRY_VISITS) {
                continue;
            }

            // Derived from the raw counts rather than read from the `bounce_rate` column,
            // which is a processed metric the API renders as a localised string by default.
            $bounceRate = (float) (((int) $row->getColumn('entry_bounce_count')) / $entryVisits);

            if ($bounceRate < self::MINIMUM_BOUNCE_RATE) {
                continue;
            }

            return [
                'title' => (string) $row->getColumn('label'),
                'entryVisits' => $entryVisits,
                'bounceRate' => $bounceRate,
            ];
        }

        return null;
    }
}
