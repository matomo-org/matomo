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

class PageTitlesSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'Actions_SubmenuPageTitles';
    protected $order = 20;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Actions_PageTitlesSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Actions_PageTitlesSubcategoryHelp2') . '</p>';
    }
}
