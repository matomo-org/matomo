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
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Plugins\Dashboard;

/**
 * Update for version 3.0.0-b1.
 */
class Updates_3_0_0_b1 extends Updates
{
    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     * @return array
     */
    public function getMigrationQueries(Updater $updater)
    {
        $db = Db::get();
        $allGoals = $db->fetchAll(sprintf("SELECT DISTINCT idgoal FROM %s", Common::prefixTable('goal')));
        $allDashboards = $db->fetchAll(sprintf("SELECT * FROM %s", Common::prefixTable('user_dashboard')));

        return $this->getDashboardMigrationSqls($allDashboards, $allGoals);
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }

    private function getDashboardMigrationSqls($allDashboards, $allGoals)
    {
        $sqls = array();


        // update dashboard to use new widgets
        $oldWidgets = array(
            array (
                'module' => 'VisitTime',
                'action' => 'getVisitInformationPerServerTime',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'VisitTime',
                'action' => 'getVisitInformationPerLocalTime',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'VisitTime',
                'action' => 'getByDayOfWeek',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'VisitsSummary',
                'action' => 'getEvolutionGraph',
                'params' =>
                    array (
                        'columns' => array ('nb_visits'),
                    ),
            ),array (
                'module' => 'VisitsSummary',
                'action' => 'getSparklines',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'VisitsSummary',
                'action' => 'index',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'Live',
                'action' => 'getVisitorLog',
                'params' =>
                    array ('small' => 1),
            ),array (
                'module' => 'VisitorInterest',
                'action' => 'getNumberOfVisitsPerVisitDuration',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'VisitorInterest',
                'action' => 'getNumberOfVisitsPerPage',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'VisitFrequency',
                'action' => 'getSparklines',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'VisitFrequency',
                'action' => 'getEvolutionGraph',
                'params' =>
                    array (
                        'columns' => array ('nb_visits_returning'),
                    ),
            ),array (
                'module' => 'DevicesDetection',
                'action' => 'getBrowserEngines',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'Referrers',
                'action' => 'getReferrerType',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'Referrers',
                'action' => 'getAll',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'Referrers',
                'action' => 'getSocials',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'Goals',
                'action' => 'widgetGoalsOverview',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'Goals',
                'action' => 'getItemsSku',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'Goals',
                'action' => 'getItemsName',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'Goals',
                'action' => 'getItemsCategory',
                'params' =>
                    array (
                    ),
            ),array (
                'module' => 'Ecommerce',
                'action' => 'widgetGoalReport',
                'params' =>
                    array (
                        'idGoal' => 'ecommerceOrder',
                    ),
            ),
        );

        foreach ($allGoals as $goal) {
            $oldWidgets[] = array (
                'module' => 'Goals',
                'action' => 'widgetGoalReport',
                'params' =>
                    array (
                        'idGoal' => (int) $goal['idgoal'],
                    ));
        }

        $newWidgets = array(
            array (
                'module' => 'VisitTime',
                'action' => 'getVisitInformationPerServerTime',
                'params' =>
                    array (
                        'viewDataTable' => 'graphVerticalBar',
                    ),
            ),array (
                'module' => 'VisitTime',
                'action' => 'getVisitInformationPerLocalTime',
                'params' =>
                    array (
                        'viewDataTable' => 'graphVerticalBar',
                    ),
            ),array (
                'module' => 'VisitTime',
                'action' => 'getByDayOfWeek',
                'params' =>
                    array (
                        'viewDataTable' => 'graphVerticalBar'
                    ),
            ),array (
                'module' => 'VisitsSummary',
                'action' => 'getEvolutionGraph',
                'params' =>
                    array (
                        'forceView' => '1',
                        'viewDataTable' => 'graphEvolution',
                    ),
            ),array (
                'module' => 'VisitsSummary',
                'action' => 'get',
                'params' =>
                    array (
                        'forceView' => '1',
                        'viewDataTable' => 'sparklines',
                    ),
            ),array (
                'module' => 'CoreHome',
                'action' => 'renderWidgetContainer',
                'uniqueId' => 'widgetVisitOverviewWithGraph',
                'params' =>
                    array (
                        'containerId' => 'VisitOverviewWithGraph',
                    ),
            ),array (
                'module' => 'Live',
                'action' => 'getLastVisitsDetails',
                'params' =>
                    array (
                        'forceView' => '1',
                        'viewDataTable' => 'Piwik\\Plugins\\Live\\VisitorLog',
                        'small' => '1',
                    ),
            ),array (
                'module' => 'VisitorInterest',
                'action' => 'getNumberOfVisitsPerVisitDuration',
                'params' =>
                    array (
                        'viewDataTable' => 'cloud',
                    ),
            ),array (
                'module' => 'VisitorInterest',
                'action' => 'getNumberOfVisitsPerPage',
                'params' =>
                    array (
                        'viewDataTable' => 'cloud',
                    ),
            ),array (
                'module' => 'VisitFrequency',
                'action' => 'get',
                'params' =>
                    array (
                        'forceView' => '1',
                        'viewDataTable' => 'sparklines'
                    ),
            ),array (
                'module' => 'VisitFrequency',
                'action' => 'getEvolutionGraph',
                'params' =>
                    array (
                        'forceView' => 1,
                        'viewDataTable' => 'graphEvolution',
                    ),
            ),array (
                'module' => 'DevicesDetection',
                'action' => 'getBrowserEngines',
                'params' =>
                    array (
                        'viewDataTable' => 'graphPie',
                    ),
            ),array (
                'module' => 'Referrers',
                'action' => 'getReferrerType',
                'params' =>
                    array (
                        'viewDataTable' => 'tableAllColumns',
                    ),
            ),array (
                'module' => 'Referrers',
                'action' => 'getAll',
                'params' =>
                    array (
                        'viewDataTable' => 'tableAllColumns',
                    ),
            ),array (
                'module' => 'Referrers',
                'action' => 'getSocials',
                'params' =>
                    array (
                        'viewDataTable' => 'graphPie',
                    ),
            ),array (
                'module' => 'CoreHome',
                'action' => 'renderWidgetContainer',
                'uniqueId' => 'widgetGoalsOverview',
                'params' =>
                    array (
                        'containerId' => 'GoalsOverview'
                    ),
            ),array (
                'module' => 'Goals',
                'action' => 'getItemsSku',
                'params' => array (),
            ),array (
                'module' => 'Goals',
                'action' => 'getItemsName',
                'params' => array (),
            ),array (
                'module' => 'Goals',
                'action' => 'getItemsCategory',
                'params' => array (),
            ),array (
                'module' => 'CoreHome',
                'action' => 'renderWidgetContainer',
                'uniqueId' => 'widgetEcommerceOverview',
                'params' =>
                    array (
                        'containerId' => 'EcommerceOverview',
                    ),
            ),
        );

        foreach ($allGoals as $goal) {
            $newWidgets[] = array (
                'module' => 'CoreHome',
                'action' => 'renderWidgetContainer',
                'uniqueId' => 'widgetGoal_' . (int) $goal['idgoal'],
                'params' =>
                    array (
                        'containerId' => 'Goal_' . (int) $goal['idgoal'],
                    ));
        }

        foreach ($allDashboards as $dashboard) {
            $dashboardLayout = json_decode($dashboard['layout']);

            $dashboardLayout = Dashboard\Model::replaceDashboardWidgets($dashboardLayout, $oldWidgets, $newWidgets);

            $newLayout = json_encode($dashboardLayout);
            if ($newLayout != $dashboard['layout']) {
                $sqls["UPDATE " . Common::prefixTable('user_dashboard') . " SET layout = '".addslashes($newLayout)."' WHERE iddashboard = ".$dashboard['iddashboard']] = false;
            }
        }

        return $sqls;
    }
}
