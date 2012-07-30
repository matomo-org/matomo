<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

require_once dirname(__FILE__) . '/TwoVisitsWithCustomVariablesTest.php';

class Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchALL_NoGoalData extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{
    protected static $width = 1111;
    protected static $height = 222;
    protected static $doExtraQuoteTests = false;

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        TwoVisitsWithCustomVariables_SegmentMatchALL_NoGoalData
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
        $resolution = self::$width . 'x' . self::$height;
        $segment    = 'resolution==' . $resolution . ';customVariableName1!@randomvalue does not exist';

        return array(
            array($apiToCall, array('idSite'       => 'all',
                                    'date'         => self::$dateTime,
                                    'periods'      => array('day', 'week'),
                                    'setDateLastN' => true,
                                    'segment'      => $segment))
        );
    }

    public function getOutputPrefix()
    {
        return 'twoVisitsWithCustomVariables_segmentMatchALL_noGoalData';
    }
}

