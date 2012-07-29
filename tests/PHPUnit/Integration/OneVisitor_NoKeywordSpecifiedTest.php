<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */

/**
 * 1) Tests empty google kwd works nicely in Live! output and Top keywords
 * 2) Tests IP anonymization
 * Also test that Live! will link to the search result page URL rather than the exact referrer URL
 * when the referrer URL is google.XX/url.... which is a redirect to landing page rather than the search result URL
 */
class Test_Piwik_Integration_OneVisitor_NoKeywordSpecified extends IntegrationTestCase
{
    protected static $dateTime = '2010-03-06 11:22:33';
    protected static $idSite   = 1;

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        OneVisitor_NoKeywordSpecified
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $apiToCall = array('Referers.getKeywords', 'Live.getLastVisitsDetails');

        return array(
            array($apiToCall, array('idSite' => self::$idSite, 'date' => self::$dateTime, 'language' => 'fr'))
        );
    }

    public function getOutputPrefix()
    {
        return 'OneVisitor_NoKeywordSpecified';
    }

    protected function setUpWebsitesAndGoals()
    {
        $this->createWebsite(self::$dateTime);
    }

    protected function trackVisits()
    {
        // tests run in UTC, the Tracker in UTC
        $dateTime = self::$dateTime;
        $idSite   = self::$idSite;
        $t        = $this->getTracker($idSite, $dateTime, $defaultInit = true, $useThirdPartyCookie = 1);

        // Also testing to record this as a bot while specifically allowing bots
        $t->setUserAgent('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $t->DEBUG_APPEND_URL .= '&bots=1';
        $t->DEBUG_APPEND_URL .= '&forceIpAnonymization=1';
        // VISIT 1 = Referrer is "Keyword not defined"
        // Alsotrigger goal to check that attribution goes to this keyword
        $t->setUrlReferrer('http://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=1&ved=0CC&url=http%3A%2F%2Fpiwik.org%2F&ei=&usg=');
        $t->setUrl('http://example.org/this%20is%20cool!');
        $this->checkResponse($t->doTrackPageView('incredible title!'));
        $idGoal = Piwik_Goals_API::getInstance()->addGoal($idSite, 'triggered js', 'manually', '', '');
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.3)->getDatetime());
        $this->checkResponse($t->doTrackGoal($idGoal, $revenue = 42));

        // VISIT 2 = Referrer has keyword, but the URL should be rewritten 
        // in Live Output to point to google search result page
        $t->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(2)->getDatetime());
        $t->setUrlReferrer('http://www.google.com.vn/url?sa=t&rct=j&q=%3C%3E%26%5C%22the%20pdo%20extension%20is%20required%20for%20this%20adapter%20but%20the%20extension%20is%20not%20loaded&source=web&cd=4&ved=0FjAD&url=http%3A%2F%2Fforum.piwik.org%2Fread.php%3F2%2C1011&ei=y-HHAQ&usg=AFQjCN2-nt5_GgDeg&cad=rja');

        $this->checkResponse($t->doTrackPageView('incredible title!'));

    }
}

