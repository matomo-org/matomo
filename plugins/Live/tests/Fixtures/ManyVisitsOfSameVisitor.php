<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates many visits for the same visitor
 */
class ManyVisitsOfSameVisitor extends Fixture
{
    public $dateTime = '2010-02-01 11:22:33';
    public $idSite = 1;
    public $idSite2 = 2;

    public function setUp(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime);
        }

        if (!self::siteCreated($this->idSite2)) {
            self::createWebsite($this->dateTime);
        }

        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function trackVisits()
    {
        $t = self::getTracker($this->idSite2, $this->dateTime, $defaultInit = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->setForceNewVisit();
        $t->setUserId(101);
        $t->setUrl('http://example.org/foo/dir/page');

        $visitDateTime = Date::factory($this->dateTime)->getDatetime();
        $t->setForceVisitDateTime($visitDateTime);

        self::checkResponse($t->doTrackPageView('incredible title'));


        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->enableBulkTracking();

        // -2 because we want to make sure to have 3 visits for the first day
        for ($numVisits = -2; $numVisits <= 30; $numVisits++) {
            $t->setForceNewVisit();
            $t->setUrl('http://example.org/my/dir/page' . ($numVisits % 4));

            if ($numVisits > 0) {
                $visitDateTime = Date::factory($this->dateTime)->addDay($numVisits)->getDatetime();
                $t->setForceVisitDateTime($visitDateTime);
            } else {
                $visitDateTime = Date::factory($this->dateTime)->subHour(-$numVisits / 10)->getDatetime();
                $t->setForceVisitDateTime($visitDateTime);
            }

            self::assertTrue($t->doTrackPageView('incredible title ' . ($numVisits % 3)));

            if ($numVisits === -2) {
                for ($k = 0; $k < 10; $k++) {
                    // we generate many actions to make sure in the test when we segment by page title that it not just
                    // returns one visit but multiple visits to ensure the group by is correct
                    self::assertTrue($t->doTrackPageView('incredible title 1'));
                }
            }
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }
}
