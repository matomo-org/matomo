<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Date;
use Piwik\Plugins\Goals\API;

/**
 * Adds one site and tracks a couple conversions.
 */
class Piwik_Test_Fixture_SomeVisitsAllConversions extends Test_Piwik_BaseFixture
{
    public $dateTime = '2009-01-04 00:11:42';
    public $idSite = 1;
    public $idGoal_OneConversionPerVisit = 1;
    public $idGoal_MultipleConversionPerVisit = 2;

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
            self::createWebsite($this->dateTime);
        }

        // First, a goal that is only recorded once per visit
        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            API::getInstance()->addGoal(
                $this->idSite, 'triggered js ONCE', 'title', 'Thank you', 'contains', $caseSensitive = false,
                $revenue = 10, $allowMultipleConversions = false
            );
        }

        // Second, a goal allowing multiple conversions
        if (!self::goalExists($idSite = 1, $idGoal = 2)) {
            API::getInstance()->addGoal(
                $this->idSite, 'triggered js MULTIPLE ALLOWED', 'manually', '', '', $caseSensitive = false,
                $revenue = 10, $allowMultipleConversions = true
            );
        }
    }

    private function trackVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = $this->idSite;
        $idGoal_OneConversionPerVisit = $this->idGoal_OneConversionPerVisit;
        $idGoal_MultipleConversionPerVisit = $this->idGoal_MultipleConversionPerVisit;

        $t = self::getTracker($idSite, $dateTime, $defaultInit = true);

        // Record 1st goal, should only have 1 conversion
        $t->setUrl('http://example.org/index.htm');
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t->doTrackPageView('Thank you mate'));
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.4)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal_OneConversionPerVisit, $revenue = 10000000));

        // Record 2nd goal, should record both conversions
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.5)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal_MultipleConversionPerVisit, $revenue = 300));
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.6)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal_MultipleConversionPerVisit, $revenue = 366));

        // Update & set to not allow multiple
        $goals = API::getInstance()->getGoals($idSite);
        $goal = $goals[$idGoal_OneConversionPerVisit];
        self::assertTrue($goal['allow_multiple'] == 0);
        API::getInstance()->updateGoal($idSite, $idGoal_OneConversionPerVisit, $goal['name'], @$goal['match_attribute'], @$goal['pattern'], @$goal['pattern_type'], @$goal['case_sensitive'], $goal['revenue'], $goal['allow_multiple'] = 1);
        self::assertTrue($goal['allow_multiple'] == 1);

        // 1st goal should Now be tracked
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.61)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal_OneConversionPerVisit, $revenue = 656));
    }
}
