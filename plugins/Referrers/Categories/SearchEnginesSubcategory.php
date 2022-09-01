<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class SearchEnginesSubcategory extends Subcategory
{
    protected $categoryId = 'Referrers_Referrers';
    protected $id = 'Referrers_SubmenuSearchEngines';
    protected $order = 10;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Referrers_SearchEnginesSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Referrers_SearchEnginesSubcategoryHelp2', ['<a href="https://matomo.org/matomo-cloud/?mtm_campaign=App_Help&mtm_source=Matomo_App" rel="noreferrer noopener" target="_blank">', '</a>', '<a href="https://plugins.matomo.org/SearchEngineKeywordsPerformance" rel="noreferrer noopener" target="_blank">', '</a>']) . '</p>';
    }
}
