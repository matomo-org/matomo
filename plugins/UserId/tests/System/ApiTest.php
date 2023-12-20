<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId\tests\System;

use Piwik\Plugins\UserId\tests\Fixtures\TrackFewVisitsAndCreateUsers;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Run system tests against the UserId.getUsers API method
 *
 * @group UserId
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var TrackFewVisitsAndCreateUsers
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
        $api = 'UserId.getUsers';
        $startDate = substr(self::$fixture->dateTime, 0, 10);
        $endDate = date('Y-m-d', strtotime($startDate) + 3600 * 24 * 365);

        $apiToTest   = array();
        $apiToTest[] = array(
            $api,
            array(
                'date' => $startDate,
                'periods' => array('day'),
                'idSite'     => 1,
                'testSuffix' => ''
            )
        );
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate,$endDate",
                'periods' => array('range'),
                'idSite'     => 1,
                'testSuffix' => ''
            )
        );

        $apiToTest[] = array(
            $api,
            array(
                'date' => $startDate,
                'periods' => array('day'),
                'idSite'     => 1,
                'testSuffix' => 'limit',
                'otherRequestParameters' => array(
                    'filter_limit' => '2',
                    'filter_offset' => '1',
                )
            )
        );
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate,$endDate",
                'periods' => array('range'),
                'idSite'     => 1,
                'testSuffix' => 'limit',
                'otherRequestParameters' => array(
                    'filter_limit' => '2',
                    'filter_offset' => '1',
                )
            )
        );

        $apiToTest[] = array(
            $api,
            array(
                'date' => $startDate,
                'periods' => array('day'),
                'idSite'     => 1,
                'testSuffix' => 'ascSortOrder',
                'otherRequestParameters' => array(
                    'filter_sort_order' => 'asc',
                )
            )
        );
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate,$endDate",
                'periods' => array('range'),
                'idSite'     => 1,
                'testSuffix' => 'ascSortOrder',
                'otherRequestParameters' => array(
                    'filter_sort_order' => 'asc',
                )
            )
        );

        $apiToTest[] = array(
            $api,
            array(
                'date' => $startDate,
                'periods' => array('day'),
                'idSite'     => 1,
                'testSuffix' => 'searchByUserId',
                'otherRequestParameters' => array(
                    'filter_pattern' => 'user2'
                )
            )
        );
        $apiToTest[] = array(
            $api,
            array(
                'date' => "$startDate,$endDate",
                'periods' => array('range'),
                'idSite'     => 1,
                'testSuffix' => 'searchByUserId',
                'otherRequestParameters' => array(
                    'filter_pattern' => 'user2'
                )
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

ApiTest::$fixture = new TrackFewVisitsAndCreateUsers();
