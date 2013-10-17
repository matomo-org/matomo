<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Tests w/ two visits & custom variables.
 */
class Test_Piwik_Integration_TwoVisitsWithCustomVariables extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $dateTime = self::$fixture->dateTime;

        $apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

        $return = array(
            array($apiToCall, array('idSite'       => 'all',
                                    'date'         => $dateTime,
                                    'periods'      => array('day', 'week'),
                                    'setDateLastN' => true)),

            // test getProcessedReport w/ custom variables subtable
            array('API.getProcessedReport', array('idSite'        => $idSite,
                                                  'date'          => $dateTime,
                                                  'periods'       => 'day',
                                                  'apiModule'     => 'CustomVariables',
                                                  'apiAction'     => 'getCustomVariablesValuesFromNameId',
                                                  'supertableApi' => 'CustomVariables.getCustomVariables',
                                                  'testSuffix'    => '__subtable')),
        );

        return $return;
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public static function getOutputPrefix()
    {
        return 'twoVisitsWithCustomVariables';
    }
}

Test_Piwik_Integration_TwoVisitsWithCustomVariables::$fixture = new Test_Piwik_Fixture_TwoVisitsWithCustomVariables();

