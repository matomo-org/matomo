<?php

/**
 * Matomo - free/libre analytics platform
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

        $startOfProblem = Date::factory('2020-01-01 00:00:00');

        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE);
        foreach ($archiveTables as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);
            list($year, $month) = explode('_', $date);

            // only add if the table is for jan 2020 or above since tables w/ that date will be most affected
            try {
                $dateObj = Date::factory("$year-$month-01 00:00:00");
            } catch (\Exception $ex) {
                continue; // date is too old for some reason
            }

            if ($dateObj->isEarlier($startOfProblem)) {
                continue;
            }

            $archivesToPurge->add("{$year}_{$month}");
        }
    }
}
