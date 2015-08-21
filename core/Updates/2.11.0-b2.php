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
use Piwik\Plugins\Dashboard\Model as DashboardModel;

/**
 * Update for version 2.11.0-b2.
 */
class Updates_2_11_0_b2 extends Updates
{

    public function getMigrationQueries(Updater $updater)
    {
        $sqls = array();

        // update dashboard to use new ecommerce widgets, they were moved from goals plugin to ecommerce
        $oldWidgets = array(
            array('module' => 'Goals', 'action' => 'getEcommerceLog',  'params' => array()),
            array('module' => 'Goals', 'action' => 'widgetGoalReport', 'params' => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER)),
        );

        $newWidgets = array(
            array('module' => 'Ecommerce', 'action' => 'getEcommerceLog',  'params' => array()),
            array('module' => 'Ecommerce', 'action' => 'widgetGoalReport', 'params' => array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER)),
        );

        $allDashboards = Db::get()->fetchAll(sprintf("SELECT * FROM %s", Common::prefixTable('user_dashboard')));

        foreach ($allDashboards as $dashboard) {
            $dashboardLayout = json_decode($dashboard['layout']);
            $dashboardLayout = DashboardModel::replaceDashboardWidgets($dashboardLayout, $oldWidgets, $newWidgets);

            $newLayout = json_encode($dashboardLayout);
            if ($newLayout != $dashboard['layout']) {
                $sqls["UPDATE " . Common::prefixTable('user_dashboard') . " SET layout = '".addslashes($newLayout)."' WHERE iddashboard = ".$dashboard['iddashboard']] = false;
            }
        }

        return $sqls;
    }

    public function doUpdate(Updater $updater)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();

        try {
            $pluginManager->activatePlugin('Ecommerce');
        } catch (\Exception $e) {
        }

        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
