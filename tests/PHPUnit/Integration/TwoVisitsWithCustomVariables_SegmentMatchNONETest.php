<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Plugins\API\API;

/**
 * testing a segment containing all supported fields
 */
class Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchNONE extends IntegrationTestCase
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
        IntegrationTestCase::loadAllPlugins();

        $apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

        return array(
            array($apiToCall, array('idSite'       => 'all',
                                    'date'         => self::$fixture->dateTime,
                                    'periods'      => array('day', 'week'),
                                    'setDateLastN' => true,
                                    'segment'      => $this->getSegmentToTest()))
        );
    }

    public function getSegmentToTest()
    {
        // Segment matching NONE
        $segments = API::getInstance()->getSegmentsMetadata(self::$fixture->idSite);

        $minimumExpectedSegmentsCount = 55; // as of Piwik 1.12
        $this->assertTrue( count($segments) >= $minimumExpectedSegmentsCount);
        $segmentExpression = array();

        $seenVisitorId = false;
        foreach ($segments as $segment) {
            $value = 'campaign';
            if ($segment['segment'] == 'visitorId') {
                $seenVisitorId = true;
                $value = '34c31e04394bdc63';
            }
            if ($segment['segment'] == 'visitEcommerceStatus') {
                $value = 'none';
            }
            $matchNone = $segment['segment'] . '!=' . $value;

            // deviceType != campaign matches ALL visits, but we want to match None
            if($segment['segment'] == 'deviceType') {
                $matchNone = $segment['segment'] . '==car%20browser';
            }
            $segmentExpression[] = $matchNone;
        }

        $segment = implode(";", $segmentExpression);

        // just checking that this segment was tested (as it has the only visible to admin flag)
        $this->assertTrue($seenVisitorId);
        $this->assertGreaterThan(100, strlen($segment));

        return $segment;
    }

    public static function getOutputPrefix()
    {
        return 'twoVisitsWithCustomVariables_segmentMatchNONE';
    }
}

Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchNONE::$fixture
    = new Test_Piwik_Fixture_TwoVisitsWithCustomVariables();
Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchNONE::$fixture->doExtraQuoteTests = false;

