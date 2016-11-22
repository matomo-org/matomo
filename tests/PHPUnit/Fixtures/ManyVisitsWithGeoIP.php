<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Cache;
use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tests\Framework\Fixture;
use Exception;
use Piwik\Tests\Framework\Mock\LocationProvider as MockLocationProvider;
use Piwik\Tracker\Visit;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Framework/Mock/LocationProvider.php';

/**
 * Adds one new website and tracks 35 visits from 18 visitors with geolocation using
 * free GeoIP databases. The GeoIP databases are downloaded if they do not exist already.
 */
class ManyVisitsWithGeoIP extends Fixture
{
    const GEOIP_IMPL_TO_TEST = 'geoip_php';

    public $idSite = 1;
    public $dateTime = '2010-01-03 11:22:33';

    public $ips = array(
        '194.57.91.215', // in Besançon, FR (unicode city name)
        '::ffff:137.82.130.49', // in British Columbia (mapped ipv4)
        '137.82.130.0', // anonymization tests
        '137.82.0.0',
        '2001:db8:85a3:0:0:8a2e:370:7334', // ipv6
        '113.62.1.1', // in Lhasa, Tibet
        '151.100.101.92', // in Rome, Italy (using country DB, so only Italy will show)
        '103.29.196.229', // in Indonesia (Bali), (only Indonesia will show up)
    );

    public $userAgents = array(
        'Mozilla/5.0 (Linux; Android 4.4.2; Nexus 4 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.136 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; U; Android 2.3.7; fr-fr; HTC Desire Build/GRI40; MildWild CM-8.0 JG Stable) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
        'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.76 Safari/537.36',
        'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; GTB6.3; Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1) ; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; OfficeLiveConnector.1.4; OfficeLivePatch.1.3)',
        'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; MDDSJS; rv:11.0) like Gecko',
        'Mozilla/5.0 (Linux; Android 4.1.1; SGPT13 Build/TJDS0170) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.114 Safari/537.36',
        'Mozilla/5.0 (Linux; U; Android 4.3; zh-cn; SM-N9006 Build/JSS15J) AppleWebKit/537.36 (KHTML, like Gecko)Version/4.0 MQQBrowser/5.0 Mobile Safari/537.36',
        'Mozilla/5.0 (X11; U; Linux i686; ru; rv:1.9.0.14) Gecko/2009090216 Ubuntu/9.04 (jaunty) Firefox/3.0.14'
    );

    protected $idGoal;
    protected $idGoal2;

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        self::downloadGeoIpDbs();

        $this->setMockLocationProvider();
        $this->trackVisits(9, false);

        $this->setLocationProvider('GeoIPCity.dat');
        $this->trackVisits(2, true, $useLocal = false);
        $this->trackVisits(4, true, $useLocal = false, $doBulk = true);

        $this->setLocationProvider('GeoIP.dat');
        $this->trackVisits(2, true);

        $this->trackOtherVisits();

