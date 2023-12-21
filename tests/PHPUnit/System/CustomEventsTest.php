<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\ThreeVisitsWithCustomEvents;

/**
 * Testing Custom Events
 *
 * @group CustomEventsTest
 * @group Plugins
 */
class CustomEventsTest extends SystemTestCase
{
    /**
     * @var ThreeVisitsWithCustomEvents
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $params['xmlFieldsToRemove'] = array('idsubdatatable');
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

    public function tearDown(): void
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
                'segment'      => "eventCategory==Movie,eventName==" . urlencode('La fiancée de l\'eau'),
                'setDateLastN' => false,
                'testSuffix'   => '_eventCategoryOrNameMatch')
            ),

            array($apiEventAndAction, array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $dayPeriod,
                'segment'      => "eventAction==rating;eventValue>9",
                'setDateLastN' => false,
                'testSuffix'   => '_eventValueMatch')
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

            // Goals and events
            array('Goals.get', array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $dayPeriod,
                'idGoal'       => ThreeVisitsWithCustomEvents::$idGoalTriggeredOnEventCategory,
                'setDateLastN' => false)
            )

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
            $result[] = array(
                'API.getProcessedReport', array('idSite'       => $idSite1,
                                                'date'         => $dateTime,
                                                'periods'      => $dayPeriod,
                                                'setDateLastN' => true,
                                                'apiModule'    => $apiModule,
                                                'apiAction'    => $apiAction,
                                                'otherRequestParameters' => array('flat' => '1'),
                                                'testSuffix'   => '_' . $api . '_flat')
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

CustomEventsTest::$fixture = new ThreeVisitsWithCustomEvents();
