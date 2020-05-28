<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class ProductViewPrice extends ActionDimension
{
    protected $type = self::TYPE_MONEY;
    protected $nameSingular = 'Goals_ProductPrice';
    protected $columnName = 'product_price';
    protected $segmentName = 'productViewPrice';
    protected $columnType = 'DOUBLE NULL';
    protected $category = 'Goals_Ecommerce';

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        $price = $request->getParam('_pkp');
        if (!empty($price)) {
            return $price;
        }

        return parent::onNewAction($request, $visitor, $action);
    }
}