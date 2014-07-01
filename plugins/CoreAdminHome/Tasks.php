<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\DataAccess\ArchivePurger;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;

class Tasks extends \Piwik\Plugin\Tasks
{
    public function schedule()
    {
        // general data purge on older archive tables, executed daily
        $this->daily('purgeOutdatedArchives', null, self::HIGH_PRIORITY);

        // lowest priority since tables should be optimized after they are modified
        $this->daily('optimizeArchiveTable', null, self::LOWEST_PRIORITY);
    }

    public function purgeOutdatedArchives()
    {
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();
        foreach ($archiveTables as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);
            list($year, $month) = explode('_', $date);

            // Somehow we may have archive tables created with older dates, prevent exception from being thrown
            if($year > 1990) {
                ArchivePurger::purgeOutdatedArchives(Date::factory("$year-$month-15"));
            }
        }
    }

    public function optimizeArchiveTable()
    {
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();
        Db::optimizeTables($archiveTables);
    }
}