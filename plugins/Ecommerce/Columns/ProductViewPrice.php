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
use Piwik\Plugin\Manager;
use Piwik\Plugins\CustomVariables\Tracker\CustomVariablesRequestProcessor;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visitor;

class ProductViewPrice extends ActionDimension
{
    protected $type = self::TYPE_MONEY;
    protected $nameSingular = 'Ecommerce_ViewedProductPrice';
    protected $columnName = 'product_price';
    protected $segmentName = 'productViewPrice';
    protected $columnType = 'DOUBLE NULL';
    protected $category = 'Goals_Ecommerce';

    public function onNewAction(Request $request, Visitor $visitor, Action $action)
    {
        $price = $request->getParam('_pkp');
        if (is_numeric($price)) {
            return $price;
        }

        // fall back to custom variables (might happen if old logs are replayed)
        if (Manager::getInstance()->isPluginActivated('CustomVariables')) {
            $customVariables = CustomVariablesRequestProcessor::getCustomVariablesInPageScope($request);
            if (isset($customVariables['custom_var_k2']) && $customVariables['custom_var_k2'] === '_pkp') {
                return $customVariables['custom_var_v2'] ?? false;
            }
        }

        return parent::onNewAction($request, $visitor, $action);
    }
}