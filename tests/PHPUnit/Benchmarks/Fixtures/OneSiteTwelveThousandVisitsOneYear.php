<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\BenchmarkTestCase;

/**
 * Reusable fixture. Tracks twelve thousand page views over a year for one site.
 */
class Piwik_Test_Fixture_OneSiteTwelveThousandVisitsOneYear
{
    public $date = '2010-01-01';
    public $period = 'year';
    public $idSite = 1;
    public $idGoal1 = 1;
    public $idGoal2 = 2;

    public function setUp()
    {
        // add one site
        Fixture::createWebsite(
            $this->date, $ecommerce = 1, $siteName = "Site #0", $siteUrl = "http://whatever.com/");

        // add two goals
        $goals = API::getInstance();
        $goals->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
        $goals->addGoal($this->idSite, 'all', 'url', 'http', 'contains');

        $urls = array();
        for ($i = 0; $i != 3; ++$i) {
            $url = "http://whatever.com/" . ($i - 1) . "/" . ($i + 1);
            $title = "page view " . ($i - 1) . " / " . ($i + 1);
            $urls[$url] = $title;
        }

        $visitTimes = array();
        $date = Date::factory($this->date);
        for ($month = 0; $month != 12; ++$month) {
            for ($day = 0; $day != 25; ++$day) {
                $visitTimes[] = $date->addPeriod($month, 'MONTH')->addDay($day)->getDatetime();
            }
        }

        // add 12,000 visits (1 visit a day from 40 visitors for 25 days of every month) w/ 3 pageviews each
        foreach ($visitTimes as $visitTime) {
            for ($visitor = 0; $visitor != 40; ++$visitor) {
                $t = BenchmarkTestCase::getLocalTracker($this->idSite);

                $ip = "157.5.6." . ($visitor + 1);
                $t->setIp($ip);
                $t->setNewVisitorId();
                $t->setForceVisitDateTime($visitTime);

                foreach ($urls as $url => $title) {
                    $t->setUrl($url);
                    $t->doTrackPageView($title);
                }
            }
        }
    }
}
