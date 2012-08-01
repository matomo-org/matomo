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
 * Tests use of custom variable segments.
 */
class Test_Piwik_Integration_TwoVisitsWithCustomVariables_SegmentContains extends Test_Piwik_Integration_TwoVisitsWithCustomVariables
{
    public static function setUpBeforeClass()
    {
        IntegrationTestCase::setUpBeforeClass();
        self::$visitorId = substr(md5(uniqid()), 0, 16);
        self::$doExtraQuoteTests = false;
        self::setUpWebsitesAndGoals();
        self::trackVisits();
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        TwoVisitsWithCustomVariables_SegmentContains
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $return = array();

        $api            = array('Actions.getPageUrls', 'Actions.getPageTitles', 'VisitsSummary.get');
        $segmentsToTest = array(
            // array( SegmentString , TestSuffix , Array of API to test)
            array("pageTitle=@*_)%", '_SegmentPageTitleContainsStrangeCharacters', array('Actions.getPageTitles', 'VisitsSummary.get')),
            array("pageUrl=@user/profile", '_SegmentPageUrlContains', $api),
            array("pageTitle=@Profile pa", '_SegmentPageTitleContains', $api),
            array("pageUrl!@user/profile", '_SegmentPageUrlExcludes', $api),
            array("pageTitle!@Profile pa", '_SegmentPageTitleExcludes', $api),
        );

        foreach ($segmentsToTest as $segment) {
            // Also test "Page URL / Page title CONTAINS string" feature
            $return[] = array($segment[2],
                array('idSite'       => self::$idSite, 'date' => self::$dateTime, 'periods' => array('day'),
                      'setDateLastN' => false,
                      'segment'      => $segment[0],
                      'testSuffix'   => $segment[1])
            );
        }
        return $return;
    }
}

