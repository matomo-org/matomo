<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce;

use Piwik\Common;
use Piwik\DataAccess\LogAggregator;
use Piwik\Piwik;
use Piwik\Plugins\Ecommerce\Columns\ProductCategory;
use Piwik\Plugins\Live\VisitorDetailsAbstract;
use Piwik\Site;
use Piwik\Tracker\GoalManager;
use Piwik\View;

class VisitorDetails extends VisitorDetailsAbstract
{
    const CATEGORY_COUNT = 5;
    const DEFAULT_LIFETIME_STAT = array(
            'lifeTimeRevenue' => 0,
            'lifeTimeConversions' => 0,
            'lifeTimeEcommerceItems' => 0);

    public function extendVisitorDetails(&$visitor)
    {
        if(Site::isEcommerceEnabledFor($visitor['idSite']))
        {
            $ecommerceMetrics                     = $this->queryEcommerceConversionsVisitorLifeTimeMetricsForVisitor($visitor['idSite'],
                $visitor['visitorId']);
            $visitor['totalEcommerceRevenue']     = $ecommerceMetrics['totalEcommerceRevenue'];
            $visitor['totalEcommerceConversions'] = $ecommerceMetrics['totalEcommerceConversions'];
            $visitor['totalEcommerceItems']       = $ecommerceMetrics['totalEcommerceItems'];

            $visitor['totalAbandonedCartsRevenue'] = $ecommerceMetrics['totalAbandonedCartsRevenue'];
            $visitor['totalAbandonedCarts']        = $ecommerceMetrics['totalAbandonedCarts'];
            $visitor['totalAbandonedCartsItems']   = $ecommerceMetrics['totalAbandonedCartsItems'];
        }
    }

    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        if (empty($action['productViewName'])) {
            unset($action['productViewName']);
        }
        if (empty($action['productViewSku'])) {
            unset($action['productViewSku']);
        }
        if (empty($action['productViewPrice'])) {
            unset($action['productViewPrice']);
        }

