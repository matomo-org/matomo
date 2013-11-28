<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UserCountry\LocationProvider\GeoIp;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/MockLocationProvider.php';

/**
 * Adds one new website and tracks 35 visits from 18 visitors with geolocation using
 * free GeoIP databases. The GeoIP databases are downloaded if they do not exist already.
 */
class Test_Piwik_Fixture_ManyVisitsWithGeoIP extends Test_Piwik_BaseFixture
{
    const GEOIP_IMPL_TO_TEST = 'geoip_php';

    public $idSite = 1;
    public $dateTime = '2010-01-03 11:22:33';

    public $ips = array(
        '194.57.91.215', // in Besançon, FR (unicode city name)
        '::ffff:137.82.130.49', // in British Columbia (mapped ipv4)
        '137.82.130.0', // anonymization tests
        '137.82.0.0',
        '2001:db8:85a3:0:0:8a2e:370:7334', // ipv6 (geoip lookup not supported)
        '113.62.1.1', // in Lhasa, Tibet
        '151.100.101.92', // in Rome, Italy (using country DB, so only Italy will show)
        '103.29.196.229', // in Indonesia (Bali), (only Indonesia will show up)
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
        self::createWebsite($this->dateTime, 0, "Site 1");
        $this->idGoal = API::getInstance()->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
        $this->idGoal2 = API::getInstance()->addGoal($this->idSite, 'two', 'url', 'xxxxxxxxxxxxx', 'contains', false, 5);
    }

    private function trackVisits($visitorCount, $setIp = false, $useLocal = true, $doBulk = false)
    {
        static $calledCounter = 0;
        $calledCounter++;

        $dateTime = $this->dateTime;
        $idSite = $this->idSite;

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
        $t->setCountry('us');
        $t->setRegion('CA');
        $t->setCity('not a city');
        $t->setLatitude(1);
        $t->setLongitude(2);
        $t->setUrl("http://piwik.net/grue/lair");
        $t->setUrlReferrer('http://google.com/?q=Wikileaks FTW');
        self::checkResponse($t->doTrackPageView('It\'s pitch black...'));
    }

    private function setLocationProvider($file)
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
        LocationProvider::$providers = null;
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

    private function unsetLocationProvider()
    {
        LocationProvider::setCurrentProvider('default');
    }

}
