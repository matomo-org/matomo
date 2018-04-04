<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\tests\Integration;

use PHPUnit_Framework_MockObject_MockObject;
use Piwik\Common;
use Piwik\Db;
use Piwik\Network\IPUtils;
use Piwik\Plugins\UserCountry\VisitorGeolocator;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tests\Framework\TestDataHelper\LogHelper;
use Piwik\Tracker\Cache;
use Piwik\Tracker\Visit;
use Piwik\Tests\Framework\Mock\LocationProvider as MockLocationProvider;

require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Framework/Mock/LocationProvider.php';

/**
 * @group UserCountry
 */
class VisitorGeolocatorTest extends IntegrationTestCase
{
    const TEST_IP = '1.2.3.4';

    /**
     * @var LogHelper
     */
    private $logInserter;

    public function setUp()
    {
        parent::setUp();

        $this->logInserter = new LogHelper();

        // ensure all providers are loaded and add mock provider
        LocationProvider::$providers = null;
        $providers = LocationProvider::getAllProviders();
        LocationProvider::$providers[] = new MockLocationProvider();
    }

    public function test_getLocation_shouldReturnLocationForProvider_IfLocationIsSetForCurrentProvider()
    {
        $location = array(
            'city' => 'Wroclaw',
            'country_code' => 'pl'
        );

        $provider = $this->getProviderMock();
        $provider->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($location));

        $geolocator = new VisitorGeolocator($provider);

