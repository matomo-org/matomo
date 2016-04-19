<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\UserId\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\UserId\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates visits with user IDs and creates the user IDs index for testing
 */
class TrackFewVisitsAndCreateUsers extends Fixture
{
    public $dateTime = '2010-02-01 11:22:33';
    public $idSite = 1;

    public function setUp()
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
            for ($numVisits = 0; $numVisits < ($key+1) * 10; $numVisits++) {
                $t->setUserId($userId);
                if ($numVisits % 5 == 0) {
                    $t->doTrackSiteSearch('some search term');
                }
                if ($numVisits % 4 == 0) {
                    $t->doTrackEvent('Event action', 'event cat');
                }
                $t->setForceNewVisit();
                $t->setUrl('http://example.org/my/dir/page' . ($numVisits % 4));

                $visitDateTime = Date::factory($this->dateTime)->addDay($numVisits)->getDatetime();
                $t->setForceVisitDateTime($visitDateTime);

                self::assertTrue($t->doTrackPageView('incredible title ' . ($numVisits % 3)));
            }
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }
}