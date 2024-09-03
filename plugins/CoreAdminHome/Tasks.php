<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome;

use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Archive\ArchivePurger;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\CronArchive;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\Model as CoreModel;
use Piwik\Date;
use Piwik\Db;
use Piwik\Http;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Emails\JsTrackingCodeMissingEmail;
use Piwik\Plugins\CoreAdminHome\Emails\TrackingFailuresEmail;
use Piwik\Plugins\CoreAdminHome\Tasks\ArchivesToPurgeDistributedList;
use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Plugins\SitesManager\SitesManager;
use Piwik\Scheduler\Schedule\SpecificTime;
use Piwik\Settings\Storage\Backend\MeasurableSettingsTable;
use Piwik\Tracker\Failures;
use Piwik\Site;
use Piwik\Tracker\FingerprintSalt;
use Piwik\Tracker\Visit\ReferrerSpamFilter;
use Piwik\Log\LoggerInterface;
use Piwik\SettingsPiwik;

class Tasks extends \Piwik\Plugin\Tasks
{
    public const TRACKING_CODE_CHECK_FLAG = 'trackingCodeExistsCheck';
    /**
     * @var ArchivePurger
     */
    private $archivePurger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Failures
     */
    private $trackingFailures;

    public function __construct(ArchivePurger $archivePurger, LoggerInterface $logger, Failures $failures)
    {
        $this->archivePurger = $archivePurger;
        $this->logger = $logger;
        $this->trackingFailures = $failures;
    }

    public function schedule()
    {
        // for browser triggered archiving, make sure we invalidate archives once a day just to make
        // sure all archives that need to be invalidated get invalidated
        $this->daily('invalidateOutdatedArchives', null, self::HIGH_PRIORITY);

        $this->daily('deleteOldFingerprintSalts', null, self::HIGH_PRIORITY);

        // general data purge on older archive tables, executed daily
        $this->daily('purgeOutdatedArchives', null, self::HIGH_PRIORITY);

        // general data purge on invalidated archive records, executed daily
        $this->daily('purgeInvalidatedArchives', null, self::LOW_PRIORITY);
        $this->daily('purgeInvalidationsForDeletedSites', null, self::LOW_PRIORITY);

        $this->weekly('purgeOrphanedArchives', null, self::NORMAL_PRIORITY);

        // lowest priority since tables should be optimized after they are modified
        $this->monthly('optimizeArchiveTable', null, self::LOWEST_PRIORITY);

        $this->daily('cleanupTrackingFailures', null, self::LOWEST_PRIORITY);
        $this->weekly('notifyTrackingFailures', null, self::LOWEST_PRIORITY);

        $generalConfig = Config::getInstance()->Tracker;
        if ((SettingsPiwik::isInternetEnabled() === true) && $generalConfig['enable_spam_filter']) {
            $this->weekly('updateSpammerList');
        }

        $this->scheduleTrackingCodeReminderChecks();
    }

    public function purgeInvalidationsForDeletedSites()
    {
        $coreModel = new CoreModel();
        $coreModel->deleteInvalidationsForDeletedSites();
    }

    public function deleteOldFingerprintSalts()
    {
        StaticContainer::get(FingerprintSalt::class)->deleteOldSalts();
    }

    public function invalidateOutdatedArchives()
    {
        if (!Rules::isBrowserTriggerEnabled()) {
            $this->logger->info("Browser triggered archiving disabled, archives will be invalidated during core:archive.");
            return;
        }

        $idSites = Request::processRequest('SitesManager.getAllSitesId');
        $cronArchive = new CronArchive();
        foreach ($idSites as $idSite) {
            $cronArchive->invalidateArchivedReportsForSitesThatNeedToBeArchivedAgain($idSite);
        }
    }

    private function scheduleTrackingCodeReminderChecks()
    {
        $daysToTrackedVisitsCheck = (int) Config::getInstance()->General['num_days_before_tracking_code_reminder'];
        if ($daysToTrackedVisitsCheck <= 0) {
            return;
        }

        // add check for a site's tracked visits
        $sites = Request::processRequest('SitesManager.getAllSites');

        foreach ($sites as $site) {
            $createdTime = Date::factory($site['ts_created']);
            $scheduledTime = $createdTime->addDay($daysToTrackedVisitsCheck)->setTime('02:00:00');

            // we don't want to run this check for every site in an install when this code is introduced,
            // so if the site is over 2 * $daysToTrackedVisitsCheck days old, assume the check has run.
            $isSiteOld = $createdTime->isEarlier(Date::today()->subDay($daysToTrackedVisitsCheck * 2));

            if ($isSiteOld || $this->hasTrackingCodeReminderRun($site['idsite'])) {
                continue;
            }

            $schedule = new SpecificTime($scheduledTime->getTimestamp());
            $this->custom($this, 'checkSiteHasTrackedVisits', $site['idsite'], $schedule);
        }
    }

    public function checkSiteHasTrackedVisits($idSite)
    {
        $this->rememberTrackingCodeReminderRan($idSite);

        if (!SitesManager::shouldPerformEmptySiteCheck($idSite)) {
            return;
        }

        if (SitesManager::hasTrackedAnyTraffic($idSite)) {
            return;
        }

        // site is still empty after N days, so send an email to the user that created the site
        $creatingUser = Site::getCreatorLoginFor($idSite);
        if (empty($creatingUser)) {
            return;
        }

        try {
            $user = Request::processRequest('UsersManager.getUser', [
                'userLogin' => $creatingUser,
            ]);
        } catch (\Exception $e) {
            return;
        }

        if (empty($user['email'])) {
            return;
        }

        $container = StaticContainer::getContainer();
        $email = $container->make(JsTrackingCodeMissingEmail::class, array(
            'login' => $user['login'],
            'emailAddress' => $user['email'],
            'idSite' => $idSite
        ));
        $email->send();
    }

