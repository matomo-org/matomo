<?php
/**
 * Matomo - free/libre analytics platform
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
            self::createWebsite(
                $this->dateTime,
                $ecommerce = 0,
                $siteName = 'site in AST',
                $siteUrl = false,
                $siteSearch = 1,
                $searchKeywordParameters = null,
                $searchCategoryParameters = null,
                $timezone = 'America/Barbados' /* AST = UTC-4 */
            );
        }
        if (!self::siteCreated($idSite = 2)) {
            self::createWebsite(
                $this->dateTime,
                $ecommerce = 0,
                $siteName = 'site in UTC',
                $siteUrl = false,
                $siteSearch = 1,
                $searchKeywordParameters = null,
                $searchCategoryParameters = null,
                $timezone = 'UTC'
            );
        }
    }

    private function trackVisits()
    {
        // This will add a visit for every hour from the yesterday 3:00 to today 12:00 in UTC
        // As we fake the now timestamp to yesterday 12:00, this means it's actually the day before yesterday to yesterday.
        // The resulting reports should have
        // 21 visits yesterday and 13 today in UTC time (in total 34)
        // 24 visits yesterday and 9 visits today in AST timezone (in total 33)
        for ($i = 3; $i <= 36; $i++) {
            $dateTime = Date::factory('yesterday')->addHour($i)->getDatetime();

            foreach ([$this->idSite, $this->idSite2] as $idSite) {
                $t = self::getTracker($idSite, $dateTime, $defaultInit = true);
                $t->setUrl('http://example.org/index.htm');
                self::checkResponse($t->doTrackPageView('incredible title!'));
            }
        }
    }

    public function provideContainerConfig()
    {
        $this->setMockNow();

        return parent::provideContainerConfig();
    }

    public function setMockNow()
    {
        // set now to 12:00 yesterday
        $now = time();
        $now = $now - ($now % 86400) - 86400;
        $now = $now + (12 * 3600);
        Date::$now = $now;
    }
}
