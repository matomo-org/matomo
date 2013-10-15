<?php

/**
 * Tests the transitions plugin.
 */
class Test_Piwik_Integration_Transitions extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
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
        return $return;
    }

    public static function getOutputPrefix()
    {
        return 'Transitions';
    }
}

Test_Piwik_Integration_Transitions::$fixture = new Test_Piwik_Fixture_SomeVisitsManyPageviewsWithTransitions();

