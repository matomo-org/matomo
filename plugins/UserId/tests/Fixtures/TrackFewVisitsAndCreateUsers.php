<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates visits with user IDs and creates the user IDs index for testing
 */
class TrackFewVisitsAndCreateUsers extends Fixture
{
    public $dateTime = '2010-02-01 11:22:33';
    public $idSite = 1;

    public function setUp(): void
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }

        $this->trackVisits();
    }

    private function trackVisits()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->enableBulkTracking();

        foreach (array('user1', 'user2', 'user3') as $key => $userId) {
            for ($numVisits = 0; $numVisits < ($key + 1) * 10; $numVisits++) {
                $t->setUserId($userId);
                $t->setVisitorId(str_pad($numVisits . $key, 16, 'a'));
                $t->setForceNewVisit();
                $t->setUrl('http://example.org/my/dir/page' . ($numVisits % 4));

                $visitDateTime = Date::factory($this->dateTime)->addDay($numVisits)->getDatetime();
                $t->setForceVisitDateTime($visitDateTime);

                self::assertTrue($t->doTrackPageView('incredible title ' . ($numVisits % 3)));

                if ($numVisits && $numVisits % 5 == 0) {
                    $visitDateTime = Date::factory($this->dateTime)->addDay($numVisits)->addHour(0.02)->getDatetime();
                    $t->setForceVisitDateTime($visitDateTime);
                    self::assertTrue($t->doTrackSiteSearch('some search term'));
                }
                if ($numVisits && $numVisits % 4 == 0) {
                    $visitDateTime = Date::factory($this->dateTime)->addDay($numVisits)->addHour(0.04)->getDatetime();
                    $t->setForceVisitDateTime($visitDateTime);
                    self::assertTrue($t->doTrackEvent('Event action', 'event cat'));
                }
            }
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }
}
