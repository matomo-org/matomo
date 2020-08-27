<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Reports;

use Piwik\Piwik;
use Piwik\Plugins\Ecommerce\Columns\ProductCategory;

class GetItemsCategory extends BaseItem
{
    protected function init()
    {
        parent::init();

        $this->name      = Piwik::translate('Goals_ProductCategory');
        $this->dimension = new ProductCategory();
        $this->order     = 32;

        $this->subcategoryId = 'Goals_Products';
    }
}
