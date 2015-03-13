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
use Piwik\Archive\ArchivePurger;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Log;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\SettingsServer;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var ArchivePurger
     */
    private $archivePurger;

    public function __construct(ArchivePurger $archivePurger = null)
    {
        $this->archivePurger = $archivePurger ?: new ArchivePurger();
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
        $logger = StaticContainer::get('Psr\Log\LoggerInterface');

        if ($this->willPurgingCausePotentialProblemInUI()) {
            $logger->info("Purging temporary archives: skipped (browser triggered archiving not enabled & not running after core:archive)");
            return false;
        }

        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();

        $logger->info("Purging archives in {tableCount} archive tables.", array('tableCount' => count($archiveTables)));

        // keep track of dates we purge for, since getTablesArchivesInstalled() will return numeric & blob
        // tables (so dates will appear two times, and we should only purge once per date)
        $datesPurged = array();

        foreach ($archiveTables as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);
            list($year, $month) = explode('_', $date);

            // Somehow we may have archive tables created with older dates, prevent exception from being thrown
            if ($year > 1990
                && empty($datesPurged[$date])
            ) {
                $dateObj = Date::factory("$year-$month-15");

                $this->archivePurger->purgeOutdatedArchives($dateObj);
                $this->archivePurger->purgeArchivesWithPeriodRange($dateObj);

                $datesPurged[$date] = true;
            }
        }
    }

    public function purgeInvalidatedArchives()
    {
        $archivesToPurge = new ArchivesToPurgeDistributedList();
        foreach ($archivesToPurge->getAllAsDates() as $date) {
            $this->archivePurger->purgeInvalidatedArchivesFrom($date);

            $archivesToPurge->removeDate($date);
        }
    }

    public function optimizeArchiveTable()
    {
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();
        Db::optimizeTables($archiveTables);
    }

    /**
     * we should only purge outdated & custom range archives if we know cron archiving has just run,
     * or if browser triggered archiving is enabled. if cron archiving has run, then we know the latest
     * archives are in the database, and we can remove temporary ones. if browser triggered archiving is
     * enabled, then we know any archives that are wrongly purged, can be re-archived on demand.
     * this prevents some situations where "no data" is displayed for reports that should have data.
     *
     * @return bool
     */
    private function willPurgingCausePotentialProblemInUI()
    {
        return !Rules::isRequestAuthorizedToArchive();
    }
}