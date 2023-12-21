<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId\tests\System;

use Piwik\Plugins\UserId\tests\Fixtures\OverwriteUserIdFixture;
use Piwik\Plugins\UserId\tests\Fixtures\TrackFewVisitsAndCreateUsers;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group UserId
 * @group OverwriteUserIdTest
 * @group Plugins
 */
class OverwriteUserIdTest extends SystemTestCase
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

        // we expext to always see 1 action only per visitor as visitorId changes every time ...
        // we also expect a new visit to be created even though userId stays the same
        $apiToTest[] = array(
            'Live.getLastVisitsDetails',
            array(
                'date' => "$startDate,$endDate",
                'periods' => array('range'),
                'idSite'     => 1,
                'testSuffix' => '',
                'otherRequestParameters' => array('doNotFetchActions' => '1', 'showColumns' => 'idVisit,userId,visitIp,actions'),
            )
        );

        return $apiToTest;
    }

    public static function getOutputPrefix()
    {
        return 'overwriteUserId';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

OverwriteUserIdTest::$fixture = new OverwriteUserIdFixture();
