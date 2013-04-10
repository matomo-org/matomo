<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Imports visits from several log files using the python log importer.
 */
class Test_Piwik_Fixture_ManySitesImportedLogs extends Test_Piwik_BaseFixture
{
    public $dateTime = '2010-03-06 11:22:33';
    public $idSite = 1;
    public $idSite2 = 2;
    public $idGoal = 1;

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        self::downloadGeoIpDbs();

        Piwik_UserCountry_LocationProvider::$providers = null;
        Piwik_UserCountry_LocationProvider_GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';
        Piwik_UserCountry_LocationProvider::setCurrentProvider('geoip_php');

        $this->trackVisits();
    }

    public function tearDown()
    {
        Piwik_UserCountry_LocationProvider::$providers = null;
        Piwik_UserCountry_LocationProvider_GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';
        Piwik_UserCountry_LocationProvider::setCurrentProvider('default');
    }

    public function setUpWebsitesAndGoals()
    {
        // for conversion testing
        self::createWebsite($this->dateTime);
        Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
        self::createWebsite($this->dateTime, $ecommerce = 0, $siteName = 'Piwik test two',
            $siteUrl = 'http://example-site-two.com');
    }

    private function trackVisits()
    {
        $this->logVisitsWithStaticResolver();
        $this->logVisitsWithAllEnabled();
        $this->replayLogFile();
        $this->logCustomFormat();
    }

    /**
     * Logs a couple visits for Aug 9, Aug 10, Aug 11 of 2012, for site we create.
     */
    private function logVisitsWithStaticResolver()
    {
        $logFile = PIWIK_INCLUDE_PATH . '/tests/resources/fake_logs.log'; # log file

        $opts = array('--idsite'                    => $this->idSite,
                      '--token-auth'                => self::getTokenAuth(),
                      '--recorders'                 => '4',
                      '--recorder-max-payload-size' => '2');

        self::executeLogImporter($logFile, $opts);
    }

    /**
     * Logs a couple visits for the site we created and two new sites that do not
     * exist yet. Visits are from Aug 12, 13 & 14 of 2012.
     */
    public function logVisitsWithDynamicResolver()
    {
        $logFile = PIWIK_INCLUDE_PATH . '/tests/resources/fake_logs_dynamic.log'; # log file

        $opts = array('--add-sites-new-hosts'       => false,
                      '--token-auth'                => self::getTokenAuth(),
                      '--recorders'                 => '4',
                      '--recorder-max-payload-size' => '1');

        self::executeLogImporter($logFile, $opts);
    }

    /**
     * Logs a couple visits for the site we created w/ all log importer options
     * enabled. Visits are for Aug 11 of 2012.
     */
    private function logVisitsWithAllEnabled()
    {
        $logFile = PIWIK_INCLUDE_PATH . '/tests/resources/fake_logs_enable_all.log';

        $opts = array('--idsite'                    => $this->idSite,
                      '--token-auth'                => self::getTokenAuth(),
                      '--recorders'                 => '4',
                      '--recorder-max-payload-size' => '2',
                      '--enable-static'             => false,
                      '--enable-bots'               => false,
                      '--enable-http-errors'        => false,
                      '--enable-http-redirects'     => false,
                      '--enable-reverse-dns'        => false);

        self::executeLogImporter($logFile, $opts);
    }

    /**
     * Logs a couple visit using log entries that are tracking requests to a piwik.php file.
     * Adds two visits to idSite=1 and two to non-existant sites.
     */
    private function replayLogFile()
    {
        $logFile = PIWIK_INCLUDE_PATH . '/tests/resources/fake_logs_replay.log';

        $opts = array('--token-auth'                => self::getTokenAuth(),
                      '--recorders'                 => '4',
                      '--recorder-max-payload-size' => '2',
                      '--replay-tracking'           => false);

        self::executeLogImporter($logFile, $opts);
    }

    /**
     * Imports a log file in custom format that contains generation time
     */
    private function logCustomFormat()
    {
        $logFile = PIWIK_INCLUDE_PATH . '/tests/resources/fake_logs_custom.log';

        $opts = array('--idsite'           => $this->idSite,
                      '--token-auth'       => self::getTokenAuth(),
                      '--log-format-regex' => '(?P<ip>\S+) - - \[(?P<date>.*?) (?P<timezone>.*?)\] (?P<status>\S+) '
                          . '\"\S+ (?P<path>.*?) \S+\" (?P<generation_time_micro>\S+)');

        self::executeLogImporter($logFile, $opts);
    }

    private static function executeLogImporter($logFile, $options)
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
