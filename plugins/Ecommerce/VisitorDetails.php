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

    /**
     * @param $idSite
     * @param $idVisitor
     * @return array
     * @throws \Exception
     */
    public function queryEcommerceConversionsVisitorLifeTimeMetricsForVisitor($idSite, $idVisitor)
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
    private function getSqlEcommerceConversionsLifeTimeMetricsForIdGoal($ecommerceIdGoal)
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
}