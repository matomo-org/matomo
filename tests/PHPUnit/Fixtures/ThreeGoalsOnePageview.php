<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that adds one site with three goals and tracks one pageview & one manual
 * goal conversion.
 */
class ThreeGoalsOnePageview extends Fixture
{
    public $dateTime = '2009-01-04 00:11:42';
    public $idSite = 1;
    public $idGoal = 1;
    public $idGoal2 = 2;
    public $idGoal3 = 3;

    public function setUp(): void
    {
        Fixture::createSuperUser();
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
            self::createWebsite($this->dateTime, $ecommerce = 1);
        }

        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            API::getInstance()->addGoal(
                $this->idSite,
                'Goal 1 - Thank you',
                'title',
                'Thank you',
                'contains',
                $caseSensitive = false,
                $revenue = 10,
                $allowMultipleConversions = 1
            );
        }

        if (!self::goalExists($idSite = 1, $idGoal = 2)) {
            API::getInstance()->addGoal(
                $this->idSite,
                'Goal 2 - Hello',
                'url',
                'hellow',
                'contains',
                $caseSensitive = false,
                $revenue = 10,
                $allowMultipleConversions = 0
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
        $t->setUrl('http://example.org/index.htm?ignore_referrer=1');
        $t->setUrlReferrer('http://www.example.org/page/'); // this should be ignored due to the `ignore_referrer` parameter in page url
        self::checkResponse($t->doTrackPageView('0'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.3)->getDatetime());
        self::checkResponse($t->doTrackGoal($this->idGoal3, $revenue = 42.256));
    }
}