    private function hasTrackingCodeReminderRun($idSite)
    {
        $table = new MeasurableSettingsTable($idSite, 'CoreAdminHome');
        $settings = $table->load();
        return !empty($settings[self::TRACKING_CODE_CHECK_FLAG]);
    }

    private function rememberTrackingCodeReminderRan($idSite)
    {
        $table = new MeasurableSettingsTable($idSite, 'CoreAdminHome');
        $settings = $table->load();
        $settings[self::TRACKING_CODE_CHECK_FLAG] = 1;
        $table->save($settings);
    }

    /**
     * To test execute the following command:
     * `./console core:run-scheduled-tasks "Piwik\Plugins\CoreAdminHome\Tasks.cleanupTrackingFailures"`
     *
     * @throws \Exception
     */
    public function cleanupTrackingFailures()
    {
        // we remove possibly outdated/fixed tracking failures that have not occurred again recently
        $this->trackingFailures->removeFailuresOlderThanDays(Failures::CLEANUP_OLD_FAILURES_DAYS);
    }

    /**
     * To test execute the following command:
     * `./console core:run-scheduled-tasks "Piwik\Plugins\CoreAdminHome\Tasks.notifyTrackingFailures"`
     *
     * @throws \Exception
     */
    public function notifyTrackingFailures()
    {
        $this->cleanupTrackingFailures();
        $failures = $this->trackingFailures->getAllFailures();
        $general = Config::getInstance()->General;
        if (!empty($failures) && $general['enable_tracking_failures_notification']) {
            $superUsers = Piwik::getAllSuperUserAccessEmailAddresses();
            foreach ($superUsers as $login => $email) {
                $email = new TrackingFailuresEmail($login, $email, count($failures));
                $email->send();
            }
        }
    }

    /**
     * @return bool `true` if the purge was executed, `false` if it was skipped.
     * @throws \Exception
     */
    public function purgeOutdatedArchives()
    {
        if ($this->willPurgingCausePotentialProblemInUI() && !Rules::$disablePureOutdatedArchive) {
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
            [$year, $month] = explode('_', $date);

            // Somehow we may have archive tables created with older dates, prevent exception from being thrown
            if ($year > 1990) {
                if (empty($datesPurged[$date])) {
                    try {
                        $dateObj = Date::factory("$year-$month-15");
                    } catch (\Exception $e) {
                        continue; // skip invalid dates
                    }

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
        $purgedDates = [];

        $archivesToPurge = new ArchivesToPurgeDistributedList();
        foreach ($archivesToPurge->getAllAsDates() as $date) {
            $this->archivePurger->purgeInvalidatedArchivesFrom($date);

            $archivesToPurge->removeDate($date);

            $purgedDates[$date->toString('Y-m')] = true;
        }

        // purge from today if not done already since we will have many archives to remove
        $today = Date::today();
        $todayStr = $today->toString('Y-m');
        if (empty($purgedDates[$todayStr])) {
            $this->archivePurger->purgeInvalidatedArchivesFrom($today);
            $purgedDates[$todayStr] = true;
        }

        // handle yesterday if it belongs to a different month
        $yesterday = Date::yesterday();
        $yesterdayStr = $yesterday->toString('Y-m');
        if (empty($purgedDates[$yesterdayStr])) {
            $this->archivePurger->purgeInvalidatedArchivesFrom($yesterday);
        }

        // handle year start table
        $yearStart = $today->toString('Y-01');
        if (empty($purgedDates[$yearStart])) {
            $this->archivePurger->purgeInvalidatedArchivesFrom(Date::factory($yearStart . '-01'));
        }
    }

    public function optimizeArchiveTable()
    {
        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();
        Db\Schema::getInstance()->optimizeTables($archiveTables);
    }

    /**
     * Update the referrer spam blacklist
     *
     * @see https://github.com/matomo-org/referrer-spam-list
     */
    public function updateSpammerList()
    {
        $url = 'https://raw.githubusercontent.com/matomo-org/referrer-spam-list/master/spammers.txt';
        $list = Http::sendHttpRequest($url, 30);

        if (preg_match('/[<>&?"\']/', $list)) {
            throw new \Exception(sprintf(
                'The spammers list downloaded from %s contains unexpected characters, considering it a fail',
                $url
            ));
        }

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
     * To test execute the following command:
     * `./console core:run-scheduled-tasks "Piwik\Plugins\CoreAdminHome\Tasks.purgeOrphanedArchives"`
     *
     * @throws \Exception
     */
    public function purgeOrphanedArchives()
    {
        $eightDaysAgo = Date::factory('now')->subDay(8);
        $model = new Model();
        $deletedSegments = $model->getSegmentsDeletedSince($eightDaysAgo);

        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled('numeric');

        $datesPurged = array();
        foreach ($archiveTables as $table) {
            $date = ArchiveTableCreator::getDateFromTableName($table);
            [$year, $month] = explode('_', $date);

            try {
                $dateObj = Date::factory("$year-$month-15");
            } catch (\Exception $e) {
                continue; // skip invalid dates
            }

            $this->archivePurger->purgeDeletedSiteArchives($dateObj);
            if (count($deletedSegments)) {
                $this->archivePurger->purgeDeletedSegmentArchives($dateObj, $deletedSegments);
            }

            $datesPurged[$date] = true;
        }
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
