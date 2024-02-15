<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Access;
use Piwik\Common;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\Installation\ServerFilesGenerator;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;
use Piwik\Plugins\Dashboard;

/**
 * Update for version 3.0.0-b1.
 */
class Updates_3_0_0_b1 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    private $pluginSettingsTable = 'plugin_setting';
    private $siteSettingsTable = 'site_setting';

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     * @param Updater $updater
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $db = Db::get();
        $allGoals = $db->fetchAll(sprintf("SELECT DISTINCT idgoal FROM %s", Common::prefixTable('goal')));
        $allDashboards = $db->fetchAll(sprintf("SELECT * FROM %s", Common::prefixTable('user_dashboard')));

        $migrations = $this->getDashboardMigrations($allDashboards, $allGoals);
        $migrations = $this->getPluginSettingsMigrations($migrations);
        $migrations = $this->getSiteSettingsMigrations($migrations);
        $migrations = $this->getBigIntPreventOverflowMigrations($migrations);

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
        $this->migratePluginEmailUpdateSetting();

        // added .woff and woff2 allowlisted file for apache webserver
        ServerFilesGenerator::deleteHtAccessFiles();
        ServerFilesGenerator::createHtAccessFiles();

        // Renamed plugin ExampleRssWidget -> RssWidget
        \Piwik\Plugin\Manager::getInstance()->activatePlugin('RssWidget');
        \Piwik\Plugin\Manager::getInstance()->deactivatePlugin('ExampleRssWidget');
    }

    private function migratePluginEmailUpdateSetting()
    {
        $isEnabled = Option::get('enableUpdateCommunicationPlugins');

        Access::doAsSuperUser(function () use ($isEnabled) {
            $table = Common::prefixTable('plugin_setting');
            $sql  = "INSERT INTO $table (`plugin_name`, `user_login`, `setting_name`, `setting_value`) VALUES (?, ?, ?, ?)";
            $bind = array('CoreUpdater', '', 'enable_plugin_update_communication', (int) !empty($isEnabled));
            Db::query($sql, $bind);
        });
    }

    /**
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getBigIntPreventOverflowMigrations($queries)
    {
        $queries[] = $this->migration->db->changeColumnTypes('log_visit', array(
            'idvisit' => 'BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT'
        ));

        $queries[] = $this->migration->db->changeColumnTypes('log_conversion_item', array(
            'idvisit' => 'BIGINT(10) UNSIGNED NOT NULL',
        ));

        $queries[] = $this->migration->db->changeColumnTypes('log_conversion', array(
            'idvisit' => 'BIGINT(10) UNSIGNED NOT NULL',
            'idlink_va' => 'BIGINT(10) UNSIGNED default NULL',
        ));

        $queries[] = $this->migration->db->changeColumnTypes('log_link_visit_action', array(
            'idlink_va' => 'BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
            'idvisit' => 'BIGINT(10) UNSIGNED NOT NULL',

            // Note; this column is made NULLable for #9231
            'idaction_name_ref' => 'INTEGER(10) UNSIGNED NULL',
        ));

        return $queries;
    }

    /**
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getPluginSettingsMigrations($queries)
    {
        $queries[] = $this->migration->db->createTable($this->pluginSettingsTable, array(
            'plugin_name' => 'VARCHAR(60) NOT NULL',
            'setting_name' => 'VARCHAR(255) NOT NULL',
            'setting_value' => 'LONGTEXT NOT NULL',
            'user_login' => "VARCHAR(100) NOT NULL DEFAULT ''",
        ));
        $queries[] = $this->migration->db->addIndex($this->pluginSettingsTable, array('plugin_name', 'user_login'));

        $optionTable = Common::prefixTable('option');
        $query = 'SELECT `option_name`, `option_value` FROM `' . $optionTable . '` WHERE `option_name` LIKE "Plugin_%_Settings"';
        $options = Db::get()->fetchAll($query);

        foreach ($options as $option) {
            $name = $option['option_name'];
            $pluginName = str_replace(array('Plugin_', '_Settings'), '', $name);
            $values = Common::safe_unserialize($option['option_value']);

            if (empty($values)) {
                continue;
            }

            foreach ($values as $settingName => $settingValue) {
                if (!is_array($settingValue)) {
                    $settingValue = array($settingValue);
                }

                foreach ($settingValue as $val) {
                    $queries[] = $this->createPluginSettingQuery($pluginName, $settingName, $val);
                }
            }
        }

        $queries[] = $this->migration->db->sql(sprintf('DELETE FROM `%s` WHERE `option_name` like "Plugin_%%_Settings"', $optionTable));

        return $queries;
    }

    /**
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getSiteSettingsMigrations($queries)
    {
        $table = $this->siteSettingsTable;

        // we cannot migrate existing settings as we do not know the related plugin name, but this feature
        // (measurablesettings) was not used anyway. also see https://github.com/piwik/piwik/issues/10703
        // we make sure to recreate the table as it might not have existed for some users instead of just
        // deleting the content of it
        $queries[] = $this->migration->db->dropTable($table);
        $queries[] = $this->migration->db->createTable($table, array(
            'idsite' => 'INTEGER(10) UNSIGNED NOT NULL',
            'plugin_name' => 'VARCHAR(60) NOT NULL',
            'setting_name' => 'VARCHAR(255) NOT NULL',
            'setting_value' => 'LONGTEXT NOT NULL',
        ));

        $table = Common::prefixTable($table);
        $queries[] = $this->migration->db->sql(
            "ALTER TABLE `$table` ADD INDEX(idsite, plugin_name);",
            Migration\Db::ERROR_CODE_COLUMN_NOT_EXISTS
        );

        return $queries;
    }

    private function createPluginSettingQuery($pluginName, $settingName, $settingValue)
    {
        $login = '';
        if (preg_match('/^.+#(.+)#$/', $settingName, $matches)) {
            $login = $matches[1];
            $settingName = str_replace('#' . $login . '#', '', $settingName);
        }

        return $this->migration->db->insert($this->pluginSettingsTable, array(
            'plugin_name' => $pluginName,
            'setting_name' => $settingName,
            'setting_value' => $settingValue,
            'user_login' => $login
        ));
    }

    private function getDashboardMigrations($allDashboards, $allGoals)
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
                        'viewDataTable' => 'VisitorLog',
                        'small' => '1',
                    ),
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

        $table = Common::prefixTable('user_dashboard');
        $sql = sprintf('UPDATE %s SET layout = ? WHERE iddashboard = ?', $table);

        foreach ($allDashboards as $dashboard) {
            $dashboardLayout = json_decode($dashboard['layout']);

            $dashboardLayout = Dashboard\Model::replaceDashboardWidgets($dashboardLayout, $oldWidgets, $newWidgets);

            $newLayout = json_encode($dashboardLayout);
            if ($newLayout != $dashboard['layout']) {
                $sqls[] = $this->migration->db->boundSql($sql, array($newLayout, $dashboard['iddashboard']));
            }
        }

        return $sqls;
    }
}
