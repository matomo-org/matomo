<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site and tracks a couple visits with many pageviews. The
 * pageviews are designed to have many transitions between pages.
 */
class SomeVisitsManyPageviewsWithTransitions extends Fixture
{
    public $dateTime = '2010-03-06 11:22:33';
    public $idSite = 1;

    private $prefixCounter = 0;

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
            self::createWebsite(
                $this->dateTime,
                $ecommerce = 0,
                $siteName = 'Piwik test',
                $siteUrl = false,
                $siteSearch = 1
            );
        }
    }

    private function trackVisits()
    {
        $tracker = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $tracker->enableBulkTracking();

        $tracker->setIp('156.5.3.1');
        $tracker->setUrlReferrer('http://www.google.com.vn/url?sa=t&rct=j&q=%3C%3E%26%5C%22the%20pdo%20extension%20is%20required%20for%20this%20adapter%20but%20the%20extension%20is%20not%20loaded&source=web&cd=4&ved=0FjAD&url=http%3A%2F%2Fforum.piwik.org%2Fread.php%3F2%2C1011&ei=y-HHAQ&usg=AFQjCN2-nt5_GgDeg&cad=rja');
        $this->trackPageView($tracker, 0, 'page/one.html');
        $this->trackPageView($tracker, 0.1, 'sub/dir/page2.html');
        $this->trackPageView($tracker, 0.2, 'page/one.html');
        $this->trackPageView($tracker, 0.3, 'the/third_page.html?foo=bar');
        $this->trackPageView($tracker, 0.4, 'page/one.html');
        $this->trackPageView($tracker, 0.5, 'the/third_page.html?foo=bar');
        $this->trackPageView($tracker, 0.5, 'the/third_page.html?foo=bar');
        $this->trackPageView($tracker, 0.6, 'page/one.html');
        $this->trackPageView($tracker, 0.6, 'page/one.html');
        $this->trackPageView($tracker, 0.7, 'the/third_page.html?foo=baz#anchor1');
        $this->trackPageView($tracker, 0.7, 'the/third_page.html?foo=baz#anchor1');
        $this->trackPageView($tracker, 0.8, 'page/one.html');
        $this->trackPageView($tracker, 0.9, 'page/one.html');
        $this->trackPageView($tracker, 1.0, 'the/third_page.html?foo=baz#anchor2');
        $this->trackPageView($tracker, 1.1, 'page/one.html');
        $this->trackPageView($tracker, 1.2, 'page3.html');

        $tracker->setIp('156.5.3.2');
        $tracker->setNewVisitorId();
        $tracker->setUrlReferrer('http://www.external.com.vn/referrerPage-notCounted.html');
        $this->trackPageView($tracker, 0, 'sub/dir/page2.html');
        $this->trackPageView($tracker, 0.1, 'the/third_page.html?foo=bar');
        $this->trackPageView($tracker, 0.2, 'page/one.html');
        $this->trackPageView($tracker, 0.3, 'the/third_page.html?foo=baz#anchor1');

        $tracker->setIp('156.5.3.3');
        $tracker->setNewVisitorId();
        $tracker->setUrlReferrer('http://www.external.com.vn/referrerPage-counted.html');
        $this->trackPageView($tracker, 0.1, 'page/one.html');
        $this->trackPageView($tracker, 0.2, 'sub/dir/page2.html');
        $this->trackPageView($tracker, 0.3, 'page/one.html');

        $tracker->setIp('156.5.3.4');
        $tracker->setNewVisitorId();
        $tracker->setUrlReferrer('');
        $this->trackPageView($tracker, 0, 'page/one.html?pk_campaign=TestCampaign&pk_kwd=TestKeyword');

        $tracker->setIp('156.5.3.5');
        $tracker->setNewVisitorId();
        $tracker->setUrlReferrer('');
        $this->trackPageView($tracker, 0, 'page/one.html');

        // perform site search before & after page/one.html, then outlink after page/one.html, then download
        // before & after
        $tracker->setIp('156.5.3.6');
        $tracker->setNewVisitorId();
        $this->trackPageView($tracker, 0, 'page/search.html#q=mykwd', $this->dateTime, $pageViewType = 'site-search', $searchKeyword = 'mykwd', $searchCategory = 'mysearchcat');
        $this->trackPageView($tracker, 0.1, 'page/one.html');
        $this->trackPageView($tracker, 0.2, 'page/search.html#q=anotherkwd', $this->dateTime, $pageViewType = 'site-search', $searchKeyword = 'anotherkwd', $searchCategory = 'mysearchcat');
        $this->trackPageView($tracker, 0.25, 'page/one.html');
        $this->trackPageView($tracker, 0.3, 'to/outlink/page.html', $this->dateTime, $pageViewType = 'outlink');
        $this->trackPageView($tracker, 0.35, 'page/one.html');
        $this->trackPageView($tracker, 0.11, 'page/search.html#q=thirdkwd', $this->dateTime, $pageViewType = 'event', 'Song name here', 'Music');
        $this->trackPageView($tracker, 0.4, '', $this->dateTime, $pageViewType = 'download');
        $this->trackPageView($tracker, 0.45, 'page/one.html');
        $this->trackPageView($tracker, 0.5, '', $this->dateTime, $pageViewType = 'download');
        $this->trackPageView($tracker, 0.55, 'page/one.html');
        $this->trackPageView($tracker, 0.6, 'to/outlink/page2.html', $this->dateTime, $pageViewType = 'outlink');

        // perform new searches/outlinks before & after in later date to test 'month' period
        $laterDate = Date::factory($this->dateTime)->addDay(8)->getDatetime();
        $tracker->setIp('156.5.3.7');
        $tracker->setNewVisitorId();
        $this->trackPageView($tracker, 0, 'page/search.html#q=thirdkwd', $laterDate, $pageViewType = 'site-search', $searchKeyword = 'thirdkwd', $searchCategory = 'mysearchcat');
        $this->trackPageView($tracker, 0.1, 'page/one.html', $laterDate);
        $this->trackPageView($tracker, 0.11, 'page/search.html#q=thirdkwd', $laterDate, $pageViewType = 'event', 'Song name here', 'Music');
        $this->trackPageView($tracker, 0.15, 'to/another/outlink.html', $laterDate, $pageViewType = 'outlink');
        $this->trackPageView($tracker, 0.2, 'page/one.html', $laterDate);
        $this->trackPageView($tracker, 0.25, '', $laterDate, $pageViewType = 'download');
        $this->trackPageView($tracker, 0.3, 'page/one.html', $laterDate);
        $this->trackPageView(
            $tracker,
            0.35,
            'page/search.html#q=anotherkwd',
            $laterDate,
            $pageViewType = 'site-search',
            $searchKeyword = 'anotherkwd',
            $searchCategory = 'mysearchcat'
        );


        $tracker->setIp('156.5.3.8');
        $tracker->setNewVisitorId();
        $tracker->setUrlReferrer('http://www.google.com.vn/url?sa=t&rct=j&q='); // search w/ unknown keyword
        $this->trackPageView($tracker, 0, 'page/one.html');

        self::checkBulkTrackingResponse($tracker->doBulkTrack());
    }

    private function trackPageView(
        $visit,
        $timeOffset,
        $path,
        $dateTime = null,
        $pageViewType = 'normal',
        $searchKeyword = null,
        $searchCategory = null
    ) {
        if ($dateTime === null) {
            $dateTime = $this->dateTime;
        }

        // rotate protocol and www to make sure it doesn't matter
        $prefixes = array('http://', 'http://www.', 'https://', 'https://');
        $prefix = $prefixes[$this->prefixCounter % 4];
        $this->prefixCounter = $this->prefixCounter + 1;

        /** @var $visit MatomoTracker */
        $visit->setUrl($prefix . 'example.org/' . $path);
        $visit->setForceVisitDateTime(Date::factory($dateTime)->addHour($timeOffset)->getDatetime());

        if ($pageViewType == 'normal') {
            self::assertTrue($visit->doTrackPageView('page title - ' . $path));
        } else if ($pageViewType == 'outlink') {
            self::assertTrue($visit->doTrackAction($prefix . 'anothersite.com/' . $path, 'link'));
        } else if ($pageViewType == 'download') {
            $downloadUrl = $prefix . 'example.org/downloads/' . $this->prefixCounter . '.tar.gz';
            self::assertTrue($visit->doTrackAction($downloadUrl, 'download'));
        } else if ($pageViewType == 'site-search') {
            self::assertTrue($visit->doTrackSiteSearch($searchKeyword, $searchCategory, $this->prefixCounter));
        } else if ($pageViewType == 'event') {
            self::assertTrue($visit->doTrackEvent($searchCategory, "event name", $searchKeyword, $this->prefixCounter));
        }
    }
}
