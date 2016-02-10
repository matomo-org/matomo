<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

    public function setUp()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }

        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    private function trackVisits()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->enableBulkTracking();

        for ($numVisits = 0; $numVisits <= 30; $numVisits++) {
            $t->setForceNewVisit();
            $t->setUrl('http://example.org/my/dir/page' . ($numVisits % 4));

            $visitDateTime = Date::factory($this->dateTime)->addDay($numVisits)->getDatetime();
            $t->setForceVisitDateTime($visitDateTime);

            self::assertTrue($t->doTrackPageView('incredible title ' . ($numVisits % 3)));
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }
}