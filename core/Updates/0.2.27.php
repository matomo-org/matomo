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
use Piwik\DbHelper;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_0_2_27 extends Updates
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
        $migrations = array(
            $this->migration->db->addColumn('log_visit', 'visit_goal_converted', 'TINYINT( 1 ) NOT NULL', 'visit_total_time'),
            $this->migration->db->sql('CREATE TABLE `' . Common::prefixTable('goal') . "` (
                `idsite` int(11) NOT NULL,
                `idgoal` int(11) NOT NULL,
                `name` varchar(50) NOT NULL,
                `match_attribute` varchar(20) NOT NULL,
                `pattern` varchar(255) NOT NULL,
                `pattern_type` varchar(10) NOT NULL,
                `case_sensitive` tinyint(4) NOT NULL,
                `revenue` float NOT NULL,
                `deleted` tinyint(4) NOT NULL default '0',
                PRIMARY KEY  (`idsite`,`idgoal`)
            )", Updater\Migration\Db::ERROR_CODE_TABLE_EXISTS),

            $this->migration->db->sql('CREATE TABLE `' . Common::prefixTable('log_conversion') . '` (
                `idvisit` int(10) unsigned NOT NULL,
                `idsite` int(10) unsigned NOT NULL,
                `visitor_idcookie` char(32) NOT NULL,
                `server_time` datetime NOT NULL,
                `visit_server_date` date NOT NULL,
                `idaction` int(11) NOT NULL,
                `idlink_va` int(11) NOT NULL,
                `referer_idvisit` int(10) unsigned default NULL,
                `referer_type` int(10) unsigned default NULL,
                `referer_name` varchar(70) default NULL,
                `referer_keyword` varchar(255) default NULL,
                `visitor_returning` tinyint(1) NOT NULL,
                `location_country` char(3) NOT NULL,
                `location_continent` char(3) NOT NULL,
                `url` text NOT NULL,
                `idgoal` int(10) unsigned NOT NULL,
                `revenue` float default NULL,
                PRIMARY KEY  (`idvisit`,`idgoal`),
                KEY `index_idsite_date` (`idsite`,`visit_server_date`)
            )', Updater\Migration\Db::ERROR_CODE_TABLE_EXISTS),
        );

        $tables = DbHelper::getTablesInstalled();
        foreach ($tables as $tableName) {
            if (preg_match('/archive_/', $tableName) == 1) {
                $columns = array('idsite','date1','date2','name','ts_archived');
                $tableNameUnprefixed = Common::unprefixTable($tableName);
                $migrations[] = $this->migration->db->addIndex($tableNameUnprefixed, $columns, 'index_all');
            }
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
