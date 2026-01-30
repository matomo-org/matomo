<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\System;

use DateTime;
use Piwik\Plugins\UserCountry\tests\Fixtures\ManySitesManyVisitsWithGeoIp;
use Piwik\API\Request;
use Piwik\DataTable;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group UserCountry2
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var ManySitesManyVisitsWithGeoIp
     */
    public static $fixture;

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $api = [
            'UserCountry.getCountry',
            'UserCountry.getContinent',
            'UserCountry.getRegion',
            'UserCountry.getCity',
        ];
        $startDate = substr(self::$fixture->dateTime, 0, 10);
        $endDate = date('Y-m-d', strtotime($startDate) + 3600 * 24 * 2);

        $apiToTest   = array();
        // single period
        $apiToTest[] = array(
            $api,
            array(
                'date' => $startDate,
                'periods' => array('day'),
                'idSite'     => 1,
                'testSuffix' => '',
            ),
        );
        // multi period
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate,$endDate",
                'periods' => array('day'),
                'idSite'     => 1,
                'testSuffix' => 'multi_period',
            ),
        );
        // multi sites
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate",
                'periods' => array('month'),
                'idSite'     => 'all',
                'testSuffix' => 'multi_sites',
            ),
        );
        // multi sites & multi period
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate,$endDate",
                'periods' => array('day'),
                'idSite'     => 'all',
                'testSuffix' => 'multi_periods_and_sites',
            ),
        );

        return $apiToTest;
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

    public function testGetCountry()
    {
        $dateTime = '2026-01-01 00:00:00';
        $idSite = static::$fixture::createWebsite(
            $dateTime,
            $ecommerce = 1
        );

        $countries = [
            'nz' => 'New Zealand',
            'it' => 'Italy',
            'us' => 'United States',
            'au' => 'Australia',
        ];

        $this->createManyEcommerceOrders($idSite, $dateTime, 400, array_keys($countries));

        $resultNoSegments = Request::processRequest('UserCountry.getCountry', [
            'idSite' => $idSite,
            'period' => 'year',
            'date' => $dateTime,
            'flat' => '1',
        ]);

        foreach ($countries as $name) {

            /** @var DataTable */
            $resultWithSegment = Request::processRequest('UserCountry.getCountry', [
                'idSite' => $idSite,
                'period' => 'year',
                'date' => $dateTime,
                'flat' => '1',
                'segment' => "countryName==$name",
            ]);

            $rowNoSegments = $resultNoSegments->getRowFromLabel($name)->getColumns();
            $rowWithSegment = $resultWithSegment->getRowFromLabel($name)->getColumns();

            $conversionsNoSegments = $rowNoSegments['nb_conversions'];
            $conversionsWithSegment = $rowWithSegment['nb_conversions'];

            $this->assertTrue($conversionsNoSegments == $conversionsWithSegment, "conversions for country $name are not equal: $conversionsNoSegments vs $conversionsWithSegment");
        }
    }

    public function createManyEcommerceOrders($siteId, $dateTime, $numberOfOrders, $countries)
    {
        static::$fixture::createSuperUser($removeExisting = true);

        $visitorIds = [
            'visit-1',
            'visit-2',
            'visit-3',
        ];

        $orderNumber = 1001;

        $dateTimeObj = new DateTime($dateTime);
        $interval = new \DateInterval('P1D');

        for ($i = 0; $i < $numberOfOrders; $i++) {
            for ($j = 0; $j < count($countries); $j++) {
                $dateTimeStr = date_format($dateTimeObj, 'Y-m-d H:i:s');
                $tracker = static::$fixture::getTracker($siteId, $dateTimeStr, $defaultInit = true, $useLocal = true);
                $tracker->setCountry($countries[$j]);
                $tracker->setVisitorId(substr(md5($visitorIds[$i % 3]), $offset = 0, $tracker::LENGTH_VISITOR_ID));
                $tracker->setTokenAuth(static::$fixture::getTokenAuth());
                $orderNo = $orderNumber + ($i * 10) + $j;
                $tracker->addEcommerceItem('SKU-' . $orderNo, 'Product ' . $orderNo, 'Category', 100, 1);
                static::$fixture::checkResponse($tracker->doTrackEcommerceOrder($orderNo, 111.11, 100, 11));
                $dateTimeObj = date_add($dateTimeObj, $interval);
            }
        }
    }
}

ApiTest::$fixture = new ManySitesManyVisitsWithGeoIp();
