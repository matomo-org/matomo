<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\System;

use Piwik\Config;
use Piwik\Plugins\Live\tests\Fixtures\ManyVisitsOfSameVisitor;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Live
 * @group ApiTest
 * @group Api
 * @group Plugins
 */
class ApiTest extends SystemTestCase
{
    /**
     * @var ManyVisitsOfSameVisitor
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
        $api = array(
            'Live.getVisitorProfile',
        );

        $apiToTest   = array();
        $apiToTest[] = array($api,
            array(
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('day'),
                'testSuffix' => ''
            )
        );
        $apiToTest[] = array($api,
            array(
                'idSite'     => 1,
                'date'       => self::$fixture->dateTime,
                'periods'    => array('day'),
                'otherRequestParameters' => array('limitVisits' => 20),
                'testSuffix' => 'higherLimit'
            )
        );

        return $apiToTest;
    }

    public function testApiWithLowerMaxVisitsLimit()
    {
        Config::getInstance()->General['live_visitor_profile_max_visits_to_aggregate'] = 20;

        $this->runApiTests('Live.getVisitorProfile', array(
            'idSite'     => 1,
            'date'       => self::$fixture->dateTime,
            'periods'    => array('day'),
            'testSuffix' => 'maxVisitLimit'
        ));
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

ApiTest::$fixture = new ManyVisitsOfSameVisitor();