<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Access;
use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\MobileMessaging\MobileMessaging;
use Piwik\Plugins\ScheduledReports\API as APIScheduledReports;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\ReportRenderer;
use Piwik\Site;
use Piwik\Url;

/**
 * Base type for all integration test fixtures. Integration test fixtures
 * add visit and related data to the database before a test is run. Different
 * tests can use the same fixtures.
 *
 * This class defines a set of helper methods for fixture types. The helper
 * methods are public, but ideally they should only be used by fixture types.
 *
 * NOTE: YOU SHOULD NOT CREATE A NEW FIXTURE UNLESS THERE IS NO WAY TO MODIFY
 * AN EXISTING FIXTURE TO HANDLE YOUR USE CASE.
 *
 * Related TODO: we should try and reduce the amount of existing fixtures by
 *                merging some together.
 */
abstract class Test_Piwik_BaseFixture extends PHPUnit_Framework_Assert
{
    const IMAGES_GENERATED_ONLY_FOR_OS = 'linux';
    const IMAGES_GENERATED_FOR_PHP = '5.5';
    const IMAGES_GENERATED_FOR_GD = '2.1.1';
    const DEFAULT_SITE_NAME = 'Piwik test';

    /** Adds data to Piwik. Creates sites, tracks visits, imports log files, etc. */
    public abstract function setUp();

    /** Does any clean up. Most of the time there will be no need to clean up. */
    public abstract function tearDown();

    /**
     * Creates a website, then sets its creation date to a day earlier than specified dateTime
     * Useful to create a website now, but force data to be archived back in the past.
     *
     * @param string $dateTime eg '2010-01-01 12:34:56'
     * @param int $ecommerce
     * @param string $siteName
     *
     * @param bool|string $siteUrl
     * @param int $siteSearch
     * @param null|string $searchKeywordParameters
     * @param null|string $searchCategoryParameters
     * @return int    idSite of website created
     */
    public static function createWebsite($dateTime, $ecommerce = 0, $siteName = false, $siteUrl = false,
                                         $siteSearch = 1, $searchKeywordParameters = null,
                                         $searchCategoryParameters = null, $timezone = null)
    {
        if($siteName === false) {
            $siteName = self::DEFAULT_SITE_NAME;
        }
        $idSite = APISitesManager::getInstance()->addSite(
            $siteName,
            $siteUrl === false ? "http://piwik.net/" : $siteUrl,
            $ecommerce,
            $siteSearch, $searchKeywordParameters, $searchCategoryParameters,
            $ips = null,
            $excludedQueryParameters = null,
            $timezone,
            $currency = null
        );

        // Manually set the website creation date to a day earlier than the earliest day we record stats for
        Db::get()->update(Common::prefixTable("site"),
            array('ts_created' => Date::factory($dateTime)->subDay(1)->getDatetime()),
            "idsite = $idSite"
        );

        // Clear the memory Website cache
        Site::clearCache();

        return $idSite;
    }

    /**
     * Returns URL to Piwik root.
     *
     * @return string
     */
    public static function getRootUrl()
    {
        $piwikUrl = Url::getCurrentUrlWithoutFileName();

        $pathBeforeRoot = 'tests';
        // Running from a plugin
        if (strpos($piwikUrl, 'plugins/') !== false) {
            $pathBeforeRoot = 'plugins';
        }

        $testsInPath = strpos($piwikUrl, $pathBeforeRoot . '/');
        if ($testsInPath !== false) {
            $piwikUrl = substr($piwikUrl, 0, $testsInPath);
        }
        return $piwikUrl;
    }

    /**
     * Returns URL to the proxy script, used to ensure piwik.php
     * uses the test environment, and allows variable overwriting
     *
     * @return string
     */
    public static function getTrackerUrl()
    {
        return self::getRootUrl() . 'tests/PHPUnit/proxy/piwik.php';
    }

    /**
     * Returns a PiwikTracker object that you can then use to track pages or goals.
     *
     * @param int     $idSite
     * @param string  $dateTime
     * @param boolean $defaultInit If set to true, the tracker object will have default IP, user agent, time, resolution, etc.
     * @param bool    $useLocal
     *
     * @return PiwikTracker
     */
    public static function getTracker($idSite, $dateTime, $defaultInit = true, $useLocal = false)
    {
        if ($useLocal) {
            require_once PIWIK_INCLUDE_PATH . '/tests/LocalTracker.php';
            $t = new Piwik_LocalTracker($idSite, self::getTrackerUrl());
        } else {
            $t = new PiwikTracker($idSite, self::getTrackerUrl());
        }
        $t->setForceVisitDateTime($dateTime);

        if ($defaultInit) {
            $t->setTokenAuth(self::getTokenAuth());
            $t->setIp('156.5.3.2');

            // Optional tracking
            $t->setUserAgent("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)");
            $t->setBrowserLanguage('fr');
            $t->setLocalTime('12:34:06');
            $t->setResolution(1024, 768);
            $t->setBrowserHasCookies(true);
            $t->setPlugins($flash = true, $java = true, $director = false);
        }
        return $t;
    }

