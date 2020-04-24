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
use Piwik\Plugins\Ecommerce\Columns\ProductSku;

class GetItemsSku extends BaseItem
{

    protected function init()
    {
        parent::init();

        $this->name      = Piwik::translate('Goals_ProductSKU');
        $this->dimension = new ProductSku();
        $this->order     = 31;

        $this->subcategoryId = 'Goals_Products';
    }

}
