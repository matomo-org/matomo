<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

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
    const IMAGES_GENERATED_FOR_PHP = '5.4';
    const IMAGES_GENERATED_FOR_GD = '2.0.36';

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
     * @return int    idSite of website created
     */
    public static function createWebsite($dateTime, $ecommerce = 0, $siteName = 'Piwik test', $siteUrl = false,
                                         $siteSearch = 1, $searchKeywordParameters = null,
                                         $searchCategoryParameters = null)
    {
        $idSite = Piwik_SitesManager_API::getInstance()->addSite(
            $siteName,
            $siteUrl === false ? "http://piwik.net/" : $siteUrl,
            $ecommerce,
            $siteSearch, $searchKeywordParameters, $searchCategoryParameters,
            $ips = null,
            $excludedQueryParameters = null,
            $timezone = null,
            $currency = null
        );

        // Manually set the website creation date to a day earlier than the earliest day we record stats for
        Zend_Registry::get('db')->update(Piwik_Common::prefixTable("site"),
            array('ts_created' => Piwik_Date::factory($dateTime)->subDay(1)->getDatetime()),
            "idsite = $idSite"
        );

        // Clear the memory Website cache
        Piwik_Site::clearCache();

        return $idSite;
    }

    /**
     * Returns URL to Piwik root.
     *
     * @return string
     */
    public static function getRootUrl()
    {
        $piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();

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
     * @param         $idSite
     * @param         $dateTime
     * @param boolean $defaultInit If set to true, the tracker object will have default IP, user agent, time, resolution, etc.
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
        self::assertEquals($expectedResponse, $response, "Expected GIF beacon, got: <br/>\n"
            . var_export($response, true)
            . "\n If you are stuck, you can enable \$GLOBALS['PIWIK_TRACKER_DEBUG']=true; in piwik.php to get more debug info."
            . base64_encode($response)
        );
    }

    public static function makeLocation($city, $region, $country, $lat = null, $long = null, $isp = null)
    {
        return array(Piwik_UserCountry_LocationProvider::CITY_NAME_KEY    => $city,
                     Piwik_UserCountry_LocationProvider::REGION_CODE_KEY  => $region,
                     Piwik_UserCountry_LocationProvider::COUNTRY_CODE_KEY => $country,
                     Piwik_UserCountry_LocationProvider::LATITUDE_KEY     => $lat,
                     Piwik_UserCountry_LocationProvider::LONGITUDE_KEY    => $long,
                     Piwik_UserCountry_LocationProvider::ISP_KEY          => $isp);
    }

    /**
     * Returns the super user token auth that can be used in tests. Can be used to
     * do bulk tracking.
     *
     * @return string
     */
    public static function getTokenAuth()
    {
        return Piwik_UsersManager_API::getInstance()->getTokenAuth(
            Piwik_Config::getInstance()->superuser['login'],
            Piwik_Config::getInstance()->superuser['password']
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
        // fake access is needed so API methods can call Piwik::getCurrentUserLogin(), e.g: 'PDFReports.addReport'
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Zend_Registry::set('access', $pseudoMockAccess);

        // retrieve available reports
        $availableReportMetadata = Piwik_PDFReports_API::getReportMetadata($idSite, Piwik_PDFReports::EMAIL_TYPE);

        $availableReportIds = array();
        foreach ($availableReportMetadata as $reportMetadata) {
            $availableReportIds[] = $reportMetadata['uniqueId'];
        }

        //@review should we also test evolution graphs?
        // set-up mail report
        Piwik_PDFReports_API::getInstance()->addReport(
            $idSite,
            'Mail Test report',
            'day', // overridden in getApiForTestingScheduledReports()
            0,
            Piwik_PDFReports::EMAIL_TYPE,
            Piwik_ReportRenderer::HTML_FORMAT, // overridden in getApiForTestingScheduledReports()
            $availableReportIds,
            array(Piwik_PDFReports::DISPLAY_FORMAT_PARAMETER => Piwik_PDFReports::DISPLAY_FORMAT_TABLES_ONLY)
        );

        // set-up sms report for one website
        Piwik_PDFReports_API::getInstance()->addReport(
            $idSite,
            'SMS Test report, one website',
            'day', // overridden in getApiForTestingScheduledReports()
            0,
            Piwik_MobileMessaging::MOBILE_TYPE,
            Piwik_MobileMessaging::SMS_FORMAT,
            array("MultiSites_getOne"),
            array("phoneNumbers" => array())
        );

        // set-up sms report for all websites
        Piwik_PDFReports_API::getInstance()->addReport(
            $idSite,
            'SMS Test report, all websites',
            'day', // overridden in getApiForTestingScheduledReports()
            0,
            Piwik_MobileMessaging::MOBILE_TYPE,
            Piwik_MobileMessaging::SMS_FORMAT,
            array("MultiSites_getAll"),
            array("phoneNumbers" => array())
        );

        if (self::canImagesBeIncludedInScheduledReports()) {
            // set-up mail report with images
            Piwik_PDFReports_API::getInstance()->addReport(
                $idSite,
                'Mail Test report',
                'day', // overridden in getApiForTestingScheduledReports()
                0,
                Piwik_PDFReports::EMAIL_TYPE,
                Piwik_ReportRenderer::HTML_FORMAT, // overridden in getApiForTestingScheduledReports()
                $availableReportIds,
                array(Piwik_PDFReports::DISPLAY_FORMAT_PARAMETER => Piwik_PDFReports::DISPLAY_FORMAT_TABLES_AND_GRAPHS)
            );

            // set-up mail report with one row evolution based png graph
            Piwik_PDFReports_API::getInstance()->addReport(
                $idSite,
                'Mail Test report',
                'day',
                0,
                Piwik_PDFReports::EMAIL_TYPE,
                Piwik_ReportRenderer::HTML_FORMAT,
                array('Actions_getPageTitles'),
                array(
                     Piwik_PDFReports::DISPLAY_FORMAT_PARAMETER => Piwik_PDFReports::DISPLAY_FORMAT_GRAPHS_ONLY,
                     Piwik_PDFReports::EVOLUTION_GRAPH_PARAMETER => 'true',
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
            $gdInfo['GD Version'] == self::IMAGES_GENERATED_FOR_GD;
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
            throw new Exception("gunzip failed($return): " . implode("\n", $output));
        }
    }

    protected static function executeLogImporter($logFile, $options)
    {
        $python = Piwik_Common::isWindows() ? "C:\Python27\python.exe" : 'python';

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
}
