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
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 2.11.0-b2.
 */
class Updates_2_11_0_b2 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $migrations = array();

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

        $sql = "UPDATE " . Common::prefixTable('user_dashboard') . " SET layout = ? WHERE iddashboard = ?";

        foreach ($allDashboards as $dashboard) {
            $dashboardLayout = json_decode($dashboard['layout']);
            $dashboardLayout = DashboardModel::replaceDashboardWidgets($dashboardLayout, $oldWidgets, $newWidgets);

            $newLayout = json_encode($dashboardLayout);
            if ($newLayout != $dashboard['layout']) {
                $migrations[] = $this->migration->db->boundSql($sql, array($newLayout, $dashboard['iddashboard']));
            }
        }

        $migrations[] = $this->migration->plugin->activate('Ecommerce');

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
