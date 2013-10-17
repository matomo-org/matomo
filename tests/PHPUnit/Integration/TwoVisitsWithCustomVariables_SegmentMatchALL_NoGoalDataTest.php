<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

class Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchALL_NoGoalData extends IntegrationTestCase
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
        $apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

        // Segment matching ALL
        // + adding DOES NOT CONTAIN segment always matched, to test this particular operator
        $resolution = self::$fixture->resolutionWidthToUse . 'x' . self::$fixture->resolutionHeightToUse;
        $segment = 'resolution==' . $resolution . ';customVariableName1!@randomvalue does not exist';

        return array(
            array($apiToCall, array('idSite'       => 'all',
                                    'date'         => self::$fixture->dateTime,
                                    'periods'      => array('day', 'week'),
                                    'setDateLastN' => true,
                                    'segment'      => $segment))
        );
    }

    public static function getOutputPrefix()
    {
        return 'twoVisitsWithCustomVariables_segmentMatchALL_noGoalData';
    }
}

Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchALL_NoGoalData::$fixture
    = new Test_Piwik_Fixture_TwoVisitsWithCustomVariables();
Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchALL_NoGoalData::$fixture->doExtraQuoteTests = false;

