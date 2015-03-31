<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreAdminHome\Tasks;

use Piwik\Concurrency\DistributedList;
use Piwik\Date;

/**
 * Distributed list that holds a list of year-month archive table identifiers (eg, 2015_01 or 2014_11). Each item in the
 * list is expected to identify a pair of archive tables that contain invalidated archives.
 *
 * The archiving purging scheduled task will read items in this list when executing the daily purge.
 *
 * This class is necessary in order to keep the archive purging scheduled task fast. W/o a way to keep track of
 * tables w/ invalid data, the task would have to iterate over every table, which is not desired for a task that
 * is executed daily.
 *
 * If users find other tables contain invalidated archives, they can use the core:purge-old-archive-data command
 * to manually purge them.
 */
class ArchivesToPurgeDistributedList extends DistributedList
{
    const OPTION_INVALIDATED_DATES_SITES_TO_PURGE = 'InvalidatedOldReports_DatesWebsiteIds';

    public function __construct()
    {
        parent::__construct(self::OPTION_INVALIDATED_DATES_SITES_TO_PURGE);
    }

    /**
     * @inheritdoc
     */
    public function setAll($yearMonths)
    {
        $yearMonths = array_unique($yearMonths, SORT_REGULAR);
        parent::setAll($yearMonths);
    }

    protected function getListOptionValue()
    {
        $result = parent::getListOptionValue();
        $this->convertOldDistributedList($result);
        return $result;
    }

    public function getAllAsDates()
    {
        $dates = array();
        foreach ($this->getAll() as $yearMonth) {
            try {
                $date = Date::factory(str_replace('_', '-', $yearMonth) . '-01');
            } catch (\Exception $ex) {
                continue; // invalid year month in distributed list
            }

            $dates[] = $date;
        }
        return $dates;
    }

    public function removeDate(Date $date)
    {
        $yearMonth = $date->toString('Y_m');
        $this->remove($yearMonth);
    }

    /**
     * Before 2.12.0 Piwik stored this list as an array mapping year months to arrays of site IDs. If this is
     * found in the DB, we convert the array to an array of year months to avoid errors and to make sure
     * the correct tables are still purged.
     */
    private function convertOldDistributedList(&$yearMonths)
    {
        foreach ($yearMonths as $key => $value) {
            if (preg_match("/^[0-9]{4}_[0-9]{2}$/", $key)) {
                unset($yearMonths[$key]);

                $yearMonths[] = $key;
            }
        }
    }
}