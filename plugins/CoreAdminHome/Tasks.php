<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Archive\Purger;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Log;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var Purger
     */
    private $archivePurger;

    public function __construct(Purger $archivePurger = null)
    {
        $this->archivePurger = $archivePurger ?: new Purger();
    }

    public function schedule()
    {
        // general data purge on older archive tables, executed daily
        $this->daily('purgeOutdatedArchives', null, self::HIGH_PRIORITY);

        // general data purge on invalidated archive records, executed daily
        $this->daily('purgeInvalidatedArchives', null, self::LOW_PRIORITY);

        // lowest priority since tables should be optimized after they are modified
        $this->daily('optimizeArchiveTable', null, self::LOWEST_PRIORITY);
    }

    public function purgeOutdatedArchives()
    {
        // TODO: is this correct? wouldn't segment archives create DONE archives, not DONE_TEMPORARY? should try to replicate in tests
        // we only delete archives if we are able to process them, otherwise, the browser might process reports
        // when &segment= is specified (or custom date range) and would below, delete temporary archives that the
        // browser is not able to process until next cron run (which could be more than 1 hour away)
        if (!Rules::isRequestAuthorizedToArchive()) {
            Log::info("Purging temporary archives: skipped (request not allowed to initiate archiving)");
            return false;
        }

        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();
        foreach ($archiveTables as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);
            list($year, $month) = explode('_', $date);

            // Somehow we may have archive tables created with older dates, prevent exception from being thrown
            if ($year > 1990) {
                $this->archivePurger->purgeOutdatedArchives(Date::factory("$year-$month-15"));
            }
        }
    }

    public function purgeInvalidatedArchives()
    {
        $this->archivePurger->purgeInvalidatedArchives();
    }

    public function optimizeArchiveTable()
    {
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();
        Db::optimizeTables($archiveTables);
    }
}