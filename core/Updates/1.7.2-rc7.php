<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_7_2_rc7 extends Updates
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
        return array(
            $this->migration->db->addColumn('user_dashboard', 'name', 'VARCHAR( 100 ) NULL DEFAULT NULL', 'iddashboard')
        );
    }

    public function doUpdate(Updater $updater)
    {
        try {
            $migrations = array();

            $table = Common::prefixTable('user_dashboard');
            $dashboards = Db::fetchAll('SELECT iddashboard, login, layout FROM `' . $table . '`');

            $updateQuery = 'UPDATE `' . $table . '` SET layout = ? WHERE iddashboard = ? AND login = ?';

            foreach ($dashboards as $dashboard) {
                $idDashboard = $dashboard['iddashboard'];
                $login = $dashboard['login'];
                $layout = $dashboard['layout'];
                $layout = html_entity_decode($layout, ENT_COMPAT | ENT_HTML401, 'UTF-8');
                $layout = str_replace("\\\"", "\"", $layout);

                $migrations[] = $this->migration->db->boundSql($updateQuery, array($layout, $idDashboard, $login));
            }

            $updater->executeMigrations(__FILE__, $migrations);
            $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
        } catch (\Exception $e) {
        }
    }
}
