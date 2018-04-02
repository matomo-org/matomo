<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\GeoIp2;

require_once PIWIK_INCLUDE_PATH . "/core/ScheduledTask.php"; // for the tracker which doesn't include this file

use Exception;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Log;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2 AS LocationProviderGeoIp2;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2\Php;
use Piwik\Scheduler\Scheduler;
use Piwik\Scheduler\Task;
use Piwik\Scheduler\Timetable;
use Piwik\Scheduler\Schedule\Monthly;
use Piwik\Scheduler\Schedule\Weekly;
use Piwik\Unzip;

/**
 * Used to automatically update installed GeoIP 2 databases, and manages the updater's
 * scheduled task.
 */
class GeoIP2AutoUpdater extends Task
{
    const SCHEDULE_PERIOD_MONTHLY = 'month';
    const SCHEDULE_PERIOD_WEEKLY = 'week';

    const SCHEDULE_PERIOD_OPTION_NAME = 'geoip2.updater_period';

    const LOC_URL_OPTION_NAME = 'geoip2.loc_db_url';
    const ISP_URL_OPTION_NAME = 'geoip2.isp_db_url';

    const LAST_RUN_TIME_OPTION_NAME = 'geoip2.updater_last_run_time';

    private static $urlOptions = array(
        'loc' => self::LOC_URL_OPTION_NAME,
        'isp' => self::ISP_URL_OPTION_NAME,
    );

    /**
     * PHP Error caught through a custom error handler while trying to use a downloaded
     * GeoIP 2 database. See catchGeoIPError for more info.
     *
     * @var array
     */
    private static $unzipPhpError = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $schedulePeriodStr = self::getSchedulePeriod();

        // created the scheduledtime instance, also, since GeoIP 2 updates are done on tuesdays,
        // get new DBs on Wednesday
        switch ($schedulePeriodStr) {
            case self::SCHEDULE_PERIOD_WEEKLY:
                $schedulePeriod = new Weekly();
                $schedulePeriod->setDay(3);
                break;
            case self::SCHEDULE_PERIOD_MONTHLY:
            default:
                $schedulePeriod = new Monthly();
                $schedulePeriod->setDayOfWeek(3, 0);
                break;
        }

