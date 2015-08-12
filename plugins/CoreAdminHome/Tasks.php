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
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Http;
use Piwik\Option;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Tracker\Visit\ReferrerSpamFilter;
use Psr\Log\LoggerInterface;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var ArchivePurger
     */
    private $archivePurger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ArchivePurger $archivePurger, LoggerInterface $logger)
    {
        $this->archivePurger = $archivePurger;
        $this->logger = $logger;
    }

    public function schedule()
    {
        // general data purge on older archive tables, executed daily
        $this->daily('purgeOutdatedArchives', null, self::HIGH_PRIORITY);

        // general data purge on invalidated archive records, executed daily
        $this->daily('purgeInvalidatedArchives', null, self::LOW_PRIORITY);

        // lowest priority since tables should be optimized after they are modified
        $this->daily('optimizeArchiveTable', null, self::LOWEST_PRIORITY);

        $this->weekly('updateSpammerBlacklist');
    }

    /**
     * @return bool `true` if the purge was executed, `false` if it was skipped.
     * @throws \Exception
     */
    public function purgeOutdatedArchives()
    {
        if ($this->willPurgingCausePotentialProblemInUI()) {
            $this->logger->info("Purging temporary archives: skipped (browser triggered archiving not enabled & not running after core:archive)");
            return false;
        }

        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();

        $this->logger->info("Purging archives in {tableCount} archive tables.", array('tableCount' => count($archiveTables)));

        // keep track of dates we purge for, since getTablesArchivesInstalled() will return numeric & blob
        // tables (so dates will appear two times, and we should only purge once per date)
        $datesPurged = array();

        foreach ($archiveTables as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);
            list($year, $month) = explode('_', $date);

            // Somehow we may have archive tables created with older dates, prevent exception from being thrown
            if ($year > 1990) {
                if (empty($datesPurged[$date])) {
                    $dateObj = Date::factory("$year-$month-15");

                    $this->archivePurger->purgeOutdatedArchives($dateObj);
                    $this->archivePurger->purgeArchivesWithPeriodRange($dateObj);

                    $datesPurged[$date] = true;
                } else {
                    $this->logger->debug("Date {date} already purged.", array('date' => $date));
                }
            } else {
                $this->logger->info("Skipping purging of archive tables *_{year}_{month}, year <= 1990.", array('year' => $year, 'month' => $month));
            }
        }

        return true;
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
     * Update the referrer spam blacklist
     *
     * @see https://github.com/piwik/referrer-spam-blacklist
     */
    public function updateSpammerBlacklist()
    {
        $url = 'https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt';
        $list = Http::sendHttpRequest($url, 30);
        $list = preg_split("/\r\n|\n|\r/", $list);
        if (count($list) < 10) {
            throw new \Exception(sprintf(
                'The spammers list downloaded from %s contains less than 10 entries, considering it a fail',
                $url
            ));
        }

        Option::set(ReferrerSpamFilter::OPTION_STORAGE_NAME, serialize($list));
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