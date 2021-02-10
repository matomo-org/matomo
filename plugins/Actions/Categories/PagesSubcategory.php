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

class PagesSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'General_Pages';
    protected $order = 5;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Actions_PagesSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Actions_PagesSubcategoryHelp2') . '</p>'
            . '<p>' . Piwik::translate('Actions_PagesSubcategoryHelp3') . '</p>';
    }
}
