<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;
use Piwik\Plugins\Goals\API;

/**
 * Add one site and track many visits with custom variables & campaign IDs and
 * use visit ID instead of heuristics.
 */
class Test_Piwik_Fixture_SomeVisitsCustomVariablesCampaignsNotHeuristics extends Test_Piwik_BaseFixture
{
    public $dateTime = '2009-01-04 00:11:42';
    public $idSite = 1;
    public $idGoal = 1;

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        self::createWebsite($this->dateTime);
        API::getInstance()->addGoal($this->idSite, 'triggered js', 'manually', '', '');
    }

    private function trackVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;
        $idGoal = $this->idGoal;

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        // Record 1st page view
        $t->setUrl('http://example.org/index.htm?utm_campaign=GA Campaign&piwik_kwd=Piwik kwd&utm_term=GA keyword SHOULD NOT DISPLAY#pk_campaign=NOT TRACKED!!&pk_kwd=NOT TRACKED!!');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        $visitorId = $t->getVisitorId();
        self::assertTrue(strlen($visitorId) == 16);

        $this->testFirstPartyCookies($t);


        // Create a new Tracker object, with different attributes
        $t2 = self::getTracker($idSite, $dateTime, $defaultInit = false);

        // Make sure the ID is different at first
        $visitorId2 = $t2->getVisitorId();
        self::assertTrue($visitorId != $visitorId2);

        // Then force the visitor ID 
        $t2->setVisitorId($visitorId);

        // And Record a Goal: The previous visit should be updated rather than a new visit Created 
        $t2->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t2->doTrackGoal($idGoal, $revenue = 42.256));

        // Yet another visitor, this time with a manual goal conversion, which should be credited to the campaign
        $t3 = self::getTracker($idSite, $dateTime);
        $t3->setUrlReferrer('http://example.org/referrer');
        $t3->setForceVisitDateTime(Date::factory($dateTime)->addHour(1.3)->getDatetime());
        // fake a website ref cookie, the campaign should be credited for conversion, not referrer.example.com nor example.org 
        $t3->DEBUG_APPEND_URL = '&_ref=http%3A%2F%2Freferrer.example.com%2Fpage%2Fsub%3Fquery%3Dtest%26test2%3Dtest3';
        $t3->setUrl('http://example.org/index.htm#pk_campaign=CREDITED TO GOAL PLEASE');
        self::checkResponse($t3->doTrackGoal($idGoal, 42));

        // visitor #4, test for blank referrer campaign keyword
        $t4 = self::getTracker($idSite, $dateTime);
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(3)->getDatetime());
        $t4->setUrlReferrer('http://bing.com/search?q=whatever');
        $t4->setUrl('http://example.org/index.html?utm_campaign=GA+Campaign');
        self::checkResponse($t4->doTrackPageView('first page'));

        // No campaign keyword specified, will use the referrer hostname
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(4)->getDatetime());
        $t4->setUrlReferrer('http://thing1.com/a/b/c.html?a=b&d=c');
        $t4->setUrl('http://example.org/index.html?utm_campaign=GA+Campaign');
        self::checkResponse($t4->doTrackPageView('second page'));

        // Test with Google adsense type URL:
        $adsenseReferrerUrl = 'http://googleads.g.doubleclick.net/pagead/ads?client=ca-pub-12345&output=html&h=280&slotname=123&w=336&lmt=1359388321&202&url=http%3A%2F%2Fwww.adsense-publisher-website.org%2F&dt=123&bpp=13&shv=r22&jsv=1565606614&correlator=ss&ga_vid=aaa&ga_sid=1359435122&ga_hid=1801871121&ga_fc=0&u_tz=780&u_his=4&u_java=1&u_h=900&u_w=1600&u_ah=876&u_aw=1551&u_cd=24&u_nplug=4&u_nmime=5&dff=georgia&dfs=16&adx=33&ady=201&biw=1551&bih=792&oid=3&fu=0&ifi=1&dtd=608&p=http%3A//www.adsense-publisher-website.com';
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(5)->getDatetime());
        $t4->setUrlReferrer($adsenseReferrerUrl);
        $t4->setUrl('http://example.org/index.html?utm_campaign=Adsense campaign');
        self::checkResponse($t4->doTrackPageView('second page'));

        // Test with google Adwords URL
        $adwordsUrl = 'http://www.google.co.nz/aclk?sa=L&ai=uYmFyiZgAf0oO0J&num=3&sig=EpOCR4xQ&ved=ENEM&adurl=http://pixel.everesttech.net/3163/cq%3Fev_sid%3D3%26ev_cmpid%3D33%26ev_ln%3Dused%2520wii%2520consoles%26ev_crx%528386%26ev_mt%3Db%26ev_n%3Dg%26ev_ltx%3D%26ev_pl%3D%26ev_pos%3D1s2%26url%3Dhttp%253A//au.shopping.com/used%2520wii%2520consoles/products%253Flinkin_id%253D8077872&rct=j&q=nintendo+consoles+second+hand';
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(6)->getDatetime());
        $t4->setUrlReferrer($adwordsUrl);
        $t4->setUrl('http://example.org/index.html?utm_campaign=Adwords campaign');
        self::checkResponse($t4->doTrackPageView('second page'));
    }

    /**
     * Test setting/getting the first party cookie via the PHP Tracking Client
     * @param $t
     */
    private function testFirstPartyCookies(PiwikTracker $t)
    {
        $viewts = '1302307497';
        $uuid = 'ca0afe7b6b692ff5';
        $_COOKIE['_pk_id_1_1fff'] = $uuid . '.1302307497.1.' . $viewts . '.1302307497';
        $_COOKIE['_pk_ref_1_1fff'] = '["YEAH","RIGHT!",1302307497,"http://referrer.example.org/page/sub?query=test&test2=test3"]';
        $_COOKIE['_pk_cvar_1_1fff'] = '{"1":["VAR 1 set, var 2 not set","yes"],"3":["var 3 set","yes!!!!"]}';

        // test loading 'id' cookie
        self::assertContains("_viewts=" . $viewts, $t->getUrlTrackPageView());
        self::assertEquals($uuid, $t->getVisitorId());
        self::assertEquals($t->getAttributionInfo(), $_COOKIE['_pk_ref_1_1fff']);
        self::assertEquals(array("VAR 1 set, var 2 not set", "yes"), $t->getCustomVariable(1));
        self::assertFalse($t->getCustomVariable(2));
        self::assertEquals(array("var 3 set", "yes!!!!"), $t->getCustomVariable(3));
        self::assertFalse($t->getCustomVariable(4));
        self::assertFalse($t->getCustomVariable(5));
        self::assertFalse($t->getCustomVariable(6));
        self::assertFalse($t->getCustomVariable(-1));

        unset($_COOKIE['_pk_id_1_1fff']);
        unset($_COOKIE['_pk_ref_1_1fff']);
        unset($_COOKIE['_pk_cvar_1_1fff']);
    }
}
