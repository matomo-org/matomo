<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestingEnvironmentVariables;
use MatomoTracker;

/**
 * Add one site and track many visits with custom variables & campaign IDs and
 * use visit ID instead of heuristics.
 */
class SomeVisitsCustomVariablesCampaignsNotHeuristics extends Fixture
{
    public $dateTime = '2009-01-04 00:11:42';
    public $idSite = 1;
    public $idGoal = 1;

    public function setUp(): void
    {
        $this->setPiwikEnvironmentOverrides();
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown(): void
    {
    }

    private function setPiwikEnvironmentOverrides()
    {
        $env = new TestingEnvironmentVariables();
        $env->overrideConfig('Tracker', 'create_new_visit_when_website_referrer_changes', 1);
        $env->save();
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }

        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            API::getInstance()->addGoal($this->idSite, 'triggered js', 'manually', '', '');
        }

        if (!self::goalExists($idSite = 1, $idGoal = 2)) {
            API::getInstance()->addGoal($this->idSite, 'view act', 'url', 'http://mutantregistration.com/act.html', 'exact');
        }
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
        $t2->setTokenAuth(self::getTokenAuth());

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

        // another action soon after last but with different campaign (should result in new visit)
        $t3->setForceVisitDateTime(Date::factory($dateTime)->addHour(1.4)->getDatetime());
        $t3->setUrl('http://example.org/index.html#pk_campaign=CREDITED TO ANOTHER GOAL');
        self::checkResponse($t3->doTrackGoal($idGoal, 24));

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

