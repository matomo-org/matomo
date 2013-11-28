<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\API\Request;

/**
 * Track visits before website creation date and test that Piwik handles them correctly.
 */
class Test_Piwik_Integration_VisitsInPast_InvalidateOldReports extends IntegrationTestCase
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

    /**
     * This should NOT return data for old dates before website creation
     */
    public function getApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $idSite2 = self::$fixture->idSite2;
        $dateTimeDateInPastWebsite1 = self::$fixture->dateTimeDateInPastWebsite1;
        $dateTimeDateInPastWebsite2 = self::$fixture->dateTimeDateInPastWebsite2;

        // We test a typical Numeric and a Recursive blob reports
        $apiToCall = array('VisitsSummary.get', 'Actions.getPageUrls');

        // We also test a segment
        //TODO

        // Build tests for the 2 websites
        return array(
            array($apiToCall, array('idSite'                 => $idSite,
                                    'testSuffix'             => 'Website' . $idSite . '_OldReportsShouldNotAppear',
                                    'date'                   => $dateTimeDateInPastWebsite1,
                                    'periods'                => 'month',
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1))),
            array($apiToCall, array('idSite'                 => $idSite2,
                                    'testSuffix'             => 'Website' . $idSite2 . '_OldReportsShouldNotAppear',
                                    'date'                   => $dateTimeDateInPastWebsite2,
                                    'periods'                => 'month',
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1))),
        );
    }

    /**
     * @depends      testApi
     * @dataProvider getAnotherApiForTesting
     * @group        Integration
     */
    public function testAnotherApi($api, $params)
    {
        $idSite = self::$fixture->idSite;
        $idSite2 = self::$fixture->idSite2;

        // 1) Invalidate old reports for the 2 websites
        // Test invalidate 1 date only
        $r = new Request("module=API&method=CoreAdminHome.invalidateArchivedReports&idSites=4,5,6,55,-1,s',1&dates=2010-01-03");
        $this->checkRequestResponse($r->process());

        // Test invalidate comma separated dates
        $r = new Request("module=API&method=CoreAdminHome.invalidateArchivedReports&idSites=" . $idSite . "," . $idSite2 . "&dates=2010-01-06,2009-10-30");
        $this->checkRequestResponse($r->process());

        // test invalidate date in the past
        // Format=original will re-throw exception
        $r = new Request("module=API&method=CoreAdminHome.invalidateArchivedReports&idSites=" . $idSite2 . "&dates=2009-06-29&format=original");
        $this->checkRequestResponse( json_encode( $r->process() ) );

        // invalidate a date more recent to check the date is only updated when it's earlier than current
        $r = new Request("module=API&method=CoreAdminHome.invalidateArchivedReports&idSites=" . $idSite2 . "&dates=2010-03-03");
        $this->checkRequestResponse($r->process());

        // Make an invalid call
        $idSiteNoAccess = 777;
        try {
            $request = new Request("module=API&method=CoreAdminHome.invalidateArchivedReports&idSites=" . $idSiteNoAccess . "&dates=2010-03-03&format=original");
            $request->process();
            $this->fail();
        } catch(Exception $e) {
        }


        // 2) Call API again, with an older date, which should now return data
        $this->runApiTests($api, $params);
    }

    /**
     * This is called after getApiToTest()
     * WE invalidate old reports and check that data is now returned for old dates
     */
    public function getAnotherApiForTesting()
    {
        $idSite = self::$fixture->idSite;
        $idSite2 = self::$fixture->idSite2;
        $dateTimeDateInPastWebsite1 = self::$fixture->dateTimeDateInPastWebsite1;
        $dateTimeDateInPastWebsite2 = self::$fixture->dateTimeDateInPastWebsite2;

        $apiToCall = array('VisitsSummary.get', 'Actions.getPageUrls');

        return array(
            array($apiToCall, array('idSite'                 => $idSite,
                                    'testSuffix'             => 'Website' . $idSite . '_OldReportsShouldAppear',
                                    'date'                   => $dateTimeDateInPastWebsite1,
                                    'periods'                => 'month',
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1))),
            array($apiToCall, array('idSite'                 => $idSite2,
                                    'testSuffix'             => 'Website' . $idSite2 . '_OldReportsShouldAppear',
                                    'date'                   => $dateTimeDateInPastWebsite2,
                                    'periods'                => 'month',
                                    'setDateLastN'           => 4, // 4months ahead
                                    'otherRequestParameters' => array('expanded' => 1))),
        );
    }

    public static function getOutputPrefix()
    {
        return 'VisitsInPast_InvalidateOldReports';
    }
}

Test_Piwik_Integration_VisitsInPast_InvalidateOldReports::$fixture = new Test_Piwik_Fixture_TwoSitesVisitsInPast();

