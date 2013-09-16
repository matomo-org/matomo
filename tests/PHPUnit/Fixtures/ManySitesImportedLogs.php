<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Access;
use Piwik\Plugins\Goals\API as APIGoals;
use Piwik\Plugins\SegmentEditor\API as APISegmentEditor;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

/**
 * Imports visits from several log files using the python log importer.
 */
class Test_Piwik_Fixture_ManySitesImportedLogs extends Test_Piwik_BaseFixture
{
    public $dateTime = '2012-08-09 11:22:33';
    public $idSite = 1;
    public $idSite2 = 2;
    public $idGoal = 1;
    public $segments = null; // should be array mapping segment name => segment definition
    
    public $addSegments = false;

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        self::downloadGeoIpDbs();

        LocationProvider::$providers = null;
        GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';
        LocationProvider::setCurrentProvider('geoip_php');

        $this->trackVisits();
        $this->setupSegments();
    }

    public function tearDown()
    {
        LocationProvider::$providers = null;
        GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';
        LocationProvider::setCurrentProvider('default');
    }

    public function setUpWebsitesAndGoals()
    {
        // for conversion testing
        self::createWebsite($this->dateTime);
        APIGoals::getInstance()->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
        self::createWebsite($this->dateTime, $ecommerce = 0, $siteName = 'Piwik test two',
            $siteUrl = 'http://example-site-two.com');
    }
    
    public function getDefaultSegments()
    {
        return array(
            'segmentOnlyOneSite'   => array('definition'      => 'browserCode==IE',
                                            'idSite'          => $this->idSite,
                                            'autoArchive'     => true,
                                            'enabledAllUsers' => true),
            'segmentNoAutoArchive' => array('definition'      => 'customVariableName1==Not-bot',
                                            'idSite'          => false,
                                            'autoArchive'     => false,
                                            'enabledAllUsers' => true),
            'segmentOnlySuperuser' => array('definition'      => 'customVariablePageName1==HTTP-code',
                                            'idSite'          => false,
                                            'autoArchive'     => true,
                                            'enabledAllUsers' => false),
        );
    }

    private function trackVisits()
    {
        $this->logVisitsWithStaticResolver();
        $this->logVisitsWithAllEnabled();
        $this->replayLogFile();
        $this->logCustomFormat();
    }
    
    private function setupSegments()
    {
        if (!$this->addSegments) {
            return;
        }
        
        if ($this->segments === null) {
            $this->segments = $this->getDefaultSegments();
        }
        
        foreach ($this->segments as $segmentName => $info) {
            $idSite = false;
            if (isset($info['idSite'])) {
                $idSite = $info['idSite'];
            }
            
            $autoArchive = true;
            if (isset($info['autoArchive'])) {
                $autoArchive = $info['autoArchive'];
            }
            
            $enabledAllUsers = true;
            if (isset($info['enabledAllUsers'])) {
                $enabledAllUsers = $info['enabledAllUsers'];
            }
            
            APISegmentEditor::getInstance()->add(
                $segmentName, $info['definition'], $idSite, $autoArchive, $enabledAllUsers);
        }
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
                      '--enable-reverse-dns'        => false,
                      '--force-lowercase-path'      => false);

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
}

// needed by tests that use stored segments w/ the proxy index.php
class Test_Access_OverrideLogin extends Access
{
    public function getLogin()
    {
        return 'superUserLogin';
    }
}
