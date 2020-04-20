<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;

class Updates_3_13_5_rc1 extends PiwikUpdates
{
    public function doUpdate(Updater $updater)
    {
        $this->addArchivesToPurge();
    }

    private function addArchivesToPurge()
    {
        $archivesToPurge = new ArchivesToPurgeDistributedList();

        $startOfProblem = Date::factory('2020-03-01 00:00:00');

        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE);
        foreach ($archiveTables as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);
            list($year, $month) = explode('_', $date);

            $dateObj = Date::factory("$year-$month-01 00:00:00");
            if ($dateObj->isEarlier($startOfProblem)) { // only add if the table is for march 2020 or above since that is when the problem appeared
                continue;
            }

            $archivesToPurge->add("{$year}_{$month}");
        }
    }
}