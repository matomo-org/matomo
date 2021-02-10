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

class ProductSubcategory extends Subcategory
{
    protected $categoryId = 'Goals_Ecommerce';
    protected $id = 'Goals_Products';
    protected $order = 10;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Ecommerce_ProductSubcategoryHelp') . '</p>';
    }
}
