<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Access;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Plugins\Installation\ServerFilesGenerator;
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

        $queries = $this->getDashboardMigrationSqls($allDashboards, $allGoals);
        $queries = $this->getPluginSettingsMigrationQueries($queries, $db);
        $queries = $this->getSiteSettingsMigrationQueries($queries);

        return $queries;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
        $this->migratePluginEmailUpdateSetting();

        // added .woff and woff2 whitelisted file for apache webserver
        ServerFilesGenerator::deleteHtAccessFiles();
        ServerFilesGenerator::createHtAccessFiles();
    }

    private function migratePluginEmailUpdateSetting()
    {
        $isEnabled = Option::get('enableUpdateCommunicationPlugins');

        Access::doAsSuperUser(function () use ($isEnabled) {
            $settings = StaticContainer::get('Piwik\Plugins\CoreUpdater\SystemSettings');
            $settings->sendPluginUpdateEmail->setValue(!empty($isEnabled));
            $settings->save();
        });
    }

    /**
     * @param $queries
     * @param Db $db
     * @return mixed
     */
    private function getPluginSettingsMigrationQueries($queries, $db)
    {
        $pluginSettingsTableName = $this->getPluginSettingsTableName();
        $dbSettings = new Db\Settings();
        $engine = $dbSettings->getEngine();

        $pluginSettingsTable = "CREATE TABLE $pluginSettingsTableName (
                          `plugin_name` VARCHAR(60) NOT NULL,
                          `setting_name` VARCHAR(255) NOT NULL,
                          `setting_value` LONGTEXT NOT NULL,
                          `user_login` VARCHAR(100) NOT NULL DEFAULT '',
                              INDEX(plugin_name, user_login)
                            ) ENGINE=$engine DEFAULT CHARSET=utf8
            ";
        $queries[$pluginSettingsTable] = 1050;

        $optionTable = Common::prefixTable('option');
        $query = 'SELECT `option_name`, `option_value` FROM `' . $optionTable . '` WHERE `option_name` LIKE "Plugin_%_Settings"';
        $options = Db::get()->fetchAll($query);

        foreach ($options as $option) {
            $name = $option['option_name'];
            $pluginName = str_replace(array('Plugin_', '_Settings'), '', $name);
            $values = @unserialize($option['option_value']);

            if (empty($values)) {
                continue;
            }

            foreach ($values as $settingName => $settingValue) {
                if (!is_array($settingValue)) {
                    $settingValue = array($settingValue);
                }

                foreach ($settingValue as $val) {
                    $queries[$this->createPluginSettingQuery($pluginName, $settingName, $val)] = 1062;
                }
            }
        }

        $queries[$query = sprintf('DELETE FROM `%s` WHERE `option_name` like "Plugin_%%_Settings"', $optionTable)] = false;

        return $queries;
    }

    /**
     * @param $queries
     * @param Db $db
     * @return mixed
     */
    private function getSiteSettingsMigrationQueries($queries)
    {
        $table = Common::prefixTable('site_setting');

        $pluginSettingsTable = "ALTER TABLE $table ADD COLUMN `plugin_name` VARCHAR(60) NOT NULL AFTER `idsite`";
        $queries[$pluginSettingsTable] = 1060;
        $queries["ALTER TABLE $table DROP PRIMARY KEY, ADD INDEX(idsite, plugin_name);"] = false;

        // we cannot migrate existing settings as we do not know the related plugin name, but this feature
        // (measurablesettings) was not really used anyway. If a migration is somewhere really needed it has to be
        // handled in the plugin
        $queries[sprintf('DELETE FROM `%s`', $table)] = false;

        return $queries;
    }

    private function createPluginSettingQuery($pluginName, $settingName, $settingValue)
    {
        $table = $this->getPluginSettingsTableName();

        $login = '';
        if (preg_match('/^.+#(.+)#$/', $settingName, $matches)) {
            $login = $matches[1];
            $settingName = str_replace('#' . $login . '#', '', $settingName);
        }

        $db = Db::get();

        $query  = sprintf("INSERT INTO %s (plugin_name, setting_name, setting_value, user_login) VALUES ", $table);
        $query .= sprintf("(%s, %s, %s, %s)", $db->quote($pluginName), $db->quote($settingName), $db->quote($settingValue), $db->quote($login));

        return $query;
    }

    private function getPluginSettingsTableName()
    {
        return Common::prefixTable('plugin_setting');
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
