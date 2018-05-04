<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Transitions\tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\SomeVisitsManyPageviewsWithTransitions;

/**
 * Tests the transitions plugin.
 *
 * @group TransitionsTest
 * @group Plugins
 */
class TransitionsTest extends SystemTestCase
{
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
        $return = array();
        $return[] = array('Transitions.getTransitionsForPageUrl', array(
            'idSite'                 => self::$fixture->idSite,
            'date'                   => self::$fixture->dateTime,
            'periods'                => array('day', 'month'),
            'testSuffix'             => '_noLimit',
            'otherRequestParameters' => array(
                'pageUrl'            => 'http://example.org/page/one.html',
            )
        ));
        $return[] = array('Transitions.getTransitionsForPageTitle', array(
            'idSite'                 => self::$fixture->idSite,
            'date'                   => self::$fixture->dateTime,
            'periods'                => array('day', 'month'),
            'testSuffix'             => '_noLimit',
            'otherRequestParameters' => array(
                'pageTitle'          => 'page title - page/one.html',
            )
        ));

        // test w/ pages that don't exist
        $return[] = array('Transitions.getTransitionsForPageUrl', array(
            'idSite'                 => self::$fixture->idSite,
            'date'                   => self::$fixture->dateTime,
            'periods'                => array('day', 'month'),
            'testSuffix'             => '_noData',
            'otherRequestParameters' => array(
                'pageUrl'            => 'http://example.org/not/a/page.html',
            )
        ));
        $return[] = array('Transitions.getTransitionsForPageTitle', array(
            'idSite'                 => self::$fixture->idSite,
            'date'                   => self::$fixture->dateTime,
            'periods'                => array('day', 'month'),
            'testSuffix'             => '_noData',
            'otherRequestParameters' => array(
                'pageTitle'          => 'not a page title',
            )
        ));

        $return[] = array('Transitions.getTransitionsForPageUrl', array( // test w/ limiting
            'idSite'                 => self::$fixture->idSite,
            'date'                   => self::$fixture->dateTime,
            'periods'                => array('day', 'month'),
            'otherRequestParameters' => array(
                'pageUrl'             => 'http://example.org/page/one.html',
                'limitBeforeGrouping' => 2
            )
        ));

        $return[] = array('Transitions.getTransitionsForPageUrl', array( // test w/ segment
            'idSite'                 => self::$fixture->idSite,
            'date'                   => self::$fixture->dateTime,
            'periods'                => array('day'),
            'testSuffix'             => '_withSegment',
            'segment'                => 'visitConvertedGoalId!%3D2',
            'otherRequestParameters' => array(
                'pageUrl'             => 'http://example.org/page/one.html',
                'limitBeforeGrouping' => 2
            )
        ));
        $return[] = array('Transitions.getTransitionsForPageTitle', array(
            'idSite'                 => self::$fixture->idSite,
            'date'                   => self::$fixture->dateTime,
            'periods'                => array('day'),
            'testSuffix'             => '_withSegment',
            'otherRequestParameters' => array(
                'pageTitle'          => 'page title - page/one.html',
            )
        ));
        return $return;
    }

    public static function getOutputPrefix()
    {
        return 'Transitions';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

TransitionsTest::$fixture = new SomeVisitsManyPageviewsWithTransitions();