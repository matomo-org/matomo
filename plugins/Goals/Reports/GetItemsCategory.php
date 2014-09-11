<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Reports;

use Piwik\Piwik;
use Piwik\Plugins\Goals\Columns\ProductCategory;

class GetItemsCategory extends BaseEcommerceItem
{
    protected function init()
    {
        parent::init();

        $this->name      = Piwik::translate('Goals_ProductCategory');
        $this->dimension = new ProductCategory();
        $this->order     = 32;
        $this->widgetTitle = 'Goals_ProductCategory';
    }
}
