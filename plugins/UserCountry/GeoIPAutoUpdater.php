<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountry
 */

/**
 * Used to automatically update installed GeoIP databases, and manages the updater's
 * scheduled task.
 */
class Piwik_UserCountry_GeoIPAutoUpdater
{
    const SCHEDULE_PERIOD_MONTHLY = 'month';
    const SCHEDULE_PERIOD_WEEKLY = 'week';

    const SCHEDULE_PERIOD_OPTION_NAME = 'geoip.updater_period';
    const LOC_URL_OPTION_NAME = 'geoip.loc_db_url';
    const ISP_URL_OPTION_NAME = 'geoip.isp_db_url';
    const ORG_URL_OPTION_NAME = 'geoip.org_db_url';

    const LAST_RUN_TIME_OPTION_NAME = 'geoip.updater_last_run_time';

    private static $urlOptions = array(
        'loc' => self::LOC_URL_OPTION_NAME,
        'isp' => self::ISP_URL_OPTION_NAME,
        'org' => self::ORG_URL_OPTION_NAME,
    );

    /**
     * PHP Error caught through a custom error handler while trying to use a downloaded
     * GeoIP database. See catchGeoIPError for more info.
     *
     * @var array
     */
    private static $unzipPhpError = null;

    /**
     * Attempts to download new location, ISP & organization GeoIP databases and
     * replace the existing ones w/ them.
     */
    public function update()
    {
        try {
            Piwik_SetOption(self::LAST_RUN_TIME_OPTION_NAME, Piwik_Date::factory('today')->getTimestamp());

            $locUrl = Piwik_GetOption(self::LOC_URL_OPTION_NAME);
            if (!empty($locUrl)) {
                $this->downloadFile('loc', $locUrl);
            }

            $ispUrl = Piwik_GetOption(self::ISP_URL_OPTION_NAME);
            if (!empty($ispUrl)) {
                $this->downloadFile('isp', $ispUrl);
            }

            $orgUrl = Piwik_GetOption(self::ORG_URL_OPTION_NAME);
            if (!empty($orgUrl)) {
                $this->downloadFile('org', $orgUrl);
            }
        } catch (Exception $ex) {
            // message will already be prefixed w/ 'Piwik_UserCountry_GeoIPAutoUpdater: '
            Piwik::log($ex->getMessage());
            $this->performRedundantDbChecks();
            throw $ex;
        }

        $this->performRedundantDbChecks();
    }

    /**
     * Downloads a GeoIP database archive, extracts the .dat file and overwrites the existing
     * old database.
     *
     * If something happens that causes the download to fail, no exception is thrown, but
     * an error is logged.
     *
     * @param string $url URL to the database to download. The type of database is determined
     *                    from this URL.
     */
    private function downloadFile($dbType, $url)
    {
        $ext = Piwik_UserCountry_GeoIPAutoUpdater::getGeoIPUrlExtension($url);
        $zippedFilename = Piwik_UserCountry_LocationProvider_GeoIp::$dbNames[$dbType][0] . '.' . $ext;

        $zippedOutputPath = Piwik_UserCountry_LocationProvider_GeoIp::getPathForGeoIpDatabase($zippedFilename);

        // download zipped file to misc dir
        try {
            $success = Piwik_Http::sendHttpRequest($url, $timeout = 3600, $userAgent = null, $zippedOutputPath);
        } catch (Exception $ex) {
            throw new Exception("Piwik_UserCountry_GeoIPAutoUpdater: failed to download '$url' to "
                . "'$zippedOutputPath': " . $ex->getMessage());
        }

        if ($success !== true) {
            throw new Exception("Piwik_UserCountry_GeoIPAutoUpdater: failed to download '$url' to "
                . "'$zippedOutputPath'! (Unknown error)");
        }

        Piwik::log(sprintf("Piwik_UserCountry_GeoIPAutoUpdater: successfully downloaded '%s'", $url));

        try {
            self::unzipDownloadedFile($zippedOutputPath, $unlink = true);
        } catch (Exception $ex) {
            throw new Exception("Piwik_UserCountry_GeoIPAutoUpdater: failed to unzip '$zippedOutputPath' after "
                . "downloading " . "'$url': " . $ex->getMessage());
        }

        Piwik::log(sprintf("Piwik_UserCountry_GeoIPAutoUpdater: successfully updated GeoIP database '%s'", $url));
    }

