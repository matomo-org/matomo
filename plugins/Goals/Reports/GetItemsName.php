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
use Piwik\Plugins\Goals\Columns\ProductName;

class GetItemsName extends BaseEcommerceItem
{
    protected function init()
    {
        parent::init();

        $this->name      = Piwik::translate('Goals_ProductName');
        $this->dimension = new ProductName();
        $this->order     = 31;
        $this->widgetTitle = 'Goals_ProductName';
    }
}
