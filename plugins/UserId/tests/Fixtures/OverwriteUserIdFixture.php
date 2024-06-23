<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

/**
 * Generates visits with user IDs and creates the user IDs index for testing
 */
class OverwriteUserIdFixture extends Fixture
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
        $testEnv = $this->getTestEnvironment();
        $testEnv->overrideConfig('Tracker', 'enable_userid_overwrites_visitorid', 0);
        $testEnv->save();

        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->enableBulkTracking();

        foreach (array('user1', 'user2', 'user3') as $key => $userId) {
            for ($numVisits = 0; $numVisits < 2; $numVisits++) {
                $t->setUserId($userId);
                $t->setIp('10.10.10.' . ($key + 1) . $numVisits);
                // each time we have a different visitorId and it should create many unique visitors and not just a unique
                // visitor per user. We don't force a new visit so this should create multiple visits each time even though
                // visitorId is always the same
                // If userId was to overwrite the visitorId then we would only see 1 visit for each visitor, but here we see
                // multiple visits since the visitorId always changes even though userId stays the same
                $t->setVisitorId(substr(md5($numVisits . $key . $userId), 0, 16));
                $t->setUrl('http://example.org/my/dir/page' . $numVisits);

                $visitDateTime = Date::factory($this->dateTime)->addPeriod($numVisits, 'minute')->addPeriod($key, 'second')->getDatetime();
                $t->setForceVisitDateTime($visitDateTime);

                self::assertTrue($t->doTrackPageView('incredible title ' . ($numVisits % 3)));
            }
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());

        $testEnv->overrideConfig('Tracker', 'enable_userid_overwrites_visitorid', 1);
        $testEnv->save();
    }
}
