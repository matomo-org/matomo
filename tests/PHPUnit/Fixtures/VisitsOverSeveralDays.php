<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Adds one website and tracks several visits from one visitor on 
 * different days that span about a month apart.
 */
class Test_Piwik_Fixture_VisitsOverSeveralDays extends Test_Piwik_BaseFixture
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
	    self::createWebsite($this->dateTimes[0], $ecommerce = 0, $siteName = 'Site AAAAAA');
	    self::createWebsite($this->dateTimes[0], $ecommerce = 0, $siteName = 'SITE BBbbBB');
    }

    private function trackVisits()
    {
        $dateTimes = $this->dateTimes;
        $idSite    = $this->idSite;

        $i = 0;
        $ridx = 0;
        foreach ($dateTimes as $dateTime) {
            $i++;
            $visitor = self::getTracker($idSite, $dateTime, $defaultInit = true);
            // Fake the visit count cookie
            $visitor->setDebugStringAppend("&_idvc=$i");

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
            $visitor->setUrl('http://example.org/homepage');
            $visitor->setUrlReferrer($this->referrerUrls[$ridx++]);
            self::checkResponse($visitor->doTrackPageView('ou pas'));

            // Test change the IP, the visit should not be split but recorded to the same idvisitor
            $visitor->setIp('200.1.15.22');

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.2)->getDatetime());
            $visitor->setUrl('http://example.org/news');
            self::checkResponse($visitor->doTrackPageView('ou pas'));

            $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(1)->getDatetime());
            $visitor->setUrl('http://example.org/news');
            $visitor->setUrlReferrer($this->referrerUrls[$ridx++]);
            self::checkResponse($visitor->doTrackPageView('ou pas'));


	        if($i <= 3 ) {

		        $visitor = self::getTracker($this->idSite2, $dateTime, $defaultInit = true);
		        $visitor->setForceVisitDateTime(Piwik_Date::factory($dateTime)->addHour(0.1)->getDatetime());
		        $visitor->setUrl('http://example.org/homepage');
		        $visitor->setUrlReferrer($this->referrerUrls[$ridx-1]);
		        self::checkResponse($visitor->doTrackPageView('Second website'));
	        }
        }
    }
}
