<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class SiteSearchSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'Actions_SubmenuSitesearch';
    protected $order = 25;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Actions_SiteSearchSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Actions_SiteSearchSubcategoryHelp2') . '</p>'
            . '<p><a href="https://matomo.org/docs/site-search/?mtm_campaign=App_Help&mtm_source=Matomo_App&mtm_keyword=UserGuides" rel="noreferrer noopener" target="_blank">' . Piwik::translate('Actions_SiteSearchSubcategoryHelp3') . '</a></p>';
    }
}