        $categories = [];
        for($i = 1; $i <= ProductCategory::PRODUCT_CATEGORY_COUNT; $i++) {
            if (!empty($action['productViewCategory'.$i])) {
                $categories[] = $action['productViewCategory'.$i];
            }

            unset($action['productViewCategory'.$i]);
        }
        if (!empty($categories)) {
            $action['productViewCategories'] = $categories;
        }
    }

    public function renderActionTooltip($action, $visitInfo)
    {
        if (!isset($action['productViewName']) && !isset($action['productViewSku']) &&
            !isset($action['productViewPrice']) && !isset($action['productViewCategories'])) {
            return [];
        }

        $view            = new View('@Ecommerce/_actionTooltip');
        $view->sendHeadersWhenRendering = false;
        $view->action    = $action;
        $view->visitInfo = $visitInfo;
        return [[ 15, $view->render() ]];
    }

    public function provideActionsForVisitIds(&$actions, $idVisits)
    {
        $ecommerceDetails = $this->queryEcommerceConversionsForVisits($idVisits);

        // use while / array_shift combination instead of foreach to save memory
        while (is_array($ecommerceDetails) && count($ecommerceDetails)) {
            $ecommerceDetail = array_shift($ecommerceDetails);

            $idVisit = $ecommerceDetail['idvisit'];

            unset($ecommerceDetail['idvisit']);

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
                    if (!is_numeric($value)) {
                        $ecommerceDetail[$column] = 0;
                    } else if ($value == round($value)) {
                        $ecommerceDetail[$column] = round($value);
                    }
                }
            }

            $idOrder = isset($ecommerceDetail['orderId']) ? $ecommerceDetail['orderId'] : GoalManager::ITEM_IDORDER_ABANDONED_CART;

            $itemsDetails = $this->queryEcommerceItemsForOrder($idVisit, $idOrder);
            foreach ($itemsDetails as &$detail) {
                if ($detail['price'] == round($detail['price'])) {
                    $detail['price'] = round($detail['price']);
                }
            }
            $ecommerceDetail['itemDetails'] = $itemsDetails;

            $actions[$idVisit][] = $ecommerceDetail;
        }
    }

    /**
     * @param $idSite
     * @param $idVisitor
     * @return array
     * @throws \Exception
     */
    protected function queryEcommerceConversionsVisitorLifeTimeMetricsForVisitor($idSite, $idVisitor)
    {
        $sql             = $this->getSqlEcommerceConversionsLifeTimeMetricsForIdGoal();
        $lifeTimeStats = $this->getDb()->fetchAll($sql, array($idSite, @Common::hex2bin($idVisitor)));

        $defaultStats = array_fill_keys([GoalManager::IDGOAL_CART, GoalManager::IDGOAL_ORDER], self::DEFAULT_LIFETIME_STAT);

        $lifeTimeStatsByGoal = array_reduce($lifeTimeStats, function ($carry, $statRow) {
            $idgoal = $statRow['idgoal'];
            $carry[$idgoal] = array_merge($carry[$idgoal], $statRow);
            return $carry;
        },$defaultStats);

        $ecommerceOrders = $lifeTimeStatsByGoal[GoalManager::IDGOAL_ORDER];
        $abandonedCarts = $lifeTimeStatsByGoal[GoalManager::IDGOAL_CART];

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
     * Returns and SQL string that queries for `lifeTimeRevenue`, `lifeTimeConversions`, and `lifeTimeEcommerceItems` grouped by
     * `idgoal` for abandoned carts and orders.
     * @return string
     */
    protected function getSqlEcommerceConversionsLifeTimeMetricsForIdGoal()
    {
        $sql = "SELECT
                    idgoal,
                    COALESCE(SUM(" . LogAggregator::getSqlRevenue('revenue') . "), 0) as lifeTimeRevenue,
                    COUNT(*) as lifeTimeConversions,
                    COALESCE(SUM(" . LogAggregator::getSqlRevenue('items') . "), 0)  as lifeTimeEcommerceItems
					FROM  " . Common::prefixTable('log_visit') . " AS log_visit
					    STRAIGHT_JOIN " . Common::prefixTable('log_conversion') . " AS log_conversion
					    ON log_visit.idvisit = log_conversion.idvisit
					WHERE
					        log_visit.idsite = ?
					    AND log_visit.idvisitor = ?
						AND log_conversion.idgoal IN ( " . GoalManager::IDGOAL_CART . ", " . GoalManager::IDGOAL_ORDER . " )
                    GROUP BY idgoal
        ";
        return $sql;
    }

    /**
     * @param $idVisit
     * @param $limit
     * @return array
     * @throws \Exception
     */
    protected function queryEcommerceConversionsForVisits($idVisits)
    {
        $sql = "SELECT
						log_conversion.idvisit,
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
						log_conversion.idlink_va,
						log_link_visit_action.idpageview
					FROM " . Common::prefixTable('log_conversion') . " AS log_conversion
		       LEFT JOIN " . Common::prefixTable('log_link_visit_action') . " AS log_link_visit_action
		              ON log_link_visit_action.idlink_va = log_conversion.idlink_va
					WHERE log_conversion.idvisit IN ('" . implode("','", $idVisits) . "')
						AND idgoal <= " . GoalManager::IDGOAL_ORDER . "
					ORDER BY log_conversion.idvisit, log_conversion.server_time ASC";
        $ecommerceDetails = $this->getDb()->fetchAll($sql);
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
        $categorySelects = [];
        $categoryJoins = [];
        for ($i = 0; $i < self::CATEGORY_COUNT; ++$i) {
            $suffix = $i === 0 ? '' : $i;
            $column = $i === 0 ? 'idaction_category' : ('idaction_category' . ($i + 1));
            $categorySelects[] = 'log_action_category' . $suffix . '.name as itemCategory' . $suffix;
            $categoryJoins[] = 'LEFT JOIN ' . Common::prefixTable('log_action') . " AS log_action_category$suffix
                                       ON $column = log_action_category$suffix.idaction";
        }
        $categorySelects = implode(',', $categorySelects);
        $categoryJoins = implode("\n", $categoryJoins);

        $sql = "SELECT
							log_action_sku.name as itemSKU,
							log_action_name.name as itemName,
							$categorySelects,
							" . LogAggregator::getSqlRevenue('price') . " as price,
							quantity as quantity
						FROM " . Common::prefixTable('log_conversion_item') . "
							INNER JOIN " . Common::prefixTable('log_action') . " AS log_action_sku
							ON  idaction_sku = log_action_sku.idaction
							LEFT JOIN " . Common::prefixTable('log_action') . " AS log_action_name
							ON  idaction_name = log_action_name.idaction
							$categoryJoins
						WHERE idvisit = ?
							AND idorder = ?
							AND deleted = 0
				";

        $bind = array($idVisit, $idOrder);

        $itemsDetails = $this->getDb()->fetchAll($sql, $bind);

        // create categories array for each item
        foreach ($itemsDetails as &$item) {
            $categories = [];
            for ($i = 0; $i < self::CATEGORY_COUNT; ++$i) {
                $suffix = $i === 0 ? '' : $i;
                if (empty($item['itemCategory' . $suffix])) {
                    continue;
                }

                $categories[] = trim($item['itemCategory' . $suffix]);
            }
            $item['categories'] = array_filter($categories);

            // remove itemCategotyN properties, except 'itemCategory' property for BC
            for ($i = 1; $i < self::CATEGORY_COUNT; ++$i) {
                unset($item['itemCategory' . $i]);
            }
        }

        return $itemsDetails;
    }

    public function initProfile($visits, &$profile)
    {
        if (Site::isEcommerceEnabledFor($visits->getFirstRow()->getColumn('idSite'))) {
            $profile['totalEcommerceRevenue']      = 0;
            $profile['totalEcommerceConversions']  = 0;
            $profile['totalEcommerceItems']        = 0;
            $profile['totalAbandonedCarts']        = 0;
            $profile['totalAbandonedCartsRevenue'] = 0;
            $profile['totalAbandonedCartsItems']   = 0;
        }
    }

    public function finalizeProfile($visits, &$profile)
    {
        $lastVisit = $visits->getLastRow();
        if ($lastVisit && Site::isEcommerceEnabledFor($lastVisit->getColumn('idSite'))) {
            $profile['totalEcommerceRevenue']      = $lastVisit->getColumn('totalEcommerceRevenue');
            $profile['totalEcommerceConversions']  = $lastVisit->getColumn('totalEcommerceConversions');
            $profile['totalEcommerceItems']        = $lastVisit->getColumn('totalEcommerceItems');
            $profile['totalAbandonedCartsRevenue'] = $lastVisit->getColumn('totalAbandonedCartsRevenue');
            $profile['totalAbandonedCarts']        = $lastVisit->getColumn('totalAbandonedCarts');
            $profile['totalAbandonedCartsItems']   = $lastVisit->getColumn('totalAbandonedCartsItems');
        }
    }
}