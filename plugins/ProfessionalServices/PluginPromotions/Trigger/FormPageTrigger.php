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
 * Triggers when a busy page of the website is one that almost certainly carries a form.
 *
 * There is no way to know from a report that a page has a form on it, so the URL is the
 * evidence: the four paths below are the conventional ones for a page built around a
 * form, and a page named after one is worth asking about.
 */
class FormPageTrigger extends ReportBackedTrigger
{
    public const NAME = 'form_visits';

    public const MINIMUM_VISITS = 500;

    /**
     * Matched anywhere in the URL, so `/en/contact-us/` and `/checkout/step-2` both count.
     */
    public const FORM_URL_FRAGMENTS = ['contact-us', 'checkout', 'signup', 'login'];

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
            'filter_sort_column' => 'nb_visits',
            'filter_sort_order' => 'desc',
            'filter_limit' => self::ROWS_TO_INSPECT,
        ];
    }

    protected function deriveContext(DataTable $report): ?array
    {
        return $this->findQualifyingFormPage($report);
    }

    /**
     * Returns the busiest page whose URL names a form, or null when none qualifies.
     *
     * The rows are expected to be ordered by visits, descending, which is how the report
     * is requested: everything below the threshold can therefore be skipped as soon as
     * the first row falls under it.
     *
     * @return array{url: string, count: int}|null
     */
    public function findQualifyingFormPage(DataTable $pageUrls): ?array
    {
        foreach ($pageUrls->getRows() as $row) {
            $visits = (int) $row->getColumn('nb_visits');

            if ($visits < self::MINIMUM_VISITS) {
                break;
            }

            $label = (string) $row->getColumn('label');
            $url = (string) $row->getMetadata('url');

            foreach (self::FORM_URL_FRAGMENTS as $fragment) {
                if (false === stripos($label, $fragment) && false === stripos($url, $fragment)) {
                    continue;
                }

                return [
                    // The report's label is the page path, which is what the copy names.
                    'url' => $label ?: $url,
                    'count' => $visits,
                ];
            }
        }

        return null;
    }
}
