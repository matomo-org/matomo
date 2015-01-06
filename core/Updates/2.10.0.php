<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Plugins\Dashboard\Model AS DashboardModel;

/**
 * Update for version 2.10.0.
 */
class Updates_2_10_0 extends Updates
{

    static function getSql()
    {
        $sqls = array('# ATTENTION: This update script will execute some more SQL queries than that below as it is necessary to rebuilt some archives #' => false);

        // update dashboard to use new widgets
        $oldWidgets = array(
            array('module' => 'Goals', 'action' => 'getEcommerceLog',  'params' => array()),
            array('module' => 'Goals', 'action' => 'widgetGoalReport', 'params' => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER)),
        );

        $newWidgets = array(
            array('module' => 'Ecommerce', 'action' => 'getEcommerceLog',  'params' => array()),
            array('module' => 'Ecommerce', 'action' => 'widgetGoalReport', 'params' => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER)),
        );

        $allDashboards = Db::get()->fetchAll(sprintf("SELECT * FROM %s", Common::prefixTable('user_dashboard')));

        foreach($allDashboards AS $dashboard) {

            $dashboardLayout = json_decode($dashboard['layout']);
            $dashboardLayout = DashboardModel::replaceDashboardWidgets($dashboardLayout, $oldWidgets, $newWidgets);

            $newLayout = json_encode($dashboardLayout);
            if ($newLayout != $dashboard['layout']) {
                $sqls["UPDATE " . Common::prefixTable('user_dashboard') . " SET layout = '".addslashes($newLayout)."' WHERE iddashboard = ".$dashboard['iddashboard']] = false;
            }
        }

        return $sqls;
    }

    static function update()
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();

        try {
            $pluginManager->activatePlugin('Ecommerce');
        } catch(\Exception $e) {
        }

        Updater::updateDatabase(__FILE__, self::getSql());
    }
}
