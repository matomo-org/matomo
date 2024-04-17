<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Adds one site and tracks a couple conversions.
 */
class SomeVisitsAllConversions extends Fixture
{
    public $dateTime = '2009-01-04 00:11:42';
    public $idSite = 1;
    public $idGoal_OneConversionPerVisit = 1;
    public $idGoal_MultipleConversionPerVisit = 2;

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
            self::createWebsite($this->dateTime);
        }

        // First, a goal that is only recorded once per visit
        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            API::getInstance()->addGoal(
                $this->idSite,
                'triggered js ONCE',
                'title',
                'Thank you',
                'contains',
                $caseSensitive = false,
                $revenue = 10,
                $allowMultipleConversions = false
            );
        }

        // Second, a goal allowing multiple conversions
        if (!self::goalExists($idSite = 1, $idGoal = 2)) {
            API::getInstance()->addGoal(
                $this->idSite,
                'triggered js MULTIPLE ALLOWED',
                'manually',
                '',
                '',
                $caseSensitive = false,
                $revenue = 10,
                $allowMultipleConversions = true
            );
        }

        if (!self::goalExists($idSite = 1, $idGoal = 3)) {
            API::getInstance()->addGoal($this->idSite, 'click event', 'event_action', 'click', 'contains');
        }

        if (!self::goalExists($idSite = 1, $idGoal = 4)) {
            API::getInstance()->addGoal($this->idSite, 'category event', 'event_category', 'The_Category', 'exact', true, false, false, 'categorydesc');
        }

        if (!self::goalExists($idSite = 1, $idGoal = 5)) {
            // including a few characters that are HTML entitiable
            API::getInstance()->addGoal($this->idSite, 'name event', 'event_name', '<the_\'"name>', 'exact', false, false, false, 'eventdesc');
        }
    }

    private function trackVisits()
    {
        $dateTime = $this->dateTime;
        $idSite = 1;
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
        API::getInstance()->updateGoal($idSite, $idGoal_OneConversionPerVisit, $goal['name'], @$goal['match_attribute'], @$goal['pattern'], @$goal['pattern_type'], @$goal['case_sensitive'], $goal['revenue'], $goal['allow_multiple'] = 1, $goal['description']);
        self::assertTrue($goal['allow_multiple'] == 1);

        // 1st goal should Now be tracked
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.61)->getDatetime());
        self::checkResponse($t->doTrackGoal($idGoal_OneConversionPerVisit, $revenue = 656));

        // few minutes later, create a new_visit
        $t->setForceVisitDateTime(Date::factory($dateTime)->addHour(0.7)->getDatetime());
        $t->setTokenAuth($this->getTokenAuth());
        $t->setForceNewVisit();
        $t->doTrackPageView('This is tracked in a new visit.');

        // should trigger two goals at once (event_category, event_action)
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t->doTrackEvent('The_Category', 'click_action', 'name'));

        // should not trigger a goal (the_category is case senstive goal)
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.4)->getDatetime());
        self::checkResponse($t->doTrackEvent('the_category', 'click_action', 'name'));

        // should trigger a goal for event_name, including a few characters that are HTML entitiable
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.4)->getDatetime());
        self::checkResponse($t->doTrackEvent('other_category', 'other_action', '<the_\'"name>'));
    }
}
