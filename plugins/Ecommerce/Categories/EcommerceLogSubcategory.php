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

class EcommerceLogSubcategory extends Subcategory
{
    protected $categoryId = 'Goals_Ecommerce';
    protected $id = 'Goals_EcommerceLog';
    protected $order = 5;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Ecommerce_EcommerceLogSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Ecommerce_EcommerceLogSubcategoryHelp2') . '</p>';
    }
}