        $this->assertEquals(
            $location,
            $geolocator->getLocation(array('ip' => '127.0.0.1'))
        );
    }

    public function test_getLocation_shouldReturnLocationForProvider_IfLocationCountryCodeIsNotSetShouldSetAsxx()
    {
        $location = array(
            'city' => 'Wroclaw'
        );

        $provider = $this->getProviderMock();
        $provider->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($location));

        $geolocator = new VisitorGeolocator($provider);

        $this->assertEquals(
            array_merge(
                $location,
                array(
                    'country_code' => Visit::UNKNOWN_CODE
                )
            ),
            $geolocator->getLocation(array('ip' => '127.0.0.2'))
        );
    }

    public function test_getLocation_shouldReturnLocationForProviderAndReadFromCacheIfIPIsNotChanged()
    {
        $locations = array(
            'pl' => array(
                'city' => 'Wroclaw',
                'country_code' => 'pl'
            ),

            'nz' => array(
                'city' => 'Wellington',
                'country_code' => 'nz'
            ),
        );

        $poland = $this->getProviderMock();
        $poland->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($locations['pl']));

        $geolocator = new VisitorGeolocator($poland);
        $geolocator->getLocation(array('ip' => '10.0.0.1'));

        $nz = $this->getProviderMock();
        $nz->expects($this->once())
            ->method('getLocation')
            ->will($this->returnValue($locations['nz']));

        $geolocator = new VisitorGeolocator($nz);
        $geolocator->getLocation(array('ip' => '10.0.0.2'));

        $this->assertEquals(
            $locations,
            array(
                'pl' => $geolocator->getLocation(array('ip' => '10.0.0.1')),
                'nz' => $geolocator->getLocation(array('ip' => '10.0.0.2'))
            )
        );
    }

    public function test_get_shouldReturnDefaultProvider_IfCurrentProviderReturnFalse()
    {
        Cache::setCacheGeneral(array('currentLocationProviderId' => 'nonexistant'));
        $geolocator = new VisitorGeolocator();

        $this->assertEquals(LocationProvider\DefaultProvider::ID, $geolocator->getProvider()->getId());
    }

    public function test_get_shouldReturnCurrentProvider_IfCurrentProviderIsSet()
    {
        Cache::setCacheGeneral(array('currentLocationProviderId' => MockLocationProvider::ID));
        $geolocator = new VisitorGeolocator();

        $this->assertEquals(MockLocationProvider::ID, $geolocator->getProvider()->getId());
    }

    public function getDataForAttributeExistingVisitTests()
    {
        $basicTestLocation = array(
            LocationProvider::COUNTRY_CODE_KEY => 'US',
            LocationProvider::REGION_CODE_KEY => 'rg',
            LocationProvider::CITY_NAME_KEY => 'the city',
            LocationProvider::LATITUDE_KEY => '29.959698',
            LocationProvider::LONGITUDE_KEY => '-90.064880'
        );

        // note: floating point values should be used for expected properties so floating point comparison is done
        // by PHPUnit
        $basicExpectedVisitProperties = array(
            'location_country' => 'us',
            'location_region' => 'rg',
            'location_city' => 'the city',
            'location_latitude' => 29.959698,
            'location_longitude' => -90.064880
        );

        return array(
            array( // test normal re-attribution
                $basicTestLocation,

                $basicExpectedVisitProperties
            ),

            array( // test w/ garbage in location provider result
                array(
                    LocationProvider::COUNTRY_CODE_KEY => 'US',
                    'garbage' => 'field',
                    LocationProvider::REGION_CODE_KEY => 'rg',
                    LocationProvider::CITY_NAME_KEY => 'the city',
                    LocationProvider::LATITUDE_KEY => '29.959698',
                    LocationProvider::LONGITUDE_KEY => '-90.064880',
                    'another' => 'garbage field'
                ),

                array(
                    'location_country' => 'us',
                    'location_region' => 'rg',
                    'location_city' => 'the city',
                    'location_latitude' => 29.959698,
                    'location_longitude' => -90.064880
                )
            ),

            array( // test when visit has some correct properties already
                $basicTestLocation,

                $basicExpectedVisitProperties,

                array(
                    'location_country' => 'US',
                    'location_region' => 'rg',
                    'location_city' => 'the city'
                ),

                array(
                    'location_country' => 'us',
                    'location_latitude' => 29.959698,
                    'location_longitude' => -90.064880
                )
            ),

            array( // test when visit has all correct properties already
                $basicTestLocation,

                $basicExpectedVisitProperties,

                $basicExpectedVisitProperties,

                array()
            )
        );
    }

    /**
     * @dataProvider getDataForAttributeExistingVisitTests
     */
    public function test_attributeExistingVisit_CorrectlySetsLocationProperties_AndReturnsCorrectResult(
        $mockLocation, $expectedVisitProperties, $visitProperties = array(), $expectedUpdateValues = null)
    {
        $mockLocationProvider = $this->getProviderMockThatGeolocates($mockLocation);

        $visit = $this->insertVisit($visitProperties);
        $this->insertTwoConversions($visit);

        $geolocator = new VisitorGeolocator($mockLocationProvider);
        $valuesUpdated = $geolocator->attributeExistingVisit($visit, $useCache = false);

        $this->assertEquals($expectedVisitProperties, $this->logInserter->getVisit($visit['idvisit']), $message = '', $delta = 0.001);

        $expectedUpdateValues = $expectedUpdateValues === null ? $expectedVisitProperties : $expectedUpdateValues;
        $this->assertEquals($expectedUpdateValues, $valuesUpdated, $message = '', $delta = 0.001);

        $conversions = $this->getConversions($visit);
        $this->assertEquals(array($expectedVisitProperties, $expectedVisitProperties), $conversions, $message = '', $delta = 0.001);
    }

    public function test_attributeExistingVisit_ReturnsNull_AndSkipsAttribution_IfIdVisitMissingFromInput()
    {
        $mockLocationProvider = $this->getProviderMock();
        $geolocator = new VisitorGeolocator($mockLocationProvider);

        $result = $geolocator->attributeExistingVisit(array());

        $this->assertNull($result);
    }

    public function test_attributeExistingVisit_ReturnsNull_AndSkipsAttribution_IfIdVisitPresent_AndLocationIpMissingFromInput()
    {
        $mockLocationProvider = $this->getProviderMock();
        $geolocator = new VisitorGeolocator($mockLocationProvider);

        $result = $geolocator->attributeExistingVisit(array('idvisit' => 1));

        $this->assertNull($result);
    }

    public function test_reattributeVisitLogs_ReattributesVisitsInDateRangeAndFromSite_AndCallsCallbackWithEveryProcessedRow()
    {
        foreach (array(1, 2) as $idSite) {
            foreach (array('2012-01-01', '2012-01-02', '2012-01-03', '2012-01-04') as $date) {
                $this->insertVisit(array(
                    'visit_last_action_time' => $date,
                    'idsite' => $idSite
                ));
            }
        }

        $mockLocationProvider = $this->getProviderMockThatGeolocates(array(
            'location_country' => 'US',
            'location_region' => 'rg',
            'location_city' => 'the city'
        ));
        $geolocator = new VisitorGeolocator($mockLocationProvider);

        $reattributedVisits = array();
        $geolocator->reattributeVisitLogs('2012-01-02', '2012-01-04', 2, $segmentLimit = 1000, function ($row) use (&$reattributedVisits) {
            $reattributedVisits[] = $row['idvisit'];
        });

        sort($reattributedVisits);

        $expectedVisitsVisited = array(6, 7);
        $this->assertEquals($expectedVisitsVisited, $reattributedVisits);

        // check that no visits were re-attributed for site 1
        $actualVisits = Db::fetchAll("SELECT visit_last_action_time, idsite, location_country, location_region, location_city FROM "
            . Common::prefixTable('log_visit') . " ORDER BY idsite ASC, visit_last_action_time ASC");
        $expectedVisits = array(
            array(
                'visit_last_action_time' => '2012-01-01 00:00:00',
                'idsite' => '1',
                'location_country' => 'xx',
                'location_region' => null,
                'location_city' => null
            ),
            array(
                'visit_last_action_time' => '2012-01-02 00:00:00',
                'idsite' => '1',
                'location_country' => 'xx',
                'location_region' => null,
                'location_city' => null
            ),
            array(
                'visit_last_action_time' => '2012-01-03 00:00:00',
                'idsite' => '1',
                'location_country' => 'xx',
                'location_region' => null,
                'location_city' => null
            ),
            array(
                'visit_last_action_time' => '2012-01-04 00:00:00',
                'idsite' => '1',
                'location_country' => 'xx',
                'location_region' => null,
                'location_city' => null
            ),
            array(
                'visit_last_action_time' => '2012-01-01 00:00:00',
                'idsite' => '2',
                'location_country' => 'xx',
                'location_region' => null,
                'location_city' => null
            ),
            array(
                'visit_last_action_time' => '2012-01-02 00:00:00',
                'idsite' => '2',
                'location_country' => 'us',
                'location_region' => 'rg',
                'location_city' => 'the city'
            ),
            array(
                'visit_last_action_time' => '2012-01-03 00:00:00',
                'idsite' => '2',
                'location_country' => 'us',
                'location_region' => 'rg',
                'location_city' => 'the city'
            ),
            array(
                'visit_last_action_time' => '2012-01-04 00:00:00',
                'idsite' => '2',
                'location_country' => 'xx',
                'location_region' => null,
                'location_city' => null
            ),
        );

        $this->assertEquals($expectedVisits, $actualVisits);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|LocationProvider
     */
    protected function getProviderMock()
    {
        return $this->getMockBuilder('\Piwik\Plugins\UserCountry\LocationProvider')
            ->setMethods(array('getId', 'getLocation', 'getInfo', 'isAvailable', 'isWorking', 'getSupportedLocationInfo'))
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getProviderMockThatGeolocates($locationResult)
    {
        $mock = $this->getProviderMock();
        $mock->expects($this->any())->method('getLocation')->will($this->returnCallback(function ($info) use ($locationResult) {
            if ($info['ip'] == VisitorGeolocatorTest::TEST_IP) {
                return $locationResult;
            } else {
                return null;
            }
        }));
        return $mock;
    }

    private function insertVisit($visit = array())
    {
        $defaultProperties = array(
            'location_ip' => IPUtils::stringToBinaryIP(self::TEST_IP)
        );

        return $this->logInserter->insertVisit(array_merge($defaultProperties, $visit));
    }

    private function insertTwoConversions($visit)
    {
        $conversionProperties = array(
            'idsite' => $visit['idsite'],
            'idvisitor' => $visit['idvisitor'],
            'location_longitude' => $visit['location_longitude'],
            'location_latitude' => $visit['location_latitude'],
            'location_region' => $visit['location_region'],
            'location_country' => $visit['location_country'],
            'location_city' => $visit['location_city'],
            'visitor_count_visits' => $visit['visitor_count_visits'],
            'visitor_returning' => $visit['visitor_returning'],
        );

        $this->logInserter->insertConversion($visit['idvisit'], $conversionProperties);

        $conversionProperties['buster'] = 2;
        $this->logInserter->insertConversion($visit['idvisit'], $conversionProperties);
    }

    private function getConversions($visit)
    {
        return Db::fetchAll("SELECT location_country, location_region, location_city, location_latitude, location_longitude
                               FROM " . Common::prefixTable('log_conversion') . " WHERE idvisit = ?", array($visit['idvisit']));
    }
}