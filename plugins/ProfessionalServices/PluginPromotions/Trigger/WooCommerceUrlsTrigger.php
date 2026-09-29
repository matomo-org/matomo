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
 * Triggers when the website was tracking WooCommerce cart activity last week.
 *
 * WooCommerce puts `add-to-cart=` in the URL when a product is added to the basket, so a
 * page URL carrying it is evidence of a WooCommerce shop rather than of any shop.
 */
class WooCommerceUrlsTrigger extends ReportBackedTrigger
{
    public const NAME = 'woocommerce_add_to_cart_urls';

    public const MINIMUM_PAGEVIEWS = 100;

    /**
     * The parameter WooCommerce adds to a URL when a product goes into the basket.
     */
    public const ADD_TO_CART_PARAMETER = 'add-to-cart=';

    /**
     * Cart URLs carry a product id, so a busy shop has many of them and no single one need
     * be busy. Enough rows are read to add them up rather than to rank them.
     */
    private const ROWS_TO_INSPECT = 500;

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
        $pageviews = $this->countCartPageviews($report);

        return $pageviews < self::MINIMUM_PAGEVIEWS ? null : ['count' => $pageviews];
    }

    /**
     * Adds up the pageviews of every URL that carries the add to cart parameter.
     *
     * Both the row's label and its url metadata are checked: whether a query string
     * survives into the label depends on the website's URL settings, and the parameter
     * only exists in a query string.
     */
    public function countCartPageviews(DataTable $pageUrls): int
    {
        $pageviews = 0;

        foreach ($pageUrls->getRows() as $row) {
            $label = (string) $row->getColumn('label');
            $url = (string) $row->getMetadata('url');

            if (
                false === strpos($label, self::ADD_TO_CART_PARAMETER)
                && false === strpos($url, self::ADD_TO_CART_PARAMETER)
            ) {
                continue;
            }

            $pageviews += (int) $row->getColumn('nb_hits');
        }

        return $pageviews;
    }
}
