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

        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled(ArchiveTableCreator::NUMERIC_TABLE);
        foreach ($archiveTables as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);
            list($year, $month) = explode('_', $date);

            $archivesToPurge->add("{$year}_{$month}");
        }
    }
}