<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Adds one website and tracks visits on different days over a month
 * using referrer URLs with search engines.
 */
class Test_Piwik_Fixture_ManyVisitsOverSeveralDaysWithSearchEngineReferrers extends Test_Piwik_BaseFixture
{
    public $today = '2010-03-06 11:22:33';
    public $idSite = 1;
    public $keywords = array(
        'free > proprietary', // testing a keyword containing >
        'peace "," not war', // testing a keyword containing ,
        'justice )(&^#%$ NOT corruption!',
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
        self::createWebsite('2010-02-01 11:22:33');
		Piwik_Goals_API::getInstance()->addGoal($this->idSite, 'triggered php', 'manually', '', '');
		Piwik_Goals_API::getInstance()->addGoal(
			$this->idSite, 'another triggered php', 'manually', '', '', false, false, true);
	}

    private function trackVisits()
    {
        $dateTime = $this->today;
        $idSite   = $this->idSite;

		$t = self::getTracker($idSite, $dateTime, $defaultInit = true);
		$t->setTokenAuth(self::getTokenAuth());
		$t->enableBulkTracking();
        for ($daysIntoPast = 30; $daysIntoPast >= 0; $daysIntoPast--)
        {
            // Visit 1: referrer website + test page views
            $visitDateTime = Piwik_Date::factory($dateTime)->subDay($daysIntoPast)->getDatetime();
            
            $t->setNewVisitorId();
            
            $t->setUrlReferrer('http://www.referrer' . ($daysIntoPast % 5) . '.com/theReferrerPage' . ($daysIntoPast % 2) . '.html');
            $t->setUrl('http://example.org/my/dir/page' . ($daysIntoPast % 4) . '?foo=bar&baz=bar');
            $t->setForceVisitDateTime($visitDateTime);
            self::assertTrue($t->doTrackPageView('incredible title ' . ($daysIntoPast % 3)));

			// Trigger goal n°1 once
			self::assertTrue($t->doTrackGoal(1));

			// Trigger goal n°2 twice
			self::assertTrue($t->doTrackGoal(2));
			$t->setForceVisitDateTime(Piwik_Date::factory($visitDateTime)->addHour(0.1)->getDatetime());
			self::assertTrue($t->doTrackGoal(2));

            // VISIT 2: search engine
            $t->setForceVisitDateTime(Piwik_Date::factory($visitDateTime)->addHour(3)->getDatetime());
            $t->setUrlReferrer('http://google.com/search?q=' . urlencode($this->keywords[$daysIntoPast % 3]));
            self::assertTrue($t->doTrackPageView('not an incredible title '));
        }
        self::checkResponse($t->doBulkTrack());
    }
}
