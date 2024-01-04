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
use Piwik\Option;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_2_0_a13 extends Updates
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
        // Renaming old archived records now that the plugin is called Referrers
        $migrations = array();
        $tables = \Piwik\DbHelper::getTablesInstalled();
        foreach ($tables as $tableName) {
            if (strpos($tableName, 'archive_') !== false) {
                $migrations[] = $this->migration->db->sql('UPDATE `' . $tableName . '` SET `name`=REPLACE(`name`, \'Referers_\', \'Referrers_\') WHERE `name` LIKE \'Referers_%\'');
            }
        }
        $errorCodeTableNotFound = '1146';

        // Rename custom segments containing Referers segments
        $migrations[] = $this->migration->db->sql('UPDATE `' . Common::prefixTable('segment') . '` SET `definition`=REPLACE(`definition`, \'referer\', \'referrer\') WHERE `definition` LIKE \'%referer%\'', $errorCodeTableNotFound);

        // Rename Referrers reports within scheduled reports
        $query = 'UPDATE `' . Common::prefixTable('report') . '` SET `reports`=REPLACE(`reports`, \'Referer\', \'Referrer\') WHERE `reports` LIKE \'%Referer%\'';
        $migrations[] = $this->migration->db->sql($query, $errorCodeTableNotFound);

        // Rename Referrers widgets in custom dashboards
        $query = 'UPDATE `' . Common::prefixTable('user_dashboard') . '` SET `layout`=REPLACE(`layout`, \'Referer\', \'Referrer\') WHERE `layout` LIKE \'%Referer%\'';
        $migrations[] = $this->migration->db->sql($query, $errorCodeTableNotFound);

        $query = 'UPDATE `' . Common::prefixTable('option') . '` SET `option_name` = \'version_ScheduledReports\' WHERE `option_name` = \'version_PDFReports\' ';
        $migrations[] = $this->migration->db->sql($query, Updater\Migration\Db::ERROR_CODE_DUPLICATE_ENTRY); // http://forum.piwik.org/read.php?2,106895

        $migrations[] = $this->migration->plugin->activate('Referrers');
        $migrations[] = $this->migration->plugin->activate('ScheduledReports');

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        // delete schema version_
        Option::delete('version_Referers');

        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        // old plugins deleted in 2.0-a17 update file
    }
}
