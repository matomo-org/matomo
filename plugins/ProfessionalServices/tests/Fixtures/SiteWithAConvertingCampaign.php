<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices\tests\Fixtures;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\Date;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\ReportPeriod;
use Piwik\Tests\Framework\Fixture;

/**
 * One website where a single campaign drove conversions of **two** goals.
 *
 * Two goals on purpose. The promotion quotes the campaign's conversions summed across
 * every goal, and with only one goal that figure is identical to the goal's own total -
 * so a link pointing at the goal's report would look correct while being the wrong slice
 * of the data. Splitting the conversions between two goals makes the two numbers differ,
 * which is what lets a test tell a correct destination from a coincidence.
 *
 * Dated relative to the reporting week, so the fixture does not stop working when the week
 * rolls over.
 */
class SiteWithAConvertingCampaign extends Fixture
{
    public $idSite = 1;

    public const CAMPAIGN = 'QA-Campaign';

    /** Above CampaignConversionsTrigger::MINIMUM_CONVERSIONS once the two are added up. */
    public const CONVERSIONS_FIRST_GOAL = 120;

    public const CONVERSIONS_SECOND_GOAL = 90;

    public function setUp(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite('2020-01-01 00:00:00');
        }

        Access::doAsSuperUser(function () {
            GoalsApi::getInstance()->addGoal($this->idSite, 'Signup', 'url', 'signup-done', 'contains');
            GoalsApi::getInstance()->addGoal($this->idSite, 'Purchase', 'url', 'purchase-done', 'contains');
        });

        $this->trackCampaignConversions('signup-done', self::CONVERSIONS_FIRST_GOAL, 0);
        $this->trackCampaignConversions('purchase-done', self::CONVERSIONS_SECOND_GOAL, 600);

        // Build the archives the promotion reads; the trigger itself never archives.
        foreach (['Referrers.getCampaigns', 'Goals.get'] as $method) {
            Request::processRequest($method, [
                'idSite' => $this->idSite,
                'period' => ReportPeriod::PERIOD,
                'date' => ReportPeriod::DATE,
                'idGoal' => 0,
                'filter_update_columns_when_show_all_goals' => 1,
            ], []);
        }
    }

    /**
     * Visits that arrive on a campaign landing page and then reach a goal page. Two
     * pageviews each, deliberately: a single page visit bounces, and a bouncing entry page
     * would let the Heatmaps promotion outrank this one.
     */
    private function trackCampaignConversions(string $goalPath, int $visits, int $minuteOffset): void
    {
        $start = Date::factory(ReportPeriod::DATE)->getDatetime();
        $tracker = self::getTracker($this->idSite, $start, true, true);

        for ($i = 0; $i < $visits; $i++) {
            $at = Date::factory($start)->addPeriod($minuteOffset + $i, 'minute')->getDatetime();

            $tracker->setNewVisitorId();
            $tracker->setIp('10.40.' . (int) ($i / 250) . '.' . ($i % 250 + 1));

            // The campaign is registered from the landing URL, not from a referrer header.
            $tracker->setForceVisitDateTime($at);
            $tracker->setUrl('http://example.org/landing?mtm_campaign=' . self::CAMPAIGN);
            self::checkResponse($tracker->doTrackPageView('Landing'));

            $tracker->setForceVisitDateTime($at);
            $tracker->setUrl('http://example.org/' . $goalPath);
            self::checkResponse($tracker->doTrackPageView('Converted'));
        }
    }

    public function tearDown(): void
    {
        // nothing to undo: the test database is rebuilt for the fixture
    }
}
