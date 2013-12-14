<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;
use Piwik\Plugins\Goals\API;

/**
 * Fixture that adds one site with three goals and tracks one pageview & one manual
 * goal conversion.
 */
class Test_Piwik_Fixture_ThreeGoalsOnePageview extends Test_Piwik_BaseFixture
{
    public $dateTime = '2009-01-04 00:11:42';
    public $idSite = 1;
    public $idGoal = 1;
    public $idGoal2 = 2;
    public $idGoal3 = 3;

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
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime, $ecommerce = 1);
        }

        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            API::getInstance()->addGoal(
                $this->idSite, 'Goal 1 - Thank you', 'title', 'Thank you', 'contains', $caseSensitive = false,
                $revenue = 10, $allowMultipleConversions = 1
            );
        }

        if (!self::goalExists($idSite = 1, $idGoal = 2)) {
            API::getInstance()->addGoal(
                $this->idSite, 'Goal 2 - Hello', 'url', 'hellow', 'contains', $caseSensitive = false,
                $revenue = 10, $allowMultipleConversions = 0
            );
        }

        if (!self::goalExists($idSite = 1, $idGoal = 3)) {
            API::getInstance()->addGoal($this->idSite, 'triggered js', 'manually', '', '');
        }
    }

    private function trackVisits()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        // Record 1st page view
        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t->doTrackGoal($this->idGoal3, $revenue = 42.256));
    }
}
