<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Testing Custom Events
 */
class Test_Piwik_Integration_CustomEvents extends IntegrationTestCase
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

    protected function getApiToCall()
    {
        return array(
            'Actions.get',
            'Live.getLastVisitsDetails',
            'Actions.getPageUrls',
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function getApiForTesting()
    {
        $dateTime = self::$fixture->dateTime;
        $idSite1 = self::$fixture->idSite;

        $apiToCall = $this->getApiToCall();

        $dayPeriod = 'day';
        $periods = array($dayPeriod, 'month');

        $result = array(
            array($apiToCall, array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $periods,
                'setDateLastN' => false,
                'testSuffix'   => '')),

            array('Actions.getPageUrls', array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $dayPeriod,
                'segment'      => "events>0",
                'setDateLastN' => false,
                'testSuffix'   => '')
            ),
            // FIXMEA: Add Events.get* here
            array('Actions.getPageUrls', array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $dayPeriod,
                'segment'      => "eventCategory==Movie,eventName==".urlencode('La fiancée de l\'eau'),
                'setDateLastN' => false,
                'testSuffix'   => '_eventCategoryOrNameMatch')
            ),

            // eventAction should not match any page view
            array('Actions.getPageUrls', array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $dayPeriod,
                'segment'      => "eventAction=@play",
                'setDateLastN' => false,
                'testSuffix'   => '_eventSegmentMatchNoAction')
            ),

            // eventValue should not match any page view
//            array('Actions.getPageUrls', array(
//                'idSite'       => $idSite1,
//                'date'         => $dateTime,
//                'periods'      => $dayPeriod,
//                'segment'      => "eventValue>0",
//                'setDateLastN' => false,
//                'testSuffix'   => '_eventSegmentMatchNoAction')
//            ),
        );

        // testing metadata API for one metadata report
        $apiToCall = array ( end($apiToCall) );

        foreach ($apiToCall as $api) {
            list($apiModule, $apiAction) = explode(".", $api);

            $result[] = array(
                'API.getProcessedReport', array('idSite'       => $idSite1,
                                                'date'         => $dateTime,
                                                'periods'      => $dayPeriod,
                                                'setDateLastN' => true,
                                                'apiModule'    => $apiModule,
                                                'apiAction'    => $apiAction,
                                                'testSuffix'   => '_' . $api . '_lastN')
            );
        }
        return $result;
    }

    public static function getOutputPrefix()
    {
        return 'CustomEvents';
    }
}

Test_Piwik_Integration_CustomEvents::$fixture = new Test_Piwik_Fixture_TwoVisitsWithCustomEvents();


