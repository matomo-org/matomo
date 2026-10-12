<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\PluginPromotions\Trigger;

use Piwik\DataTable;
use Piwik\Plugins\Referrers\API as ReferrersApi;

/**
 * Triggers when search engines are sending real traffic while withholding the queries
 * behind it, which is the blind spot the promotion offers to fill.
 */
class KeywordsNotDefinedTrigger extends ReportBackedTrigger
{
    public const NAME = 'keywords_not_defined';

    public const MINIMUM_VISITS = 500;

    private const ROWS_TO_INSPECT = 100;

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getRequiredArchives(): array
    {
        return ['Referrers'];
    }

    protected function getApiMethod(): string
    {
        return 'Referrers.getKeywords';
    }

    protected function getApiParameters(): array
    {
        return [
            'filter_sort_column' => 'nb_visits',
            'filter_sort_order' => 'desc',
            'filter_limit' => self::ROWS_TO_INSPECT,
        ];
    }

    protected function deriveContext(DataTable $report): ?array
    {
        $visits = $this->countVisitsWithoutAKeyword($report);

        return $visits < self::MINIMUM_VISITS ? null : ['count' => $visits];
    }

    /**
     * The visits search engines sent without saying what was searched for.
     *
     * Matomo stores that keyword as an empty label and renders it as "Keyword not
     * defined" only when a report is displayed, so both forms are matched: the raw label
     * the archive holds, and the rendered string in case the row has been through the
     * filter that replaces it.
     */
    public function countVisitsWithoutAKeyword(DataTable $keywords): int
    {
        $rendered = ReferrersApi::getKeywordNotDefinedString();
        $visits = 0;

        foreach ($keywords->getRows() as $row) {
            $label = (string) $row->getColumn('label');

            if (ReferrersApi::LABEL_KEYWORD_NOT_DEFINED === $label || $rendered === $label) {
                $visits += (int) $row->getColumn('nb_visits');
            }
        }

        return $visits;
    }
}
