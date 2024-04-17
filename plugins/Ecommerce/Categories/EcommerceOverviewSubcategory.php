<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Ecommerce\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;
use Piwik\Url;

class EcommerceOverviewSubcategory extends Subcategory
{
    protected $categoryId = 'Goals_Ecommerce';
    protected $id = 'General_Overview';
    protected $order = 2;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Ecommerce_EcommerceOverviewSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Ecommerce_EcommerceOverviewSubcategoryHelp2') . '</p>'
            . '<p><a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/ecommerce-analytics/', null, null, 'App.Ecommerce.Overview')
            . '" rel="noreferrer noopener" target="_blank">' . Piwik::translate('Ecommerce_EcommerceOverviewSubcategoryHelp3') . '</a></p>';
    }
}
