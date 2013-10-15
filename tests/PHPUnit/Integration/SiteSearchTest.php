<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Testing the various use cases w/ internal Site Search tracking
 */
class Test_Piwik_Integration_SiteSearch extends IntegrationTestCase
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
            'Actions.getPageUrls',
            'Actions.getPageTitles',
            'CustomVariables.getCustomVariables',
            'Actions.getSiteSearchKeywords',
            'Actions.getSiteSearchCategories',
            'Actions.getSiteSearchNoResultKeywords',
            'Actions.getPageTitlesFollowingSiteSearch',
            'Actions.getPageUrlsFollowingSiteSearch',
        );
    }

    public function getApiForTesting()
    {
        $dateTime = self::$fixture->dateTime;
        $idSite1 = self::$fixture->idSite1;

        $apiToCall = $this->getApiToCall();

        $periods = array('day', 'month');

        $result = array(
            // Request data for the last 6 periods and idSite=all
            array($apiToCall, array('idSite'       => 'all',
                                    'date'         => $dateTime,
                                    'periods'      => $periods,
                                    'setDateLastN' => true,
                                    'testSuffix'   => '_AllSites')),

            // We also test a single period/single site to check that this use case (Reports per idSite in the response) works
            array($apiToCall, array(
                'idSite'       => $idSite1,
                'date'         => $dateTime,
                'periods'      => $periods,
                'setDateLastN' => false,
                'testSuffix'   => '_NotLastNPeriods')),
        );

        // testing metadata API for multiple periods
        foreach ($apiToCall as $api) {
            list($apiModule, $apiAction) = explode(".", $api);

            $result[] = array(
                'API.getProcessedReport', array('idSite'       => $idSite1,
                                                'date'         => $dateTime,
                                                'periods'      => $periods,
                                                'setDateLastN' => true,
                                                'apiModule'    => $apiModule,
                                                'apiAction'    => $apiAction,
                                                'testSuffix'   => '_' . $api . '_firstSite_lastN')
            );
        }
        return $result;
    }

    public static function getOutputPrefix()
    {
        return 'SiteSearch';
    }
}

Test_Piwik_Integration_SiteSearch::$fixture = new Test_Piwik_Fixture_ThreeSitesWithManyVisitsWithSiteSearch();

