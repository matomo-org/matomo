<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site and tracks two visits. One visit is a bot and one has no keyword
 * but is from a search engine.
 */
class TwoVisitsNoKeywordWithBot extends Fixture
{
    public $dateTime = '2010-03-06 11:22:33';
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }
    }

    private function trackVisits()
    {
        // tests run in UTC, the Tracker in UTC
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;
        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        // Also testing to record this as a bot while specifically allowing bots
        $t->setUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $t->DEBUG_APPEND_URL .= '&bots=1';
        $t->DEBUG_APPEND_URL .= '&forceIpAnonymization=1';

        // VISIT 1 = Referrer is "Keyword not defined"
        // Alsotrigger goal to check that attribution goes to this keyword
        $t->setUrlReferrer('http://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=1&ved=0CC&url=http%3A%2F%2Fpiwik.org%2F&ei=&usg=');

        $t->setUrl('http://example.org/this%20is%20cool!?filter=<script>alert(1);</script>{"place":{"place":"0c5b2444-70a0-4932-980c-b4dc0d3f02b5"}}');
        self::checkResponse($t->doTrackPageView('incredible title! (Page URL contains a HTML entity)'));

        $idGoal = 1;
        if (!self::goalExists($idSite, $idGoal)) {
            $idGoal = API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
        }
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal, $revenue = 42));

        // VISIT 2 = Referrer has keyword, but the URL should be rewritten
        // in Live Output to point to google search result page
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(2)->getDatetime());
        $t->setUrlReferrer('http://www.google.com.vn/url?sa=t&rct=j&q=%3C%3E%26%5C%22the%20pdo%20extension%20is%20required%20for%20this%20adapter%20but%20the%20extension%20is%20not%20loaded&source=web&cd=4&ved=0FjAD&url=http%3A%2F%2Fforum.piwik.org%2Fread.php%3F2%2C1011&ei=y-HHAQ&usg=AFQjCN2-nt5_GgDeg&cad=rja');

        // Test with empty title, that the output of Live is valid
        self::checkResponse($t->doTrackPageView(''));
    }
}