    /**
     * Unzips a downloaded GeoIP database. Only unzips .gz & .tar.gz files.
     *
     * @param string $path Path to zipped file.
     * @param bool $unlink Whether to unlink archive or not.
     */
    public static function unzipDownloadedFile($path, $unlink = false)
    {
        $parts = explode('.', basename($path));
        $filenameStart = $parts[0];

        $dbFilename = $filenameStart . '.dat';
        $tempFilename = $filenameStart . '.dat.new';
        $outputPath = Piwik_UserCountry_LocationProvider_GeoIp::getPathForGeoIpDatabase($tempFilename);

        // extract file
        if (substr($path, -7, 7) == '.tar.gz') {
            // find the .dat file in the tar archive
            $unzip = Piwik_Unzip::factory('tar.gz', $path);
            $content = $unzip->listContent();

            if (empty($content)) {
                throw new Exception(Piwik_Translate('UserCountry_CannotListContent',
                    array("'$path'", $unzip->errorInfo())));
            }

            $datFile = null;
            foreach ($content as $info) {
                $archivedPath = $info['filename'];
                if (basename($archivedPath) === $dbFilename) {
                    $datFile = $archivedPath;
                }
            }

            if ($datFile === null) {
                throw new Exception(Piwik_Translate('UserCountry_CannotFindGeoIPDatabaseInArchive',
                    array($dbFilename, "'$path'")));
            }

            // extract JUST the .dat file
            $unzipped = $unzip->extractInString($datFile);

            if (empty($unzipped)) {
                throw new Exception(Piwik_Translate('UserCountry_CannotUnzipDatFile',
                    array("'$path'", $unzip->errorInfo())));
            }

            // write unzipped to file
            $fd = fopen($outputPath, 'wb');
            fwrite($fd, $unzipped);
            fclose($fd);
        } else if (substr($path, -3, 3) == '.gz') {
            $unzip = Piwik_Unzip::factory('gz', $path);
            $success = $unzip->extract($outputPath);

            if ($success !== true) {
                throw new Exception(Piwik_Translate('UserCountry_CannotUnzipDatFile',
                    array("'$path'", $unzip->errorInfo())));
            }
        } else {
            $ext = end(explode(basename($path), '.', 2));
            throw new Exception(Piwik_Translate('UserCountry_UnsupportedArchiveType', "'$ext'"));
        }

        try {
            // test that the new archive is a valid GeoIP database
            $dbType = Piwik_UserCountry_LocationProvider_GeoIp::getGeoIPDatabaseTypeFromFilename($dbFilename);
            if ($dbType === false) // sanity check
            {
                throw new Exception("Unexpected GeoIP archive file name '$path'.");
            }

            $customDbNames = array(
                'loc' => array(),
                'isp' => array(),
                'org' => array()
            );
            $customDbNames[$dbType] = array($tempFilename);

            $phpProvider = new Piwik_UserCountry_LocationProvider_GeoIp_Php($customDbNames);

            $location = self::getTestLocationCatchPhpErrors($phpProvider);

            if (empty($location)
                || self::$unzipPhpError !== null
            ) {
                if (self::$unzipPhpError !== null) {
                    list($errno, $errstr, $errfile, $errline) = self::$unzipPhpError;
                    Piwik::log("Piwik_UserCountry_GeoIPAutoUpdater: Encountered PHP error when testing newly downloaded" .
                        " GeoIP database: $errno: $errstr on line $errline of $errfile.");
                }

                throw new Exception(Piwik_Translate('UserCountry_ThisUrlIsNotAValidGeoIPDB'));
            }

            // delete the existing GeoIP database (if any) and rename the downloaded file
            $oldDbFile = Piwik_UserCountry_LocationProvider_GeoIp::getPathForGeoIpDatabase($dbFilename);
            if (file_exists($oldDbFile)) {
                unlink($oldDbFile);
            }

            $tempFile = Piwik_UserCountry_LocationProvider_GeoIp::getPathForGeoIpDatabase($tempFilename);
            rename($existing = $tempFile, $newName = $oldDbFile);

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
     * Creates a ScheduledTask instance based on set option values.
     *
     * @return Piwik_ScheduledTask
     */
    public static function makeScheduledTask()
    {
        $instance = new Piwik_UserCountry_GeoIPAutoUpdater();

        $schedulePeriodStr = self::getSchedulePeriod();

        // created the scheduledtime instance, also, since GeoIP updates are done on tuesdays,
        // get new DBs on Wednesday
        switch ($schedulePeriodStr) {
            case self::SCHEDULE_PERIOD_WEEKLY:
                $schedulePeriod = new Piwik_ScheduledTime_Weekly();
                $schedulePeriod->setDay(3);
                break;
            case self::SCHEDULE_PERIOD_MONTHLY:
            default:
                $schedulePeriod = new Piwik_ScheduledTime_Monthly();
                $schedulePeriod->setDayOfWeek(3, 0);
                break;
        }

        return new Piwik_ScheduledTask($instance, 'update', null, $schedulePeriod, Piwik_ScheduledTask::LOWEST_PRIORITY);
    }

    /**
     * Sets the options used by this class based on query parameter values.
     *
     * See setUpdaterOptions for query params used.
     */
    public static function setUpdaterOptionsFromUrl()
    {
        self::setUpdaterOptions(array(
                                     'loc'    => Piwik_Common::getRequestVar('loc_db', false, 'string'),
                                     'isp'    => Piwik_Common::getRequestVar('isp_db', false, 'string'),
                                     'org'    => Piwik_Common::getRequestVar('org_db', false, 'string'),
                                     'period' => Piwik_Common::getRequestVar('period', false, 'string'),
                                ));
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
     */
    public static function setUpdaterOptions($options)
    {
        // set url options
        foreach (self::$urlOptions as $optionKey => $optionName) {
            if (!isset($options[$optionKey])) {
                continue;
            }

            Piwik_SetOption($optionName, $url = $options[$optionKey]);
        }

        // set period option
        if (!empty($options['period'])) {
            $period = $options['period'];
            if ($period != self::SCHEDULE_PERIOD_MONTHLY
                && $period != self::SCHEDULE_PERIOD_WEEKLY
            ) {
                throw new Exception(Piwik_Translate(
                    'UserCountry_InvalidGeoIPUpdatePeriod',
                    array("'$period'", "'" . self::SCHEDULE_PERIOD_MONTHLY . "', '" . self::SCHEDULE_PERIOD_WEEKLY . "'")
                ));
            }

            Piwik_SetOption(self::SCHEDULE_PERIOD_OPTION_NAME, $period);
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
        if (Piwik_GetOption(self::LOC_URL_OPTION_NAME) !== false
            || Piwik_GetOption(self::ISP_URL_OPTION_NAME) !== false
            || Piwik_GetOption(self::ORG_URL_OPTION_NAME) !== false
        ) {
            return true;
        }

        return false;
    }

    /**
     * Retrieves the URLs used to update various GeoIP database files.
     *
     * @return array
     */
    public static function getConfiguredUrls()
    {
        $result = array();
        foreach (self::$urlOptions as $key => $optionName) {
            $result[$key] = Piwik_GetOption($optionName);
        }
        return $result;
    }

    /**
     * Returns the confiured URL (if any) for a type of database.
     *
     * @param string $key 'loc', 'isp' or 'org'
     * @return string|false
     */
    public static function getConfiguredUrl($key)
    {
        if (empty(self::$urlOptions[$key])) {
            throw new Exception("Invalid key $key");
        }
        $url = Piwik_GetOption(self::$urlOptions[$key]);
        return $url;
    }

    /**
     * Performs a GeoIP database update.
     */
    public static function performUpdate()
    {
        $instance = new Piwik_UserCountry_GeoIPAutoUpdater();
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
        $period = Piwik_GetOption(self::SCHEDULE_PERIOD_OPTION_NAME);
        if ($period === false) {
            $period = self::SCHEDULE_PERIOD_MONTHLY;
        }
        return $period;
    }

    /**
     * Returns an array of strings for GeoIP databases that have update URLs configured, but
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
                $path = Piwik_UserCountry_LocationProvider_GeoIp::getPathToGeoIpDatabase(
                    Piwik_UserCountry_LocationProvider_GeoIp::$dbNames[$key]);
                if ($path === false) {
                    $result[] = $key;
                }
            }
        }
        return $result;
    }

    /**
     * Returns the extension of a URL used to update a GeoIP database, if it can be found.
     */
    public static function getGeoIPUrlExtension($url)
    {
        // check for &suffix= query param that is special to MaxMind URLs
        if (preg_match('/suffix=([^&]+)/', $url, $matches)) {
            return $matches[1];
        }

        // use basename of url
        $filenameParts = explode('.', basename($url), 2);
        if (count($filenameParts) > 1) {
            return end($filenameParts);
        } else {
            return reset($filenameParts);
        }
    }

    /**
     * Tests a location provider using a test IP address and catches PHP errors
     * (ie, notices) if they occur. PHP error information is held in self::$unzipPhpError.
     *
     * @param Piwik_UserCountry_LocationProvider $provider The provider to test.
     * @return array|false $location The result of geolocation. False if no location
     *                               can be found.
     */
    private static function getTestLocationCatchPhpErrors($provider)
    {
        // note: in most cases where this will fail, the error will usually be a PHP fatal error/notice.
        // in order to delete the files in such a case (which can be caused by a man-in-the-middle attack)
        // we need to catch them, so we set a new error handler.
        self::$unzipPhpError = null;
        set_error_handler(array('Piwik_UserCountry_GeoIPAutoUpdater', 'catchGeoIPError'));

        $location = $provider->getLocation(array('ip' => Piwik_UserCountry_LocationProvider_GeoIp::TEST_IP));

        restore_error_handler();

        return $location;
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
     */
    protected function performRedundantDbChecks()
    {
        $databaseTypes = array_keys(Piwik_UserCountry_LocationProvider_GeoIp::$dbNames);

        foreach ($databaseTypes as $type) {
            $customNames = array(
                'loc' => array(),
                'isp' => array(),
                'org' => array()
            );
            $customNames[$type] = Piwik_UserCountry_LocationProvider_GeoIp::$dbNames[$type];

            // create provider that only uses the DB type we're testing
            $provider = new Piwik_UserCountry_LocationProvider_GeoIp_Php($customNames);

            // test the provider. on error, we rename the broken DB.
            self::getTestLocationCatchPhpErrors($provider);
            if (self::$unzipPhpError !== null) {
                list($errno, $errstr, $errfile, $errline) = self::$unzipPhpError;
                Piwik::log("Piwik_UserCountry_GeoIPAutoUpdater: Encountered PHP error when performing redundant " .
                    "tests on GeoIP $type database: $errno: $errstr on line $errline of $errfile.");

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
     * Returns the path to a GeoIP database and a path to rename it to if it's broken.
     *
     * @param array $possibleDbNames The possible names of the database.
     * @return array Array with two elements, the path to the existing database, and
     *               the path to rename it to if it is broken. The second will end
     *               with something like .broken .
     */
    private function getOldAndNewPathsForBrokenDb($possibleDbNames)
    {
        $pathToDb = Piwik_UserCountry_LocationProvider_GeoIp::getPathToGeoIpDatabase($possibleDbNames);
        $newPath = false;

        if ($pathToDb !== false) {
            $newPath = $pathToDb . ".broken";
        }

        return array($pathToDb, $newPath);
    }

    /**
     * Custom PHP error handler used to catch any PHP errors that occur when
     * testing a downloaded GeoIP file.
     *
     * If we download a file that is supposed to be a GeoIP database, we need to make
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
     * @return Piwik_Date|false
     */
    public static function getLastRunTime()
    {
        $timestamp = Piwik_GetOption(self::LAST_RUN_TIME_OPTION_NAME);
        return $timestamp === false ? false : Piwik_Date::factory((int)$timestamp);
    }
}
