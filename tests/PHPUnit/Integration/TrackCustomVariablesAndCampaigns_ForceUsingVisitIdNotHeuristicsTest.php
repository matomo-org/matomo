<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Test tracker & API when forcing the use of visit ID instead of heuristics. Also
 * tests campaign tracking.
 */
class Test_Piwik_Integration_TrackCustomVariablesAndCampaigns_ForceUsingVisitIdNotHeuristics extends IntegrationTestCase
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
        $apiToCall = array('VisitsSummary.get', 'Referrers.getCampaigns', 'Referrers.getWebsites');

        return array(
            // TOTAL should be: 1 visit, 1 converted goal, 1 page view
            array($apiToCall, array('idSite' => self::$fixture->idSite, 'date' => self::$fixture->dateTime))
        );
    }

    public static function getOutputPrefix()
    {
        return 'PiwikTracker_trackForceUsingVisitId_insteadOfHeuristics_alsoTestsCampaignTracking';
    }
}

Test_Piwik_Integration_TrackCustomVariablesAndCampaigns_ForceUsingVisitIdNotHeuristics::$fixture =
    new Test_Piwik_Fixture_SomeVisitsCustomVariablesCampaignsNotHeuristics();

