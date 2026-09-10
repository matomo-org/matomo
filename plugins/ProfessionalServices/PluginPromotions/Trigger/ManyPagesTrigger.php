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
 * Triggers on a website with many pages that are all genuinely being visited, which is
 * the shape of a site big enough for front end errors to go unnoticed.
 */
class ManyPagesTrigger extends ReportBackedTrigger
{
    public const NAME = 'many_pages';

    public const MINIMUM_PAGES = 50;

    public const MINIMUM_VISITS_PER_PAGE = 100;

    /**
     * Only pages above the visit threshold count, and the report is ordered by visits, so
     * reading a little beyond the page threshold is enough to answer.
     */
    private const ROWS_TO_INSPECT = 200;

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
        return 'Actions.getPageUrls';
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
        $activePages = $this->countActivePages($report);

        return $activePages < self::MINIMUM_PAGES ? null : ['count' => $activePages];
    }

    /**
     * Counts the pages that cleared the visit threshold.
     *
     * The rows are expected to be ordered by visits, descending, which is how the report
     * is requested, so counting can stop at the first row that falls below it.
     */
    public function countActivePages(DataTable $pages): int
    {
        $active = 0;

        foreach ($pages->getRows() as $row) {
            if ((int) $row->getColumn('nb_visits') < self::MINIMUM_VISITS_PER_PAGE) {
                break;
            }

            $active++;
        }

        return $active;
    }
}
