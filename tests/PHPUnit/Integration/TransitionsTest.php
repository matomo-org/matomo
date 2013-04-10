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
     * @group        Transitions
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
            'otherRequestParameters' => array(
                'pageUrl'             => 'http://example.org/page/one.html',
                'limitBeforeGrouping' => 2
            )
        ));
        return $return;
    }

    public function getOutputPrefix()
    {
        return 'Transitions';
    }
}

Test_Piwik_Integration_Transitions::$fixture = new Test_Piwik_Fixture_SomeVisitsManyPageviewsWithTransitions();

