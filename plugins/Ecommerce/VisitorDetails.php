<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce;

use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\LogAggregator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Tracker\Action;
use Piwik\Tracker\GoalManager;
use Piwik\Tracker\PageUrl;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendVisitorDetails(&$visitor)
    {
        $ecommerceMetrics = $this->queryEcommerceConversionsVisitorLifeTimeMetricsForVisitor($visitor['idSite'], $visitor['visitorId']);
        $visitor['totalEcommerceRevenue']      = $ecommerceMetrics['totalEcommerceRevenue'];
        $visitor['totalEcommerceConversions']  = $ecommerceMetrics['totalEcommerceConversions'];
        $visitor['totalEcommerceItems']        = $ecommerceMetrics['totalEcommerceItems'];

        $visitor['totalAbandonedCartsRevenue'] = $ecommerceMetrics['totalAbandonedCartsRevenue'];
        $visitor['totalAbandonedCarts']        = $ecommerceMetrics['totalAbandonedCarts'];
        $visitor['totalAbandonedCartsItems']   = $ecommerceMetrics['totalAbandonedCartsItems'];
    }

    public function provideActions(&$actions, $visitorDetails)
    {
        $ecommerceDetails = $this->queryEcommerceConversionsForVisit($visitorDetails['idVisit']);

        foreach ($ecommerceDetails as &$ecommerceDetail) {
            if ($ecommerceDetail['type'] == Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART) {
                unset($ecommerceDetail['orderId']);
                unset($ecommerceDetail['revenueSubTotal']);
                unset($ecommerceDetail['revenueTax']);
                unset($ecommerceDetail['revenueShipping']);
                unset($ecommerceDetail['revenueDiscount']);
            }

            // 25.00 => 25
            foreach ($ecommerceDetail as $column => $value) {
                if (strpos($column, 'revenue') !== false) {
                    if ($value == round($value)) {
                        $ecommerceDetail[$column] = round($value);
                    }
                }
            }

            $idOrder = isset($ecommerceDetail['orderId']) ? $ecommerceDetail['orderId'] : GoalManager::ITEM_IDORDER_ABANDONED_CART;

            $itemsDetails = $this->queryEcommerceItemsForOrder($visitorDetails['idVisit'], $idOrder);
            foreach ($itemsDetails as &$detail) {
                if ($detail['price'] == round($detail['price'])) {
                    $detail['price'] = round($detail['price']);
                }
            }
            $ecommerceDetail['itemDetails'] = $itemsDetails;
        }

        $actions = array_merge($actions, $ecommerceDetails);
    }

    /**
     * @param $idSite
     * @param $idVisitor
     * @return array
     * @throws \Exception
     */
    protected function queryEcommerceConversionsVisitorLifeTimeMetricsForVisitor($idSite, $idVisitor)
    {
        $sql = $this->getSqlEcommerceConversionsLifeTimeMetricsForIdGoal(GoalManager::IDGOAL_ORDER);
        $ecommerceOrders = Db::fetchRow($sql, array($idSite, @Common::hex2bin($idVisitor)));

        $sql = $this->getSqlEcommerceConversionsLifeTimeMetricsForIdGoal(GoalManager::IDGOAL_CART);
        $abandonedCarts = Db::fetchRow($sql, array($idSite, @Common::hex2bin($idVisitor)));

        return array(
            'totalEcommerceRevenue'      => $ecommerceOrders['lifeTimeRevenue'],
            'totalEcommerceConversions'  => $ecommerceOrders['lifeTimeConversions'],
            'totalEcommerceItems'        => $ecommerceOrders['lifeTimeEcommerceItems'],
            'totalAbandonedCartsRevenue' => $abandonedCarts['lifeTimeRevenue'],
            'totalAbandonedCarts'        => $abandonedCarts['lifeTimeConversions'],
            'totalAbandonedCartsItems'   => $abandonedCarts['lifeTimeEcommerceItems']
        );
    }


    /**
     * @param $ecommerceIdGoal
     * @return string
     */
    protected function getSqlEcommerceConversionsLifeTimeMetricsForIdGoal($ecommerceIdGoal)
    {
        $sql = "SELECT
                    COALESCE(SUM(" . LogAggregator::getSqlRevenue('revenue') . "), 0) as lifeTimeRevenue,
                    COUNT(*) as lifeTimeConversions,
                    COALESCE(SUM(" . LogAggregator::getSqlRevenue('items') . "), 0)  as lifeTimeEcommerceItems
					FROM  " . Common::prefixTable('log_visit') . " AS log_visit
					    LEFT JOIN " . Common::prefixTable('log_conversion') . " AS log_conversion
					    ON log_visit.idvisit = log_conversion.idvisit
					WHERE
					        log_visit.idsite = ?
					    AND log_visit.idvisitor = ?
						AND log_conversion.idgoal = " . $ecommerceIdGoal . "
        ";
        return $sql;
    }

    /**
     * @param $idVisit
     * @param $limit
     * @return array
     * @throws \Exception
     */
    protected function queryEcommerceConversionsForVisit($idVisit)
    {
        $limit = (int)Config::getInstance()->General['visitor_log_maximum_actions_per_visit'];
        $sql = "SELECT
						case idgoal when " . GoalManager::IDGOAL_CART
            . " then '" . Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART
            . "' else '" . Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER . "' end as type,
						idorder as orderId,
						" . LogAggregator::getSqlRevenue('revenue') . " as revenue,
						" . LogAggregator::getSqlRevenue('revenue_subtotal') . " as revenueSubTotal,
						" . LogAggregator::getSqlRevenue('revenue_tax') . " as revenueTax,
						" . LogAggregator::getSqlRevenue('revenue_shipping') . " as revenueShipping,
						" . LogAggregator::getSqlRevenue('revenue_discount') . " as revenueDiscount,
						items as items,
						log_conversion.server_time as serverTimePretty,
						log_conversion.idlink_va
					FROM " . Common::prefixTable('log_conversion') . " AS log_conversion
					WHERE idvisit = ?
						AND idgoal <= " . GoalManager::IDGOAL_ORDER . "
					ORDER BY server_time ASC
					LIMIT 0, $limit";
        $ecommerceDetails = Db::fetchAll($sql, array($idVisit));
        return $ecommerceDetails;
    }

    /**
     * @param $idVisit
     * @param $idOrder
     * @param $actionsLimit
     * @return array
     * @throws \Exception
     */
    protected function queryEcommerceItemsForOrder($idVisit, $idOrder)
    {
        $limit = (int)Config::getInstance()->General['visitor_log_maximum_actions_per_visit'];
        $sql = "SELECT
							log_action_sku.name as itemSKU,
							log_action_name.name as itemName,
							log_action_category.name as itemCategory,
							" . LogAggregator::getSqlRevenue('price') . " as price,
							quantity as quantity
						FROM " . Common::prefixTable('log_conversion_item') . "
							INNER JOIN " . Common::prefixTable('log_action') . " AS log_action_sku
							ON  idaction_sku = log_action_sku.idaction
							LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_name
							ON  idaction_name = log_action_name.idaction
							LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_category
							ON idaction_category = log_action_category.idaction
						WHERE idvisit = ?
							AND idorder = ?
							AND deleted = 0
						LIMIT 0, $limit
				";

        $bind = array($idVisit, $idOrder);

        $itemsDetails = Db::fetchAll($sql, $bind);
        return $itemsDetails;
    }
}