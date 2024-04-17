<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountry\tests\System;

use Piwik\Plugins\UserCountry\tests\Fixtures\ManySitesManyVisitsWithGeoIp;
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
                'testSuffix' => ''
            )
        );
        // multi period
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate,$endDate",
                'periods' => array('day'),
                'idSite'     => 1,
                'testSuffix' => 'multi_period'
            )
        );
        // multi sites
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate",
                'periods' => array('month'),
                'idSite'     => 'all',
                'testSuffix' => 'multi_sites'
            )
        );
        // multi sites & multi period
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate,$endDate",
                'periods' => array('day'),
                'idSite'     => 'all',
                'testSuffix' => 'multi_periods_and_sites'
            )
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
}

ApiTest::$fixture = new ManySitesManyVisitsWithGeoIp();
