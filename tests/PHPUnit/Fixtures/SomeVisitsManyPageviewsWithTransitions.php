<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Adds one site and tracks a couple visits with many pageviews. The
 * pageviews are designed to have many transitions between pages.
 */
class Test_Piwik_Fixture_SomeVisitsManyPageviewsWithTransitions extends Test_Piwik_BaseFixture
{
    public $dateTime = '2010-03-06 11:22:33';
    public $idSite = 1;

    private $prefixCounter = 0;

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
    }

    private function trackVisits()
    {
        $visit1 = $this->createVisit(1);
        $visit1->setUrlReferrer('http://www.google.com.vn/url?sa=t&rct=j&q=%3C%3E%26%5C%22the%20pdo%20extension%20is%20required%20for%20this%20adapter%20but%20the%20extension%20is%20not%20loaded&source=web&cd=4&ved=0FjAD&url=http%3A%2F%2Fforum.piwik.org%2Fread.php%3F2%2C1011&ei=y-HHAQ&usg=AFQjCN2-nt5_GgDeg&cad=rja');
        $this->trackPageView($visit1, 0, 'page/one.html');
        $this->trackPageView($visit1, 0.1, 'sub/dir/page2.html');
        $this->trackPageView($visit1, 0.2, 'page/one.html');
        $this->trackPageView($visit1, 0.3, 'the/third_page.html?foo=bar');
        $this->trackPageView($visit1, 0.4, 'page/one.html');
        $this->trackPageView($visit1, 0.5, 'the/third_page.html?foo=bar');
        $this->trackPageView($visit1, 0.6, 'page/one.html');
        $this->trackPageView($visit1, 0.7, 'the/third_page.html?foo=baz#anchor1');
        $this->trackPageView($visit1, 0.8, 'page/one.html');
        $this->trackPageView($visit1, 0.9, 'page/one.html');
        $this->trackPageView($visit1, 1.0, 'the/third_page.html?foo=baz#anchor2');
        $this->trackPageView($visit1, 1.1, 'page/one.html');
        $this->trackPageView($visit1, 1.2, 'page3.html');

        $visit2 = $this->createVisit(2);
        $visit2->setUrlReferrer('http://www.external.com.vn/referrerPage-notCounted.html');
        $this->trackPageView($visit2, 0, 'sub/dir/page2.html');
        $this->trackPageView($visit2, 0.1, 'the/third_page.html?foo=bar');
        $this->trackPageView($visit2, 0.2, 'page/one.html');
        $this->trackPageView($visit2, 0.3, 'the/third_page.html?foo=baz#anchor1');

        $visit3 = $this->createVisit(3);
        $visit3->setUrlReferrer('http://www.external.com.vn/referrerPage-counted.html');
        $this->trackPageView($visit3, 0.1, 'page/one.html');
        $this->trackPageView($visit3, 0.2, 'sub/dir/page2.html');
        $this->trackPageView($visit3, 0.3, 'page/one.html');

        $visit4 = $this->createVisit(4);
        $this->trackPageView($visit4, 0, 'page/one.html?pk_campaign=TestCampaign&pk_kwd=TestKeyword');

        $visit5 = $this->createVisit(5);
        $this->trackPageView($visit5, 0, 'page/one.html');
    }

    private function createVisit($id)
    {
        $visit = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $visit->setIp('156.5.3.' . $id);
        return $visit;
    }

    private function trackPageView($visit, $timeOffset, $path)
    {
        // rotate protocol and www to make sure it doesn't matter
        $prefixes = array('http://', 'http://www.', 'https://', 'https://');
        $prefix = $prefixes[$this->prefixCounter];
        $this->prefixCounter = ($this->prefixCounter + 1) % 4;

        /** @var $visit PiwikTracker */
        $visit->setUrl($prefix . 'example.org/' . $path);
        $visit->setForceVisitDateTime(Piwik_Date::factory($this->dateTime)->addHour($timeOffset)->getDatetime());
        self::checkResponse($visit->doTrackPageView('page title'));
    }
}
