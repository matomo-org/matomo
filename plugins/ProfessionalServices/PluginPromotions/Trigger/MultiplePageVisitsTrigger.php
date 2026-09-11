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
 * Triggers when visitors regularly move through several pages in one visit, which is what
 * makes a page to page path worth drawing.
 */
class MultiplePageVisitsTrigger extends ReportBackedTrigger
{
    public const NAME = 'multiple_page_visits';

    public const MINIMUM_VISITS = 500;

    public const MINIMUM_PAGES_PER_VISIT = 4;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getRequiredArchives(): array
    {
        return ['VisitorInterest'];
    }

    protected function getApiMethod(): string
    {
        return 'VisitorInterest.getNumberOfVisitsPerPage';
    }

    protected function getApiParameters(): array
    {
        return ['filter_limit' => -1];
    }

    protected function deriveContext(DataTable $report): ?array
    {
        $visits = $this->countVisitsOverPageThreshold($report);

        return $visits < self::MINIMUM_VISITS ? null : ['count' => $visits];
    }

    /**
     * Adds up the visits in every bucket that starts at or above the page threshold.
     *
     * The report buckets visits by how many pages they viewed, and each row carries the
     * segment that selects its bucket: `actions==4`, `actions>=6;actions<=7`,
     * `actions>=21`. The first number in it is the bucket's lower bound, so a bucket whose
     * lower bound clears the threshold has every one of its visits clear it too.
     *
     * The bound is read from there rather than from the label, because the label is
     * translated for display before the report is returned - it arrives as
     * "VisitorInterest_NPages", with no number in it at all.
     */
    public function countVisitsOverPageThreshold(DataTable $visitsPerPage): int
    {
        $visits = 0;

        foreach ($visitsPerPage->getRows() as $row) {
            if (!preg_match('/(\d+)/', (string) $row->getMetadata('segment'), $matches)) {
                continue;
            }

            if ((int) $matches[1] < self::MINIMUM_PAGES_PER_VISIT) {
                continue;
            }

            $visits += (int) $row->getColumn('nb_visits');
        }

        return $visits;
    }
}