        $this->setLocationProvider('GeoIPCity.dat');
    }

    public function tearDown()
    {
        $this->unsetLocationProvider();
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime, 0, "Site 1");
        }

        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            $this->idGoal = API::getInstance()->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
        }

        if (!self::goalExists($idSite = 1, $idGoal = 2)) {
            $this->idGoal2 = API::getInstance()->addGoal($this->idSite, 'two', 'url', 'xxxxxxxxxxxxx', 'contains', false, 5);
        }
    }

    private function trackVisits($visitorCount, $setIp = false, $useLocal = true, $doBulk = false)
    {
        static $calledCounter = 0;
        $calledCounter++;

        $dateTime = $this->dateTime;
        $idSite = $this->idSite;

        if ($useLocal) {
            Cache::getTransientCache()->flushAll(); // make sure dimension cache is empty between local tracking runs
            Visit::$dimensions = null;
        }

        // use local tracker so mock location provider can be used
        $t = self::getTracker($idSite, $dateTime, $defaultInit = true, $useLocal);
        if ($doBulk) {
            $t->enableBulkTracking();
        }
        $t->setTokenAuth(self::getTokenAuth());
        for ($i = 0; $i != $visitorCount; ++$i) {
            $t->setVisitorId( substr(md5($i + $calledCounter * 1000), 0, $t::LENGTH_VISITOR_ID));
            if ($setIp) {
                $t->setIp(current($this->ips));
                $t->setUserAgent(current($this->userAgents));
                next($this->userAgents);
                next($this->ips);
            } else {
                $t->setIp("1.2.4.$i");
            }

            // first visit
            $date = Date::factory($dateTime)->addDay($i);
            $t->setForceVisitDateTime($date->getDatetime());
            $t->setUrl("http://piwik.net/grue/lair");
            $t->setCustomVariable(1, 'Cvar 1 name', 'Cvar1 value is ' .$i , 'visit');
            $t->setCustomVariable(5, 'Cvar 5 name', 'Cvar5 value is ' .$i , 'visit');
            $t->setCustomVariable(2, 'Cvar 2 PAGE name', 'Cvar2 PAGE value is ' .$i, 'page');
            $t->setCustomVariable(5, 'Cvar 5 PAGE name', 'Cvar5 PAGE value is ' .$i, 'page');

            $r = $t->doTrackPageView('It\'s <script> pitch black...');
            if (!$doBulk) {
                self::checkResponse($r);
            }

            // second visit
            $date = $date->addHour(1);
            $t->setForceVisitDateTime($date->getDatetime());
            $t->setUrl("http://piwik.net/space/quest/iv");

            // Manually record some data
            $t->setDebugStringAppend(
                '&_idts='. $date->subDay(100)->getTimestampUTC(). // first visit timestamp
                '&_ects='. $date->subDay(50)->getTimestampUTC(). // Timestamp ecommerce
                '&_viewts='. $date->subDay(10)->getTimestampUTC(). // Last visit timestamp
                '&_idvc=5' // Visit count
            );
            $r = $t->doTrackPageView("Space Quest XII");

            if (!$doBulk) {
                self::checkResponse($r);
            }

            // Track site search (for AutoSuggestAPI test)
            // Only for half visitors so they don't all have a "site search" as last action and some of them have a standard page view as last action
            $date = $date->addHour(0.1);
            $t->setForceVisitDateTime($date->getDatetime());
            if( ($i % 2) == 0) {
                $r = $t->doTrackSiteSearch('Bring on the party', 'CAT');
            }

            if (!$doBulk) {
                self::checkResponse($r);
            }

            $date = $date->addHour(0.2);
            $t->setForceVisitDateTime($date->getDatetime());
            $r = $t->doTrackGoal($this->idGoal2);
            if (!$doBulk) {
                self::checkResponse($r);
            }

            $date = $date->addHour(0.05);
            $t->setForceVisitDateTime($date->getDatetime());
            $t->doTrackAction('http://example.org/path/file' . $i . '.zip', "download" );
            if (!$doBulk) {
                self::checkResponse($r);
            }

            $date = $date->addHour(0.05);
            $t->setForceVisitDateTime($date->getDatetime());
            $r = $t->doTrackAction('http://example-outlink.org/' . $i . '.html', "link" );
            if (!$doBulk) {
                self::checkResponse($r);
            }

            $date = $date->addHour(0.05);
            $t->setForceVisitDateTime($date->getDatetime());
            $r = $t->doTrackEvent('Cat' . $i, 'Action' . $i, 'Name' . $i, 345.678 + $i );

            if (!$doBulk) {
                self::checkResponse($r);
            }

        }
        if ($doBulk) {
            self::checkBulkTrackingResponse($t->doBulkTrack());
        }
    }

    private function trackOtherVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);
        $t->setVisitorId('fed33392d3a48ab2');
        $t->setTokenAuth(self::getTokenAuth());
        $t->setForceVisitDateTime(Date::factory($dateTime)->addDay(20)->getDatetime());
        $t->setIp('194.57.91.215');
        $t->setUserId('userid.email@example.org');
        $t->setCountry('us');
        $t->setRegion('CA');
        $t->setCity('not a city');
        $t->setLatitude(1);
        $t->setLongitude(2);
        $t->setUrl("http://piwik.net/grue/lair");
        $t->setUrlReferrer('http://google.com/?q=Wikileaks FTW');
        $t->setUserAgent("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) AppleWebKit/522+ (KHTML, like Gecko) Safari/419.3 (.NET CLR 3.5.30729)");
        self::checkResponse($t->doTrackPageView('It\'s pitch black...'));
    }

    public function setLocationProvider($file)
    {
        GeoIp::$dbNames['loc'] = array($file);
        GeoIp::$geoIPDatabaseDir = 'tests/lib/geoip-files';
        LocationProvider::$providers = null;
        LocationProvider::setCurrentProvider(self::GEOIP_IMPL_TO_TEST);

        if (LocationProvider::getCurrentProviderId() !== self::GEOIP_IMPL_TO_TEST) {
            throw new Exception("Failed to set the current location provider to '" . self::GEOIP_IMPL_TO_TEST . "'.");
        }

        $possibleFiles = GeoIp::$dbNames['loc'];
        if (GeoIp::getPathToGeoIpDatabase($possibleFiles) === false) {
            throw new Exception("The GeoIP location provider cannot find the '$file' file! Tests will fail.");
        }
    }

    private function setMockLocationProvider()
    {
        LocationProvider::$providers = array();
        LocationProvider::$providers[] = new MockLocationProvider();
        LocationProvider::setCurrentProvider('mock_provider');
        MockLocationProvider::$locations = array(
            self::makeLocation('Stratford-upon-Avon', 'P3', 'gb', 123.456, 21.321), // template location

            // same region, different city, same country
            self::makeLocation('Nuneaton and Bedworth', 'P3', 'gb', $isp = 'comcast.net'),

            // same region, city & country (different lat/long)
            self::makeLocation('Stratford-upon-Avon', 'P3', 'gb', 124.456, 22.231, $isp = 'comcast.net'),

            // same country, different region & city
            self::makeLocation('London', 'H9', 'gb'),

            // same country, different region, same city
            self::makeLocation('Stratford-upon-Avon', 'G5', 'gb', $lat = null, $long = null, $isp = 'awesomeisp.com'),

            // different country, diff region, same city
            self::makeLocation('Stratford-upon-Avon', '66', 'ru'),

            // different country, diff region (same as last), different city
            self::makeLocation('Hluboká nad Vltavou', '66', 'ru'),

            // different country, diff region (same as last), same city
            self::makeLocation('Stratford-upon-Avon', '66', 'mk'),

            // unknown location
            self::makeLocation(null, null, null),
        );
    }

    public static function unsetLocationProvider()
    {
        try {
            LocationProvider::setCurrentProvider('default');
        } catch(Exception $e) {
            // ignore error
        }
    }
}