    /**
     * Checks that the response is a GIF image as expected.
     * Will fail the test if the response is not the expected GIF
     *
     * @param $response
     */
    public static function checkResponse($response)
    {
        $trans_gif_64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
        $expectedResponse = base64_decode($trans_gif_64);

        $url = "\n =========================== \n URL was: " . PiwikTracker::$DEBUG_LAST_REQUESTED_URL;
        self::assertEquals($expectedResponse, $response, "Expected GIF beacon, got: <br/>\n"
            . var_export($response, true)
            . "\n If you are stuck, you can enable \$GLOBALS['PIWIK_TRACKER_DEBUG']=true; in piwik.php to get more debug info."
            . base64_encode($response)
            . $url
        );
    }

    /**
     * Checks that the response from bulk tracking is a valid JSON
     * string. Will fail the test if JSON status is not success.
     *
     * @param $response
     */
    public static function checkBulkTrackingResponse($response) {
        $data = json_decode($response, true);
        if (!is_array($data) || empty($response)) {
            throw new Exception("Bulk tracking response (".$response.") is not an array: " . var_export($data, true) . "\n");
        }
        if(!isset($data['status'])) {
            throw new Exception("Returned data didn't have a status: " . var_export($data,true));
        }
        self::assertArrayHasKey('status', $data);
        self::assertEquals('success', $data['status']);
    }

    public static function makeLocation($city, $region, $country, $lat = null, $long = null, $isp = null)
    {
        return array(LocationProvider::CITY_NAME_KEY    => $city,
                     LocationProvider::REGION_CODE_KEY  => $region,
                     LocationProvider::COUNTRY_CODE_KEY => $country,
                     LocationProvider::LATITUDE_KEY     => $lat,
                     LocationProvider::LONGITUDE_KEY    => $long,
                     LocationProvider::ISP_KEY          => $isp);
    }

    /**
     * Returns the super user token auth that can be used in tests. Can be used to
     * do bulk tracking.
     *
     * @return string
     */
    public static function getTokenAuth()
    {
        return APIUsersManager::getInstance()->getTokenAuth(
            Config::getInstance()->superuser['login'],
            Config::getInstance()->superuser['password']
        );
    }

    /**
     * Create three MAIL and two MOBILE scheduled reports
     *
     * Reports sent by mail can contain PNG graphs when the user specifies it.
     * Depending on the system under test, generated images differ slightly.
     * Because of this discrepancy, PNG graphs are only tested if the system under test
     * has the characteristics described in 'canImagesBeIncludedInScheduledReports'.
     * See tests/README.md for more detail.
     *
     * @see canImagesBeIncludedInScheduledReports
     * @param int $idSite id of website created
     */
    public static function setUpScheduledReports($idSite)
    {
        // fake access is needed so API methods can call Piwik::getCurrentUserLogin(), e.g: 'ScheduledReports.addReport'
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Access::setSingletonInstance($pseudoMockAccess);

        // retrieve available reports
        $availableReportMetadata = APIScheduledReports::getReportMetadata($idSite, ScheduledReports::EMAIL_TYPE);

        $availableReportIds = array();
        foreach ($availableReportMetadata as $reportMetadata) {
            $availableReportIds[] = $reportMetadata['uniqueId'];
        }

        //@review should we also test evolution graphs?
        // set-up mail report
        APIScheduledReports::getInstance()->addReport(
            $idSite,
            'Mail Test report',
            'day', // overridden in getApiForTestingScheduledReports()
            0,
            ScheduledReports::EMAIL_TYPE,
            ReportRenderer::HTML_FORMAT, // overridden in getApiForTestingScheduledReports()
            $availableReportIds,
            array(ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_ONLY)
        );

        // set-up sms report for one website
        APIScheduledReports::getInstance()->addReport(
            $idSite,
            'SMS Test report, one website',
            'day', // overridden in getApiForTestingScheduledReports()
            0,
            MobileMessaging::MOBILE_TYPE,
            MobileMessaging::SMS_FORMAT,
            array("MultiSites_getOne"),
            array("phoneNumbers" => array())
        );

        // set-up sms report for all websites
        APIScheduledReports::getInstance()->addReport(
            $idSite,
            'SMS Test report, all websites',
            'day', // overridden in getApiForTestingScheduledReports()
            0,
            MobileMessaging::MOBILE_TYPE,
            MobileMessaging::SMS_FORMAT,
            array("MultiSites_getAll"),
            array("phoneNumbers" => array())
        );

        if (self::canImagesBeIncludedInScheduledReports()) {
            // set-up mail report with images
            APIScheduledReports::getInstance()->addReport(
                $idSite,
                'Mail Test report',
                'day', // overridden in getApiForTestingScheduledReports()
                0,
                ScheduledReports::EMAIL_TYPE,
                ReportRenderer::HTML_FORMAT, // overridden in getApiForTestingScheduledReports()
                $availableReportIds,
                array(ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_TABLES_AND_GRAPHS)
            );

            // set-up mail report with one row evolution based png graph
            APIScheduledReports::getInstance()->addReport(
                $idSite,
                'Mail Test report',
                'day',
                0,
                ScheduledReports::EMAIL_TYPE,
                ReportRenderer::HTML_FORMAT,
                array('Actions_getPageTitles'),
                array(
                     ScheduledReports::DISPLAY_FORMAT_PARAMETER => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY,
                     ScheduledReports::EVOLUTION_GRAPH_PARAMETER => 'true',
                )
            );
        }
    }

