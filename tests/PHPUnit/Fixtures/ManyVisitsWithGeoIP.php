<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Cache;
use Piwik\Date;
use Piwik\Option;
use Piwik\Plugins\Goals\API;
use Piwik\Plugins\GeoIp2\LocationProvider\GeoIp2;
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
    const GEOIP_IMPL_TO_TEST = 'geoip2php';

    public $idSite = 1;
    public $dateTime = '2010-01-03 11:22:33';

    public $ips = array(
        '194.57.91.215', // in Besançon, FR (unicode city name)
        '::ffff:137.82.130.49', // in British Columbia (mapped ipv4)
        '137.82.130.0', // anonymization tests
        '137.82.0.0',
        '2003:f6:93bf:26f:9ec7:a6ff:fe29:27df', // ipv6 in US (without region or city)
        '113.62.1.1', // in Lhasa, Tibet
        '151.100.101.92', // in Rome, Italy (using country DB, so only Italy will show)
        '103.29.196.229', // in Indonesia, Central Java (Bali)
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

    public function setUp(): void
    {
        // set option, so tracked data for the past won't get converted
        Option::set(GeoIp2::SWITCH_TO_ISO_REGIONS_OPTION_NAME, 1);

        $this->setUpWebsitesAndGoals();

        $this->setMockLocationProvider();
        $this->trackVisits(9, false);

        $this->setLocationProvider('GeoIP2-City.mmdb');
        $this->trackVisits(2, true, $useLocal = false);
        $this->trackVisits(4, true, $useLocal = false, $doBulk = true);

        $this->setLocationProvider('GeoIP2-Country.mmdb');
        $this->trackVisits(2, true);

        $this->trackOtherVisits();

        $this->setLocationProvider('GeoIP2-City.mmdb');
    }

    public function tearDown(): void
    {
        $this->unsetLocationProvider();
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime, 0, "Site " . $this->idSite);
        }

        if (!self::goalExists($this->idSite, $idGoal = 1)) {
            $this->idGoal = API::getInstance()->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
        }

        if (!self::goalExists($this->idSite, $idGoal = 2)) {
            $this->idGoal2 = API::getInstance()->addGoal($this->idSite, 'two', 'url', 'xxxxxxxxxxxxx', 'contains', false, 5, false, 'twodesc');
        }
    }

    protected $calledCounter = 0;

    private function trackVisits($visitorCount, $setIp = false, $useLocal = true, $doBulk = false)
    {
        $this->calledCounter++;

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
            $this->trackVisit($t, $this->calledCounter, $i, $doBulk, array('setIp' => $setIp));
        }

        if ($doBulk) {
            self::checkBulkTrackingResponse($t->doBulkTrack());
        }
    }

    /**
     * Insert a new visit into the database.
     * @param \MatomoTracker $t          The tracker to record the visits on
     * @param int $fixtureCounter       Number of times this fixture has been run
     * @param int $visitorCounter       Visitor counter within this execution of the fixture
     * @param boolean $doBulk           Should this visit be left for bulk insert later, or processed now?
     * @param array $params             Other params as required to set up the visit
     */
    protected function trackVisit(\MatomoTracker $t, $fixtureCounter, $visitorCounter, $doBulk, array $params)
    {
        $setIp = isset($params['setIp']) && $params['setIp'];

        // NOTE: floor() is so some visits share the same visit ID
        $t->setVisitorId(substr(md5(floor($visitorCounter / 2) + $fixtureCounter * 1000), 0, $t::LENGTH_VISITOR_ID));

        $userAgent = null;
        if ($setIp) {
            $userAgent = current($this->userAgents);

            $t->setIp(current($this->ips));
            $t->setUserAgent($userAgent);
            next($this->userAgents);
            next($this->ips);
        } else {
            $t->setIp("1.2.4.$visitorCounter");
        }

        // first visit
        $date = Date::factory($this->dateTime)->addDay($visitorCounter);
        $t->setForceVisitDateTime($date->getDatetime());
        $t->setUrl("http://piwik.net/grue/lair");
        $t->setCustomVariable(1, 'Cvar 1 name', 'Cvar1 value is ' . $visitorCounter, 'visit');
        $t->setCustomVariable(5, 'Cvar 5 name', 'Cvar5 value is ' . $visitorCounter, 'visit');
        $t->setCustomVariable(2, 'Cvar 2 PAGE name', 'Cvar2 PAGE value is ' . $visitorCounter, 'page');
        $t->setCustomVariable(5, 'Cvar 5 PAGE name', 'Cvar5 PAGE value is ' . $visitorCounter, 'page');

        $r = $t->doTrackPageView('It\'s <script> pitch black...');
        if (!$doBulk) {
            self::checkResponse($r);
        }

        // second visit
        if ($userAgent) {
            $t->setUserAgent($userAgent); // unset in doTrack...
        }

        $date = $date->addHour(1);
        $t->setForceVisitDateTime($date->getDatetime());
        $t->setUrl("http://piwik.net/space/quest/iv");

        // Manually record some data
        $r = $t->doTrackPageView("Space Quest XII");

        if (!$doBulk) {
            self::checkResponse($r);
        }

        if ($userAgent) {
            $t->setUserAgent($userAgent); // unset in doTrack...
        }

        // Track site search (for AutoSuggestAPI test)
        // Only for half visitors so they don't all have a "site search" as last action and some of them have a standard page view as last action
        $date = $date->addHour(0.1);
        $t->setForceVisitDateTime($date->getDatetime());
        if(($visitorCounter % 2) == 0) {
            $r = $t->doTrackSiteSearch('Bring on the party', 'CAT', $visitorCounter * 6);
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
        $t->doTrackAction('http://example.org/path/file' . $visitorCounter . '.zip', "download");
        if (!$doBulk) {
            self::checkResponse($r);
        }

        $date = $date->addHour(0.05);
        $t->setForceVisitDateTime($date->getDatetime());
        $r = $t->doTrackAction('http://example-outlink.org/' . $visitorCounter . '.html', "link");
        if (!$doBulk) {
            self::checkResponse($r);
        }

        $date = $date->addHour(0.05);
        $t->setForceVisitDateTime($date->getDatetime());
        $r = $t->doTrackEvent(
            'Cat' . $visitorCounter,
            'Action' . $visitorCounter,
            'Name' . $visitorCounter,
            345.678 + $visitorCounter
        );
        if (!$doBulk) {
            self::checkResponse($r);
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
        GeoIp2::$dbNames['loc'] = array($file);
        LocationProvider::$providers = null;
        LocationProvider::setCurrentProvider(self::GEOIP_IMPL_TO_TEST);

        if (LocationProvider::getCurrentProviderId() !== self::GEOIP_IMPL_TO_TEST) {
            throw new Exception("Failed to set the current location provider to '" . self::GEOIP_IMPL_TO_TEST . "'.");
        }

        $possibleFiles = GeoIp2::$dbNames['loc'];
        if (GeoIp2::getPathToGeoIpDatabase($possibleFiles) === false) {
            throw new Exception("The GeoIP2 location provider cannot find the '$file' file! Tests will fail.");
        }
    }

    private function setMockLocationProvider()
    {
        LocationProvider::$providers = array();
        LocationProvider::$providers[] = new MockLocationProvider();
        LocationProvider::setCurrentProvider('mock_provider');
        MockLocationProvider::$locations = array(
            self::makeLocation('Stratford-upon-Avon', 'WAR', 'gb', 123.456, 21.321), // template location

            // same region, different city, same country
            self::makeLocation('Nuneaton and Bedworth', 'WAR', 'gb', $isp = 'comcast.net'),

            // same region, city & country (different lat/long)
            self::makeLocation('Stratford-upon-Avon', 'WAR', 'gb', 124.456, 22.231, $isp = 'comcast.net'),

            // same country, different region & city
            self::makeLocation('London', 'LND', 'gb'),

            // same country, different region, same city
            self::makeLocation('Stratford-upon-Avon', 'KEN', 'gb', $lat = null, $long = null, $isp = 'awesomeisp.com'),

            // different country, diff region, same city
            self::makeLocation('Stratford-upon-Avon', 'SPE', 'ru'),

            // different country, diff region (same as last), different city
            self::makeLocation('Hluboká nad Vltavou', 'SPE', 'ru'),

            // different country, diff region (same as last), same city
            self::makeLocation('Stratford-upon-Avon', '18', 'mk'),

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
