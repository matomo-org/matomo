<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
    public static $fixture = null; // initialized below class definition

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

        $apiToTest   = array();
        $apiToTest[] = array(
            $api,
            array(
                'idSite'     => 1,
                'testSuffix' => ''
            )
        );
        $apiToTest[] = array(
            $api,
            array(
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
                'idSite'     => 1,
                'testSuffix' => 'descSortOrder',
                'otherRequestParameters' => array(
                    'filter_sort_order' => 'desc',
                )
            )
        );
        $apiToTest[] = array(
            $api,
            array(
                'idSite'     => 1,
                'testSuffix' => 'sortByVisitsNumber',
                'otherRequestParameters' => array(
                    'filter_sort_order' => 'desc',
                    'filter_sort_column' => 'total_visits',
                )
            )
        );
        $apiToTest[] = array(
            $api,
            array(
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