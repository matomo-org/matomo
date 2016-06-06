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

/**
 */
class Updates_1_7_2_rc7 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array(
            'ALTER TABLE `' . Common::prefixTable('user_dashboard') . '`
		        ADD `name` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `iddashboard`' => 1060,
        );
    }

    public function doUpdate(Updater $updater)
    {
        try {
            $dashboards = Db::fetchAll('SELECT * FROM `' . Common::prefixTable('user_dashboard') . '`');
            foreach ($dashboards as $dashboard) {
                $idDashboard = $dashboard['iddashboard'];
                $login = $dashboard['login'];
                $layout = $dashboard['layout'];
                $layout = html_entity_decode($layout);
                $layout = str_replace("\\\"", "\"", $layout);
                Db::query('UPDATE `' . Common::prefixTable('user_dashboard') . '` SET layout = ? WHERE iddashboard = ? AND login = ?', array($layout, $idDashboard, $login));
            }
            $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
        } catch (\Exception $e) {
        }
    }
}
