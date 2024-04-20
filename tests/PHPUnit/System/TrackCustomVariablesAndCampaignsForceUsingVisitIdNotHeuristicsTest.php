<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\SomeVisitsCustomVariablesCampaignsNotHeuristics;

/**
 * Test tracker & API when forcing the use of visit ID instead of heuristics. Also
 * tests campaign tracking.
 *
 * @group TrackCustomVariablesAndCampaignsForceUsingVisitIdNotHeuristicsTest
 * @group Plugins
 */
class TrackCustomVariablesAndCampaignsForceUsingVisitIdNotHeuristicsTest extends SystemTestCase
{
    /**
     * @var SomeVisitsCustomVariablesCampaignsNotHeuristics
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array('VisitsSummary.get', 'Referrers.getCampaigns', 'Referrers.getWebsites', 'Live.getLastVisitsDetails');

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

TrackCustomVariablesAndCampaignsForceUsingVisitIdNotHeuristicsTest::$fixture =
    new SomeVisitsCustomVariablesCampaignsNotHeuristics();