        parent::__construct($this, 'update', null, $schedulePeriod, Task::LOWEST_PRIORITY);
    }

    /**
     * Attempts to download new location & ISP GeoIP databases and
     * replace the existing ones w/ them.
     */
    public function update()
    {
        try {
            Option::set(self::LAST_RUN_TIME_OPTION_NAME, Date::factory('today')->getTimestamp());

            $locUrl = Option::get(self::LOC_URL_OPTION_NAME);
            if (!empty($locUrl)) {
                $this->downloadFile('loc', $locUrl);
            }

            $ispUrl = Option::get(self::ISP_URL_OPTION_NAME);
            if (!empty($ispUrl)) {
                $this->downloadFile('isp', $ispUrl);
            }
        } catch (Exception $ex) {
            // message will already be prefixed w/ 'GeoIP2AutoUpdater: '
            Log::error($ex);
            $this->performRedundantDbChecks();
            throw $ex;
        }

        $this->performRedundantDbChecks();
    }

    /**
     * Downloads a GeoIP 2 database archive, extracts the .mmdb file and overwrites the existing
     * old database.
     *
     * If something happens that causes the download to fail, no exception is thrown, but
     * an error is logged.
     *
     * @param string $dbType
     * @param string $url URL to the database to download. The type of database is determined
     *                    from this URL.
     * @throws Exception
     */
    protected function downloadFile($dbType, $url)
    {
        $url = trim($url);

        $ext = GeoIP2AutoUpdater::getGeoIPUrlExtension($url);

        // NOTE: using the first item in $dbNames[$dbType] makes sure GeoLiteCity will be renamed to GeoIPCity
        $zippedFilename = LocationProviderGeoIp2::$dbNames[$dbType][0] . '.' . $ext;

        $zippedOutputPath = LocationProviderGeoIp2::getPathForGeoIpDatabase($zippedFilename);

        $url = self::removeDateFromUrl($url);

        // download zipped file to misc dir
        try {
            $success = Http::sendHttpRequest($url, $timeout = 3600, $userAgent = null, $zippedOutputPath);
        } catch (Exception $ex) {
            throw new Exception("GeoIP2AutoUpdater: failed to download '$url' to "
                . "'$zippedOutputPath': " . $ex->getMessage());
        }

        if ($success !== true) {
            throw new Exception("GeoIP2AutoUpdater: failed to download '$url' to "
                . "'$zippedOutputPath'! (Unknown error)");
        }

        Log::info("GeoIP2AutoUpdater: successfully downloaded '%s'", $url);

        try {
            self::unzipDownloadedFile($zippedOutputPath, $dbType, $unlink = true);
        } catch (Exception $ex) {
            throw new Exception("GeoIP2AutoUpdater: failed to unzip '$zippedOutputPath' after "
                . "downloading " . "'$url': " . $ex->getMessage());
        }

        Log::info("GeoIP2AutoUpdater: successfully updated GeoIP 2 database '%s'", $url);
    }

    /**
     * Unzips a downloaded GeoIP 2 database. Only unzips .gz & .tar.gz files.
     *
     * @param string $path Path to zipped file.
     * @param bool $unlink Whether to unlink archive or not.
     * @throws Exception
     */
    public static function unzipDownloadedFile($path, $dbType, $unlink = false)
    {
        $tempPath = LocationProviderGeoIp2::getPathForGeoIpDatabase('update/');

        // extract file
        if (substr($path, -7, 7) == '.tar.gz') {
            $unzip = Unzip::factory('tar.gz', $path);
            $success = $unzip->extract($tempPath);

            if ($success !== true) {
                throw new Exception(Piwik::translate('UserCountry_CannotUnzipDatFile',
                    array("'$path'", $unzip->errorInfo())));
            }
        } else if (substr($path, -3, 3) == '.gz') {
            $unzip = Unzip::factory('gz', $path);
            $success = $unzip->extract($tempPath);

            if ($success !== true) {
                throw new Exception(Piwik::translate('UserCountry_CannotUnzipDatFile',
                    array("'$path'", $unzip->errorInfo())));
            }
        } else {
            $ext = end(explode(basename($path), '.', 2));
            throw new Exception(Piwik::translate('UserCountry_UnsupportedArchiveType', "'$ext'"));
        }

        try {
            $extractedFiles = glob($tempPath . '*/*');

            foreach ($extractedFiles as $extractedFile) {
                $filename = basename($extractedFile);
                if (in_array($filename, LocationProviderGeoIp2::$dbNames[$dbType])) {
                    $dbFilename = $filename;
                    $tempFilename = $filename . '.new';
                    $outputPath = LocationProviderGeoIp2::getPathForGeoIpDatabase($tempFilename);
                    @rename($extractedFile, $outputPath);
                    Filesystem::unlinkRecursive($tempPath, true);
                    break;
                }
            }

            // test that the new archive is a valid GeoIP 2 database
            if (empty($dbFilename) || false === LocationProviderGeoIp2::getGeoIPDatabaseTypeFromFilename($dbFilename)) {
                throw new Exception("Unexpected GeoIP 2 archive file name '$path'.");
            }

            $customDbNames = array(
                'loc' => array(),
                'isp' => array()
            );
            $customDbNames[$dbType] = array($tempFilename);

            $phpProvider = new Php($customDbNames);

            try {
                $location = $phpProvider->getLocation(array('ip' => LocationProviderGeoIp2::TEST_IP));
            } catch (\Exception $e) {
                Log::info("GeoIP2AutoUpdater: Encountered exception when testing newly downloaded" .
                    " GeoIP 2 database: %s", $e->getMessage());

                throw new Exception(Piwik::translate('UserCountry_ThisUrlIsNotAValidGeoIPDB'));
            }

            if (empty($location)) {
                throw new Exception(Piwik::translate('UserCountry_ThisUrlIsNotAValidGeoIPDB'));
            }

            // delete the existing GeoIP database (if any) and rename the downloaded file
            $oldDbFile = LocationProviderGeoIp2::getPathForGeoIpDatabase($dbFilename);
            if (file_exists($oldDbFile)) {
                unlink($oldDbFile);
            }

            $tempFile = LocationProviderGeoIp2::getPathForGeoIpDatabase($tempFilename);
            if (@rename($tempFile, $oldDbFile) !== true) {
                //In case the $tempfile cannot be renamed, we copy the file.
                copy($tempFile, $oldDbFile);
                unlink($tempFile);
            }

            // delete original archive
            if ($unlink) {
                unlink($path);
            }
        } catch (Exception $ex) {
            // remove downloaded files
            if (file_exists($outputPath)) {
                unlink($outputPath);
            }
            unlink($path);

            throw $ex;
        }
    }

    /**
     * Sets the options used by this class based on query parameter values.
     *
     * See setUpdaterOptions for query params used.
     */
    public static function setUpdaterOptionsFromUrl()
    {
        $options = array(
            'loc'    => Common::getRequestVar('loc_db', false, 'string'),
            'isp'    => Common::getRequestVar('isp_db', false, 'string'),
            'period' => Common::getRequestVar('period', false, 'string'),
        );

        foreach (self::$urlOptions as $optionKey => $optionName) {
            $options[$optionKey] = Common::unsanitizeInputValue($options[$optionKey]); // URLs should not be sanitized
        }

        self::setUpdaterOptions($options);
    }

    /**
     * Sets the options used by this class based on the elements in $options.
     *
     * The following elements of $options are used:
     *   'loc' - URL for location database.
     *   'isp' - URL for ISP database.
     *   'org' - URL for Organization database.
     *   'period' - 'weekly' or 'monthly'. When to run the updates.
     *
     * @param array $options
     * @throws Exception
     */
    public static function setUpdaterOptions($options)
    {
        // set url options
        foreach (self::$urlOptions as $optionKey => $optionName) {
            if (!isset($options[$optionKey])) {
                continue;
            }

            $url = $options[$optionKey];
            $url = self::removeDateFromUrl($url);

            Option::set($optionName, $url);
        }

        // set period option
        if (!empty($options['period'])) {
            $period = $options['period'];

            if ($period != self::SCHEDULE_PERIOD_MONTHLY
                && $period != self::SCHEDULE_PERIOD_WEEKLY
            ) {
                throw new Exception(Piwik::translate(
                    'UserCountry_InvalidGeoIPUpdatePeriod',
                    array("'$period'", "'" . self::SCHEDULE_PERIOD_MONTHLY . "', '" . self::SCHEDULE_PERIOD_WEEKLY . "'")
                ));
            }

            Option::set(self::SCHEDULE_PERIOD_OPTION_NAME, $period);

            /** @var Scheduler $scheduler */
            $scheduler = StaticContainer::getContainer()->get('Piwik\Scheduler\Scheduler');

            $scheduler->rescheduleTask(new GeoIP2AutoUpdater());
        }
    }

    /**
     * Returns true if the auto-updater is setup to update at least one type of
     * database. False if otherwise.
     *
     * @return bool
     */
    public static function isUpdaterSetup()
    {
        if (Option::get(self::LOC_URL_OPTION_NAME) !== false
            || Option::get(self::ISP_URL_OPTION_NAME) !== false
        ) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves the URLs used to update various GeoIP 2 database files.
     *
     * @return array
     */
    public static function getConfiguredUrls()
    {
        $result = array();
        foreach (self::$urlOptions as $key => $optionName) {
            $result[$key] = Option::get($optionName);
        }
        return $result;
    }

    /**
     * Returns the confiured URL (if any) for a type of database.
     *
     * @param string $key 'loc', 'isp' or 'org'
     * @throws Exception
     * @return string|false
     */
    public static function getConfiguredUrl($key)
    {
        if (empty(self::$urlOptions[$key])) {
            throw new Exception("Invalid key $key");
        }
        $url = Option::get(self::$urlOptions[$key]);
        return $url;
    }

    /**
     * Performs a GeoIP 2 database update.
     */
    public static function performUpdate()
    {
        $instance = new GeoIP2AutoUpdater();
        $instance->update();
    }

    /**
     * Returns the configured update period, either 'week' or 'month'. Defaults to
     * 'month'.
     *
     * @return string
     */
    public static function getSchedulePeriod()
    {
        $period = Option::get(self::SCHEDULE_PERIOD_OPTION_NAME);
        if ($period === false) {
            $period = self::SCHEDULE_PERIOD_MONTHLY;
        }
        return $period;
    }

    /**
     * Returns an array of strings for GeoIP 2 databases that have update URLs configured, but
     * are not present in the misc directory. Each string is a key describing the type of
     * database (ie, 'loc', 'isp' or 'org').
     *
     * @return array
     */
    public static function getMissingDatabases()
    {
        $result = array();
        foreach (self::getConfiguredUrls() as $key => $url) {
            if (!empty($url)) {
                // if a database of the type does not exist, but there's a url to update, then
                // a database is missing
                $path = LocationProviderGeoIp2::getPathToGeoIpDatabase(
                    LocationProviderGeoIp2::$dbNames[$key]);
                if ($path === false) {
                    $result[] = $key;
                }
            }
        }
        return $result;
    }

    /**
     * Returns the extension of a URL used to update a GeoIP 2 database, if it can be found.
     */
    public static function getGeoIPUrlExtension($url)
    {
        // check for &suffix= query param that is special to MaxMind URLs
        if (preg_match('/suffix=([^&]+)/', $url, $matches)) {
            $ext = $matches[1];
        } else {
            // use basename of url
            $filenameParts = explode('.', basename($url), 2);
            if (count($filenameParts) > 1) {
                $ext = end($filenameParts);
            } else {
                $ext = reset($filenameParts);
            }
        }

        self::checkForSupportedArchiveType($ext);

        return $ext;
    }

    /**
     * Avoid downloading archive types we don't support. No point in downloading it,
     * if we can't unzip it...
     *
     * @param string $ext The URL file's extension.
     * @throws \Exception
     */
    private static function checkForSupportedArchiveType($ext)
    {
        if ($ext != 'tar.gz'
            && $ext != 'gz'
            && $ext != 'mmdb.gz'
        ) {
            throw new \Exception(Piwik::translate('UserCountry_UnsupportedArchiveType', "'$ext'"));
        }
    }

    /**
     * Utility function that checks if geolocation works with each installed database,
     * and if one or more doesn't, they are renamed to make sure tracking will work.
     * This is a safety measure used to make sure tracking isn't affected if strange
     * update errors occur.
     *
     * Databases are renamed to ${original}.broken .
     *
     * Note: method is protected for testability.
     *
     * @param $logErrors - only used to hide error logs during tests
     */
    protected function performRedundantDbChecks($logErrors = true)
    {
        $databaseTypes = array_keys(LocationProviderGeoIp2::$dbNames);

        foreach ($databaseTypes as $type) {
            $customNames = array(
                'loc' => array(),
                'isp' => array(),
                'org' => array()
            );
            $customNames[$type] = LocationProviderGeoIp2::$dbNames[$type];

            // create provider that only uses the DB type we're testing
            $provider = new Php($customNames);

            // test the provider. on error, we rename the broken DB.
            try {
                $location = $provider->getLocation(array('ip' => LocationProviderGeoIp2::TEST_IP));
            } catch (\Exception $e) {
                if($logErrors) {
                    Log::error("GeoIP2AutoUpdater: Encountered exception when performing redundant tests on GeoIP2 "
                        . "%s database: %s: %s", $type, $e->getMessage());
                }

                // get the current filename for the DB and an available new one to rename it to
                list($oldPath, $newPath) = $this->getOldAndNewPathsForBrokenDb($customNames[$type]);

                // rename the DB so tracking will not fail
                if ($oldPath !== false
                    && $newPath !== false
                ) {
                    if (file_exists($newPath)) {
                        unlink($newPath);
                    }

                    rename($oldPath, $newPath);
                }
            }
        }
    }

    /**
     * Returns the path to a GeoIP 2 database and a path to rename it to if it's broken.
     *
     * @param array $possibleDbNames The possible names of the database.
     * @return array Array with two elements, the path to the existing database, and
     *               the path to rename it to if it is broken. The second will end
     *               with something like .broken .
     */
    private function getOldAndNewPathsForBrokenDb($possibleDbNames)
    {
        $pathToDb = LocationProviderGeoIp2::getPathToGeoIpDatabase($possibleDbNames);
        $newPath = false;

        if ($pathToDb !== false) {
            $newPath = $pathToDb . ".broken";
        }

        return array($pathToDb, $newPath);
    }

    /**
     * Custom PHP error handler used to catch any PHP errors that occur when
     * testing a downloaded GeoIP 2 file.
     *
     * If we download a file that is supposed to be a GeoIP 2 database, we need to make
     * sure it is one. This is done simply by attempting to use it. If this fails, it
     * will most of the time fail as a PHP error, which we catch w/ this function
     * after it is passed to set_error_handler.
     *
     * The PHP error is stored in self::$unzipPhpError.
     *
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     */
    public static function catchGeoIPError($errno, $errstr, $errfile, $errline)
    {
        self::$unzipPhpError = array($errno, $errstr, $errfile, $errline);
    }

    /**
     * Returns the time the auto updater was last run.
     *
     * @return Date|false
     */
    public static function getLastRunTime()
    {
        $timestamp = Option::get(self::LAST_RUN_TIME_OPTION_NAME);
        return $timestamp === false ? false : Date::factory((int)$timestamp);
    }

    /**
     * Removes the &date=... query parameter if present in the URL. This query parameter
     * is in MaxMind URLs by default and will force the download of an old database.
     *
     * @param string $url
     * @return string
     */
    private static function removeDateFromUrl($url)
    {
        return preg_replace("/&date=[^&#]*/", '', $url);
    }

    /**
     * Returns the next scheduled time for the auto updater.
     *
     * @return Date|false
     */
    public static function getNextRunTime()
    {
        $task = new GeoIP2AutoUpdater();

        $timetable = new Timetable();
        return $timetable->getScheduledTaskTime($task->getName());
    }

    /**
     * See {@link Piwik\Scheduler\Schedule\Schedule::getRescheduledTime()}.
     */
    public function getRescheduledTime()
    {
        $nextScheduledTime = parent::getRescheduledTime();

        // if a geoip 2 database is out of date, run the updater as soon as possible
        if ($this->isAtLeastOneGeoIpDbOutOfDate($nextScheduledTime)) {
            return time();
        }

        return $nextScheduledTime;
    }

    private function isAtLeastOneGeoIpDbOutOfDate($rescheduledTime)
    {
        $previousScheduledRuntime = $this->getPreviousScheduledTime($rescheduledTime)->setTime("00:00:00")->getTimestamp();

        foreach (LocationProviderGeoIp2::$dbNames as $type => $dbNames) {
            $dbUrl = Option::get(self::$urlOptions[$type]);
            $dbPath = LocationProviderGeoIp2::getPathToGeoIpDatabase($dbNames);

            // if there is a URL for this DB type and the GeoIP 2 DB file's last modified time is before
            // the time the updater should have been previously run, then **the file is out of date**
            if (!empty($dbUrl)
                && filemtime($dbPath) < $previousScheduledRuntime
            ) {
                return true;
            }
        }

        return false;
    }

    private function getPreviousScheduledTime($rescheduledTime)
    {
        $updaterPeriod = self::getSchedulePeriod();

        if ($updaterPeriod == self::SCHEDULE_PERIOD_WEEKLY) {
            return Date::factory($rescheduledTime)->subWeek(1);
        } else if ($updaterPeriod == self::SCHEDULE_PERIOD_MONTHLY) {
            return Date::factory($rescheduledTime)->subMonth(1);
        }
        throw new Exception("Unknown GeoIP 2 updater period found in database: %s", $updaterPeriod);
    }
}
