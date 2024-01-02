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
 * Adds one website and tracks several visits from one visitor on
 * different days that span about a month apart.
 */
class VisitsOverSeveralDays extends Fixture
{
    public $dateTimes = array(
        '2010-12-14 01:00:00',
        '2010-12-15 01:00:00',
        '2010-12-25 01:00:00',
        '2011-01-15 01:00:00',
        '2011-01-16 01:00:00',
    );

    public $idSite = 1;
    public $idSite2 = 2;
    public $forceLargeWindowLookBackForVisitor = false;

    // one per visit
    public $referrerUrls = array(
        'http://facebook.com/whatever',
        'http://www.facebook.com/another/path',
        'http://fb.me/?q=sdlfjs&n=slfjsd',
        'http://twitter.com/whatever2',
        'http://www.twitter.com/index?a=2334',
        'http://t.co/id/?y=dsfs',
        'http://www.flickr.com',
        'http://xanga.com',
        'http://skyrock.com',
        'http://mixi.jp',
    );

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
            self::createWebsite($this->dateTimes[0], $ecommerce = 0, $siteName = 'Site AAAAAA');
        }

        if (!self::siteCreated($idSite = 2)) {
            self::createWebsite($this->dateTimes[0], $ecommerce = 0, $siteName = 'SITE BBbbBB');
        }
    }

    private function trackVisits()
    {
        $dateTimes = $this->dateTimes;

        $days = 0;
        $ridx = 0;
        foreach ($dateTimes as $dateTime) {
            $days++;

            $visitor = $this->makeTracker($this->idSite, $dateTime);

            // FIRST VISIT THIS DAY
            $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.1)->getDatetime());
            $visitor->setUrl('http://example.org/homepage');
            $visitor->setUrlReferrer($this->referrerUrls[$ridx++]);
            self::checkResponse($visitor->doTrackPageView('ou pas'));

            // Test change the IP, the visit should not be split but recorded to the same idvisitor
            $visitor->setIp('200.1.15.22');

            $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.2)->getDatetime());
            $urlWithThreeSubdirectories = 'http://example.org/sub1/sub2/sub3/news';
            $visitor->setUrl($urlWithThreeSubdirectories);
            self::checkResponse($visitor->doTrackPageView('ou pas'));

            // SECOND VISIT THIS DAY
            $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(1)->getDatetime());
            $visitor->setUrl('http://example.org/news');
            $visitor->setUrlReferrer($this->referrerUrls[$ridx++]);
            self::checkResponse($visitor->doTrackPageView('ou pas'));

            if ($days <= 3) {
                $visitor = $this->makeTracker($this->idSite2, $dateTime);
                $visitor->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.1)->getDatetime());
                $visitor->setUrl('http://example.org/homepage');
                $visitor->setUrlReferrer($this->referrerUrls[$ridx - 1]);
                self::checkResponse($visitor->doTrackPageView('Second website'));
            }
        }
    }

    protected function makeTracker($idSite, $dateTime, $debugStringAppend = '')
    {
        $tracker = parent::getTracker($idSite, $dateTime, $defaultInit = true);

        if($this->forceLargeWindowLookBackForVisitor) {
            // Fakes the config value window_look_back_for_visitor tested in TrackerWindowLookBack
            $debugStringAppend .= '&forceLargeWindowLookBackForVisitor=1';

            // Here we force the visitor ID cookie value sent to piwik.php, to create a "unique visitor" for all visits in fixture
            // we do not use setVisitorId(), because we want shouldLookupOneVisitorFieldOnly() to return false for this particular test case
            $debugStringAppend .= '&_id=2f4f673d4732e11d';
        }
        $tracker->setDebugStringAppend($debugStringAppend);
        return $tracker;
    }
}