        // Test with Google adwords type URL:
        $adwordsReferrerUrl = 'http://googleads.g.doubleclick.net/pagead/ads?client=ca-pub-12345&output=html&h=280&slotname=123&w=336&lmt=1359388321&202&url=http%3A%2F%2Fwww.adwords-publisher-website.org%2F&dt=123&bpp=13&shv=r22&jsv=1565606614&correlator=ss&ga_vid=aaa&ga_sid=1359435122&ga_hid=1801871121&ga_fc=0&u_tz=780&u_his=4&u_java=1&u_h=900&u_w=1600&u_ah=876&u_aw=1551&u_cd=24&u_nplug=4&u_nmime=5&dff=georgia&dfs=16&adx=33&ady=201&biw=1551&bih=792&oid=3&fu=0&ifi=1&dtd=608&p=http%3A//www.adwords-publisher-website.com';
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(5)->getDatetime());
        $t4->setUrlReferrer($adwordsReferrerUrl);
        $t4->setUrl('http://example.org/index.html?utm_campaign=Adwords campaign');
        self::checkResponse($t4->doTrackPageView('second page'));

        // Test with google Adwords URL
        $adwordsUrl = 'http://www.google.co.nz/aclk?sa=L&ai=uYmFyiZgAf0oO0J&num=3&sig=EpOCR4xQ&ved=ENEM&adurl=http://pixel.everesttech.net/3163/cq%3Fev_sid%3D3%26ev_cmpid%3D33%26ev_ln%3Dused%2520wii%2520consoles%26ev_crx%528386%26ev_mt%3Db%26ev_n%3Dg%26ev_ltx%3D%26ev_pl%3D%26ev_pos%3D1s2%26url%3Dhttp%253A//au.shopping.com/used%2520wii%2520consoles/products%253Flinkin_id%253D8077872&rct=j&q=nintendo+consoles+second+hand';
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(6)->getDatetime());
        $t4->setUrlReferrer($adwordsUrl);
        $t4->setUrl('http://example.org/index.html?utm_campaign=AdWords campaign');
        self::checkResponse($t4->doTrackPageView('second page'));

        // Test with google adwords
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(7)->getDatetime());
        $adwords = 'http://googleads.g.doubleclick.net/pagead/ads?client=ca-pub-x&output=html&h=15&slotname=2973049897&adk=3777420323&w=728&lmt=1381755030&flash=11.9.900.117&url=http%3A%2F%2Fexample.com%2F&dt=1381755030169&bpp=8&bdt=2592&shv=r20131008&cbv=r20130906&saldr=sa&correlator=1381755030200&frm=20&ga_vid=1659309719.1381755030&ga_sid=1381755030&ga_hid=1569070879&ga_fc=0&u_tz=660&u_his=3&u_java=1&u_h=768&u_w=1366&u_ah=728&u_aw=1366&u_cd=24&u_nplug=0&u_nmime=0&dff=times%20new%20roman&dfs=13&adx=311&ady=107&biw=1349&bih=673&oid=2&ref=http%3A%2F%2Fwww.google.com.au%2Furl%3Fsa%3Dt%26rct%3Dj%26q%3D%26esrc%3Ds%26frm%3D1%26source%3Dweb%26cd%3D10%26ved%3D0CGcQFjAJ%26url%3Dhttp%253A%252F%252Fexample.com%252F%26ei%3DXNtbUvrJPKXOiAfw1IH4Bw%26usg%3DAFQjCNE66zRf2zaUw8FKf0JWxiM1FiXHVg&vis=1&fu=0&ifi=1&pfi=32&dtd=122&xpc=tBekiCZTWM&p=http%3A//example.com&rl_rc=true&adsense_enabled=true&ad_type=text_image&oe=utf8&height=15&width=728&format=fp_al_lp&kw_type=radlink&prev_fmts=728x15_0ads_al&rt=ChBSW-iYAADltAqmmOfZAA2SEg1BbmltYXRlZCBUZXh0Ggj019wBciBqgSgBUhMI8OHhzq6WugIVhJOmCh2NYQBO&hl=en&kw0=Animated+Text&kw1=Animated+GIF&kw2=Animated+Graphics&kw3=Fonts&okw=Animated+Text';
        $t4->setUrlReferrer($adwords);
        $t4->setUrl('http://example.org/index.html');
        self::checkResponse($t4->doTrackPageView('Hello world'));

        // Test with google adwords bis
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(8)->getDatetime());
        $adwords = 'http://googleads.g.doubleclick.net/pagead/ads?lient=ca-pub-x&output=html&h=15&slotname=4299800108&adk=2258396486&w=728&lmt=1381746604&flash=11.9.900.117&url=http%3A%2F%2Fwww.example.com%2Fphotofilters%2F%26section_id%3D%26p%3D4&dt=1381746604865&bpp=5&bdt=83&shv=r20131008&cbv=r20130906&saldr=sa&correlator=1381746604888&frm=20&ga_vid=1273315809.1372079408&ga_sid=1381744659&ga_hid=2064025848&ga_fc=1&u_tz=120&u_his=17&u_java=1&u_h=864&u_w=1536&u_ah=826&u_aw=1536&u_cd=24&u_nplug=0&u_nmime=0&dff=times%20new%20roman&dfs=12&adx=404&ady=159&biw=1536&bih=770&oid=3&ref=http%3A%2F%2Fwww.example.com%2Fphotofilters%2F%26section_id%3D%26p%3D3&vis=0&fu=0&ifi=1&pfi=0&dtd=51&xpc=Pn2WpF35Mu&p=http%3A//www.example.com&rl_rc=false&adsense_enabled=true&ad_type=text&ui=rc:0&oe=utf8&height=15&width=728&format=fpkc_al_lp&kw_type=radlink&prev_fmts=728x15_0ads_al&rt=ChBSW8euAAeWTgrCYs_kAEUQEhBQaG90byBCYWNrZ3JvdW5kGgjieib00mVdpSgBUhMIy7OEnY-WugIVoZDCCh0qUgC-&hl=en&kw0=Photo+Shop+Image&kw1=Photo+Background&kw2=Photo+to+Painting&kw3=Photo+Digital&okw=Photo+Background';
        $t4->setUrlReferrer($adwords);
        $t4->setUrl('http://example.org/index.html');
        self::checkResponse($t4->doTrackPageView('Bonjour le monde'));

        // test one action w/ no campaign & then one action w/ a campaign (should result in 1 visit w/ overridden referrer)
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(10)->getDatetime());
        $t4->setUrlReferrer('');
        $t4->setUrl('http://example.org/index.html');
        self::checkResponse($t4->doTrackPageView('Hallo welt'));

        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(10.1)->getDatetime());
        $t4->setUrl('http://example.org/index.html?utm_campaign=GA Campaign&piwik_kwd=Piwik kwd');
        self::checkResponse($t4->doTrackPageView('¡hola mundo'));

        // right after last action, visit w/ referrer website (should result in another visit)
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(10.2)->getDatetime());
        $t4->setUrlReferrer('http://myreferrerwebsite.com');
        $t4->setUrl('http://example.org/index.html');
        self::checkResponse($t4->doTrackPageView('Dia duit ar domhan'));

        // test one action w/ no referrer website & then one action w/ referrer website (should result in 1 visit w/ overridden referrer)
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(11)->getDatetime());
        $t4->setUrlReferrer('');
        $t4->setUrl('http://example.org/index.html');
        self::checkResponse($t4->doTrackPageView('привет мир'));

        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(11.1)->getDatetime());
        $t4->setUrlReferrer('http://myotherreferrerwebsite.com');
        $t4->setUrl('http://example.org/index.html');
        self::checkResponse($t4->doTrackPageView('hallå världen'));

        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(11.2)->getDatetime()); // same referrer in next action, should result in just another action
        $t4->setUrlReferrer('http://myotherreferrerwebsite.com');
        $t4->setUrl('http://example.org/index.html');
        self::checkResponse($t4->doTrackPageView('halló heimur'));

        // same visitor as last w/ action soon after last action but w/ new referrer website (should result in another visit)
        $t4->setForceVisitDateTime(Date::factory($dateTime)->addHour(11.3)->getDatetime());
        $t4->setUrlReferrer('http://mutantregistration.com');
        $t4->setUrl('http://example.org/index.html');
        self::checkResponse($t4->doTrackPageView('העלא וועלט'));

        // test campaigns that are specified through _rcn (only conversion will be attributed to that campaign)
        $t5 = self::getTracker($idSite, $dateTime);
        $t5->setUrlReferrer('http://xavierinstitute.org');
        $t5->setUrl('http://mutantregistration.com/act.html');
        $t5->setAttributionInfo(json_encode(array('Gifted Search'))); // rcn supplied, nothing else
        self::checkResponse($t5->doTrackPageView('Mutant Registration'));
        self::checkResponse($t5->doTrackEcommerceOrder('vg25gedefg', 17.4));

        $t5->setForceVisitDateTime(Date::factory($dateTime)->addHour(1)->getDatetime());
        $t5->setUrlReferrer('http://mutantrights.org');
        $t5->setUrl('http://asteroidm.com');
        // all params suppplied, one that differs from url referrer
        $t5->setAttributionInfo(json_encode(array('Recruiting Drive', 'am i a mutant?',
            urlencode(Date::factory($dateTime)->addHour(1)->getDatetime()), 'http://sentinelwatch.org')));
        self::checkResponse($t5->doTrackPageView('Fighting Back'));
        self::checkResponse($t5->doTrackEcommerceOrder('32452435zdfg', 22.9));

        $t5->setForceVisitDateTime(Date::factory($dateTime)->addHour(2)->getDatetime());
        $t5->setUrlReferrer('http://apocalypsenow.org');
        $t5->setUrl('http://mutantrights.org');

        // params supplied, for existing campaign
        $t5->setAttributionInfo(json_encode(array('GA Campaign', 'some keyword',
            urlencode(Date::factory($dateTime)->addHour(2)->getDatetime()))));
        self::checkResponse($t5->doTrackPageView('Mutant Registration'));
        self::checkResponse($t5->doTrackEcommerceOrder('fsg5her35h', 5.33));
    }

    // see updateDomainHash() in piwik.js
    private function getFirstPartyCookieDomainHash()
    {
        $host = \Piwik\Url::getHost();
        $cookiePath = MatomoTracker::DEFAULT_COOKIE_PATH;
        return substr(sha1($host . $cookiePath), 0, 4);
    }

    /**
     * Test setting/getting the first party cookie via the PHP Tracking Client
     * @param $t
     */
    private function testFirstPartyCookies(MatomoTracker $t)
    {
        $domainHash = $this->getFirstPartyCookieDomainHash();
        $idCookieName = '_pk_id_1_' . $domainHash;
        $refCookieName = '_pk_ref_1_' . $domainHash;
        $customVarCookieName = '_pk_cvar_1_' . $domainHash;

        $viewts = '1302307497';
        $uuid = 'ca0afe7b6b692ff5';
        $_COOKIE[$idCookieName] = $uuid . '.' . $viewts;
        $_COOKIE[$refCookieName] = '["YEAH","RIGHT!",1302307497,"http://referrer.example.org/page/sub?query=test&test2=test3"]';
        $_COOKIE[$customVarCookieName] = '{"1":["VAR 1 set, var 2 not set","yes"],"3":["var 3 set","yes!!!!"]}';

        // test loading 'id' cookie
        self::assertStringContainsString("_idts=" . $viewts, $t->getUrlTrackPageView());
        self::assertEquals($uuid, $t->getVisitorId());
        self::assertEquals($t->getAttributionInfo(), $_COOKIE[$refCookieName]);
        self::assertEquals(array("VAR 1 set, var 2 not set", "yes"), $t->getCustomVariable(1));
        self::assertFalse($t->getCustomVariable(2));
        self::assertEquals(array("var 3 set", "yes!!!!"), $t->getCustomVariable(3));
        self::assertFalse($t->getCustomVariable(4));
        self::assertFalse($t->getCustomVariable(5));
        self::assertFalse($t->getCustomVariable(6));
        self::assertFalse($t->getCustomVariable(-1));

        unset($_COOKIE[$idCookieName]);
        unset($_COOKIE[$refCookieName]);
        unset($_COOKIE[$customVarCookieName]);
    }
}
