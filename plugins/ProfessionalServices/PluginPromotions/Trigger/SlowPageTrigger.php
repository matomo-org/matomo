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
 * Triggers when a page people actually look at is slow to load.
 *
 * The load time comes from the performance metrics the tracker sends alongside a
 * pageview, so a website that does not send them can never satisfy this, however slow it
 * is.
 */
class SlowPageTrigger extends ReportBackedTrigger
{
    public const NAME = 'slow_page';

    public const MINIMUM_PAGEVIEWS = 500;

    /**
     * In seconds, which is the unit `avg_page_load_time` is reported in.
     */
    public const MINIMUM_LOAD_TIME = 3.0;

    private const ROWS_TO_INSPECT = 100;

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
            'filter_sort_column' => 'nb_hits',
            'filter_sort_order' => 'desc',
            'filter_limit' => self::ROWS_TO_INSPECT,
        ];
    }

    protected function deriveContext(DataTable $report): ?array
    {
        return $this->findSlowestBusyPage($report);
    }

    /**
     * Returns the busiest page that is also slow, or null when none qualifies.
     *
     * The rows are expected to be ordered by pageviews, descending, which is how the
     * report is requested: the first qualifying row is therefore the busiest one, and
     * everything below the pageview threshold can be skipped.
     *
     * @return array{url: string, count: int, loadTime: float}|null
     */
    public function findSlowestBusyPage(DataTable $pages): ?array
    {
        foreach ($pages->getRows() as $row) {
            $pageviews = (int) $row->getColumn('nb_hits');

            if ($pageviews < self::MINIMUM_PAGEVIEWS) {
                break;
            }

            $loadTime = (float) $row->getColumn('avg_page_load_time');

            if ($loadTime < self::MINIMUM_LOAD_TIME) {
                continue;
            }

            return [
                'url' => (string) ($row->getColumn('label') ?: $row->getMetadata('url')),
                'count' => $pageviews,
                'loadTime' => $loadTime,
            ];
        }

        return null;
    }
}
