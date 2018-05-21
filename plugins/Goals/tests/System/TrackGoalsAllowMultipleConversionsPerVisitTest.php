<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\tests\System;

use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\SomeVisitsAllConversions;

/**
 * Tests API methods with goals that do and don't allow multiple
 * conversions per visit.
 *
 * @group TrackGoalsAllowMultipleConversionsPerVisitTest
 * @group Plugins
 */
class TrackGoalsAllowMultipleConversionsPerVisitTest extends SystemTestCase
{
    public static $fixture = null;

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    /**
     * @depends      testApi
     */
    public function testCheck()
    {
        $idSite = self::$fixture->idSite;

        // test delete is working as expected
        $goals = API::getInstance()->getGoals($idSite);
        $this->assertTrue(5 == count($goals));
        API::getInstance()->deleteGoal($idSite, self::$fixture->idGoal_OneConversionPerVisit);
        API::getInstance()->deleteGoal($idSite, self::$fixture->idGoal_MultipleConversionPerVisit);
        $goals = API::getInstance()->getGoals($idSite);
        $this->assertTrue(3 == count($goals));
    }

    public function getApiForTesting()
    {
        $apiToCall = array(
            'VisitTime.getVisitInformationPerServerTime',
            'VisitsSummary.get',
            'Goals.get'
        );

        return array(
            array($apiToCall, array('idSite' => self::$fixture->idSite, 'date' => self::$fixture->dateTime)),
            array(array('Goals.get'), array(
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'segment' => 'pageUrl=@/',
                'testSuffix' => '_withLogLinkVisitActionSegment'
            )),

        );
    }

    public static function getOutputPrefix()
    {
        return 'trackGoals_allowMultipleConversionsPerVisit';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

TrackGoalsAllowMultipleConversionsPerVisitTest::$fixture = new SomeVisitsAllConversions();