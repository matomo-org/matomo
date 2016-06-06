<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Updater;
use Piwik\Updates;

class Updates_2_10_0_b7 extends Updates
{

    public function getMigrationQueries(Updater $updater)
    {
        $sqls = array();

        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();

        $archiveBlobTables = array_filter($archiveTables, function ($name) {
            return ArchiveTableCreator::getTypeFromTableName($name) == ArchiveTableCreator::BLOB_TABLE;
        });

        foreach ($archiveBlobTables as $table) {
            $sqls["UPDATE " . $table . " SET name = 'Resolution_resolution' WHERE name = 'UserSettings_resolution'"] = false;
            $sqls["UPDATE " . $table . " SET name = 'Resolution_configuration' WHERE name = 'UserSettings_configuration'"] = false;
        }

        return $sqls;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