    /**
     * Return true if system under test has Piwik core team's most common configuration
     */
    public static function canImagesBeIncludedInScheduledReports()
    {
        $gdInfo = gd_info();
        return
            stristr(php_uname(), self::IMAGES_GENERATED_ONLY_FOR_OS) &&
            strpos( phpversion(), self::IMAGES_GENERATED_FOR_PHP) !== false &&
            strpos( $gdInfo['GD Version'], self::IMAGES_GENERATED_FOR_GD) !== false;
    }

    public static $geoIpDbUrl = 'http://piwik-team.s3.amazonaws.com/GeoIP.dat.gz';
    public static $geoLiteCityDbUrl = 'http://piwik-team.s3.amazonaws.com/GeoLiteCity.dat.gz';

    public static function downloadGeoIpDbs()
    {
        $geoIpOutputDir = PIWIK_INCLUDE_PATH . '/tests/lib/geoip-files';
        self::downloadAndUnzip(self::$geoIpDbUrl, $geoIpOutputDir, 'GeoIP.dat');
        self::downloadAndUnzip(self::$geoLiteCityDbUrl, $geoIpOutputDir, 'GeoIPCity.dat');
    }

    public static function downloadAndUnzip($url, $outputDir, $filename)
    {
        $bufferSize = 1024 * 1024;

        if (!is_dir($outputDir)) {
            mkdir($outputDir);
        }

        $deflatedOut = $outputDir . '/' . $filename;
        $outfileName = $deflatedOut . '.gz';

        if (file_exists($deflatedOut)) {
            return;
        }

        $dump = fopen($url, 'rb');
        $outfile = fopen($outfileName, 'wb');
        $bytesRead = 0;
        while (!feof($dump)) {
            fwrite($outfile, fread($dump, $bufferSize), $bufferSize);
            $bytesRead += $bufferSize;
        }
        fclose($dump);
        fclose($outfile);

        // unzip the dump
        exec("gunzip -c \"" . $outfileName . "\" > \"$deflatedOut\"", $output, $return);
        if ($return !== 0) {
            \Piwik\Log::info("gunzip failed with file that has following contents:");
            \Piwik\Log::info(file_get_contents($outfile));
            throw new Exception("gunzip failed($return): " . implode("\n", $output));
        }
    }

    protected static function executeLogImporter($logFile, $options)
    {
        $python = \Piwik\SettingsServer::isWindows() ? "C:\Python27\python.exe" : 'python';

        // create the command
        $cmd = $python
            . ' "' . PIWIK_INCLUDE_PATH . '/misc/log-analytics/import_logs.py" ' # script loc
            . '-ddd ' // debug
            . '--url="' . self::getRootUrl() . 'tests/PHPUnit/proxy/" ' # proxy so that piwik uses test config files
        ;

        foreach ($options as $name => $value) {
            $cmd .= $name;
            if ($value !== false) {
                $cmd .= '="' . $value . '"';
            }
            $cmd .= ' ';
        }

        $cmd .= '"' . $logFile . '" 2>&1';

        // run the command
        exec($cmd, $output, $result);
        if ($result !== 0) {
            throw new Exception("log importer failed: " . implode("\n", $output) . "\n\ncommand used: $cmd");
        }

        return $output;
    }

    public static function siteCreated($idSite)
    {
        return Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('site') . " WHERE idsite = ?", array($idSite)) != 0;
    }

    public static function goalExists($idSite, $idGoal)
    {
        return Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('goal') . " WHERE idgoal = ? AND idsite = ?", array($idGoal, $idSite)) != 0;
    }
}