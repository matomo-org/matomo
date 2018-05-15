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
 * Adds one site and 1000 actions for every day of one month (January). Each
 * action uses a distinct URL. The site has two goals, one that is converted on
 * each visit, and another that matches half of all visits.
 */
class Piwik_Test_Fixture_OneSiteThousandsOfDistinctUrlsOverMonth
{
    public $date = '2010-01-01';
    public $period = 'month';
    public $idSite = 1;

    public function setUp()
    {
        // add one site
        Fixture::createWebsite(
            $this->date, $ecommerce = 1, $siteName = "Site #0", $siteUrl = "http://whatever.com/");

        // add two goals
        $goals = API::getInstance();
        $goals->addGoal($this->idSite, 'all', 'url', 'http', 'contains', false, 5);
        $goals->addGoal($this->idSite, 'all', 'url', 'thing2', 'contains');

        $start = Date::factory($this->date);

        $dates = array();
        for ($day = 0; $day != 31; ++$day) {
            $dates[] = $start->addDay($day);
        }

        $t = BenchmarkTestCase::getLocalTracker($this->idSite);

        $actionNum = 0;
        foreach ($dates as $date) {
            for ($visitNum = 0; $visitNum != 1000; ++$visitNum) {
                if ($visitNum % 2 == 0) {
                    $url = "http://whatever.com/$actionNum/0/1/2/3/4/5/6/7/8/9";
                    $referrerUrl = "http://google.com/?q=$actionNum";
                } else {
                    $url = "http://whatever.com/thing2/$actionNum/0/1/2/3/4/5/6/7/8/9";
                    $referrerUrl = "http://";
                }
                $title = "A page title / $actionNum / 0 / 1 / 2 / 3 / 4 / 5 / 6 / 7 / 8 /9";

                $t->setNewVisitorId();
                $t->setForceVisitDateTime($date);

                $t->setUrl($url);
                $t->setUrlReferrer($referrerUrl);
                Fixture::checkResponse($t->doTrackPageView($title));
                ++$actionNum;
            }
        }
    }
}
