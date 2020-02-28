<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site with a non UTC timezone and tracks a couple visits near the end of the day.
 */
class VisitsInDifferentTimezones extends Fixture
{
    public $idSite = 1;
    public $idSite2 = 2;
    public $dateTime = '2010-03-06';
    public $date;

    public function __construct()
    {
        $this->date = Date::factory($this->dateTime)->toString();
    }

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
        // tests run in UTC, the Tracker in UTC
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime, $ecommerce = 0, $siteName = 'site in EST', $siteUrl = false,
                                $siteSearch = 1, $searchKeywordParameters = null,
                                $searchCategoryParameters = null, $timezone = 'America/New_York');
        }
        if (!self::siteCreated($idSite = 2)) {
            self::createWebsite($this->dateTime, $ecommerce = 0, $siteName = 'site in UTC', $siteUrl = false,
                $siteSearch = 1, $searchKeywordParameters = null,
                $searchCategoryParameters = null, $timezone = 'UTC');
        }
    }

    private function trackVisits()
    {
        // track 2 hours before today in UTC. for utc website, there will be 1 visit yesterday, 0 today.
        // for est website, there will be 0 visit yesterday, 1 today.
        $dateTime = Date::factory('today')->subHour(2)->getDatetime();

        foreach ([$this->idSite, $this->idSite2] as $idSite) {
            $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

            // visit that is 'tomorrow' in UTC
            $t->setUrl('http://example.org/index.htm');
            self::checkResponse($t->doTrackPageView('incredible title!'));
        }
    }

    public function provideContainerConfig()
    {
        $this->setMockNow();

        return parent::provideContainerConfig();
    }

    public function setMockNow()
    {
        // set now to 1:00 am today
        $now = time();
        $now = $now - ($now % 86400);
        $now = $now + 3600;
        Date::$now = $now;
    }
}