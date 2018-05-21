<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Goals\tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\TwoVisitsWithCustomVariables;

/**
 * @group Plugins
 * @group TrackGoalsOneConversionPerVisitTest
 */
class TrackGoalsOneConversionPerVisitTest extends SystemTestCase
{
    /**
     * @var TwoVisitsWithCustomVariables
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
        $apiToCall = array('Goals.get');

        return array(
            array($apiToCall, array(
                'idSite'       => self::$fixture->idSite,
                'date'         => self::$fixture->dateTime,
                'periods'      => array('day'))),
            // test for https://github.com/piwik/piwik/issues/9194 requesting log_conversion with log_link_visit_action segment
            array($apiToCall, array(
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'segment' => 'pageUrl=@/',
                'testSuffix' => '_withLogLinkVisitActionSegment'
            )),
            array($apiToCall, array(
                'idSite' => self::$fixture->idSite,
                'date' => self::$fixture->dateTime,
                'segment' => 'visitCount>=1;pageUrl=@/',
                'testSuffix' => '_withLogLinkVisitActionAndLogVisitSegment'
            )),
        );
    }

    public static function getOutputPrefix()
    {
        return 'trackGoals_oneConversionPerVisit';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

TrackGoalsOneConversionPerVisitTest::$fixture = new TwoVisitsWithCustomVariables();
TrackGoalsOneConversionPerVisitTest::$fixture->doExtraQuoteTests = false;