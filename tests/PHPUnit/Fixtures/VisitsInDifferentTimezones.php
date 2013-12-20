<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;

/**
 * Adds one site with a non UTC timezone and tracks a couple visits near the end of the day.
 */
class Test_Piwik_Fixture_VisitsInDifferentTimezones extends Test_Piwik_BaseFixture
{
    public $idSite = 1;
    public $dateTime = '2010-03-06';
    public $date;

    public function __construct()
    {
        $this->date = Date::factory($this->dateTime)->toString();
    }

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
        // tests run in UTC, the Tracker in UTC
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime, $ecommerce = 0, $siteName = false, $siteUrl = false,
                                $siteSearch = 1, $searchKeywordParameters = null,
                                $searchCategoryParameters = null, $timezone = 'America/New_York');
        }
    }

    private function trackVisits()
    {
        $dateTime = Date::factory($this->date)->addHour(27); // tracking a visit that is tomorrow in New York time
        $idSite = $this->idSite;

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        // visit that is 'tomorrow' in UTC
        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('incredible title!'));
    }
}