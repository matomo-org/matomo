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
            'Events.getCategory',
            'Events.getAction',
            'Events.getName',
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

        $apiToCallProcessedReportMetadata = $this->getApiToCall();

        $dayPeriod = 'day';
        $periods = array($dayPeriod, 'month');

        $apiEventAndAction = array('Events', 'Actions.getPageUrls');
        $result = array(
            array($apiToCallProcessedReportMetadata, array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $periods,
                'setDateLastN' => false,
                'testSuffix'   => '')),

            array($apiEventAndAction, array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $dayPeriod,
                'segment'      => "eventCategory==Movie,eventName==".urlencode('La fiancée de l\'eau'),
                'setDateLastN' => false,
                'testSuffix'   => '_eventCategoryOrNameMatch')
            ),

            // eventAction should not match any page view
            array($apiEventAndAction, array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $dayPeriod,
                'segment'      => "eventAction=@play",
                'setDateLastN' => false,
                'testSuffix'   => '_segmentMatchesEventActionPlay')
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

        $apiToCallProcessedReportMetadata = array(
            'Events.getCategory',
            'Events.getAction',
            'Events.getName',
        );
        // testing metadata API for Events reports
        foreach ($apiToCallProcessedReportMetadata as $api) {
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

        // Test secondary dimensions
        $secondaryDimensions = array('eventCategory', 'eventAction', 'eventName');
        foreach($secondaryDimensions as $secondaryDimension) {
            $result[] = array(array('Events'), array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $periods,
                'otherRequestParameters' => array(
                    'secondaryDimension' => $secondaryDimension
                ),
                'setDateLastN' => false,
                'testSuffix'   => '_secondaryDimensionIs' . ucfirst($secondaryDimension))
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


