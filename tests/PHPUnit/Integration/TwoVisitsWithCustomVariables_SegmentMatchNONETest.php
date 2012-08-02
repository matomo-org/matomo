<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

require_once dirname(__FILE__) . '/TwoVisitsWithCustomVariablesTest.php';

/**
 * testing a segment containing all supported fields
 */
class Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentMatchNONE extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{
    public static function setUpBeforeClass()
    {
        IntegrationTestCase::setUpBeforeClass();
        self::$visitorId = substr(md5(uniqid()), 0, 16);
        self::$doExtraQuoteTests = false;
        try {
            self::setUpWebsitesAndGoals();
            self::trackVisits();
        } catch(Exception $e) {
            // Skip whole test suite if an error occurs while setup
            throw new PHPUnit_Framework_SkippedTestSuiteError($e->getMessage());
        }
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        TwoVisitsWithCustomVariables_SegmentMatchNONE
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array('VisitsSummary.get', 'CustomVariables.getCustomVariables');

        return array(
            array($apiToCall, array('idSite'       => 'all',
                                    'date'         => self::$dateTime,
                                    'periods'      => array('day', 'week'),
                                    'setDateLastN' => true,
                                    'segment'      => $this->getSegmentToTest()))
        );
    }

    public function getSegmentToTest()
    {
        // Segment matching NONE
        $segments          = Piwik_API_API::getInstance()->getSegmentsMetadata(self::$idSite);
        $segmentExpression = array();

        $seenVisitorId = false;
        foreach ($segments as $segment) {
            $value = 'campaign';
            if ($segment['segment'] == 'visitorId') {
                $seenVisitorId = true;
                $value         = '34c31e04394bdc63';
            }
            if ($segment['segment'] == 'visitEcommerceStatus') {
                $value = 'none';
            }
            $segmentExpression[] = $segment['segment'] . '!=' . $value;
        }

        $segment = implode(";", $segmentExpression);

        // just checking that this segment was tested (as it has the only visible to admin flag)
        $this->assertTrue($seenVisitorId);
        $this->assertGreaterThan(100, strlen($segment));

        return $segment;
    }

    public function getOutputPrefix()
    {
        return 'twoVisitsWithCustomVariables_segmentMatchNONE';
    }
}

