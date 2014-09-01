<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Tracker\Visit;
use Piwik\Tests\Fixture;
use PiwikTracker;
use Exception;

/**
 * Adds one site and tracks a couple visits using a custom visitor ID.
 */
class FewVisitsWithSetVisitorId extends Fixture
{
    public $idSite = 1;
    public $dateTime = '2010-03-06 11:22:33';

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits_setVisitorId();
        $this->trackVisits_setUserId();
    }

    public function tearDown()
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        // tests run in UTC, the Tracker in UTC
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime);
        }
    }

    private function trackVisits_setVisitorId()
    {
        // total = 2 visitors, 3 page views
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        // First, some basic tests
        self::settingInvalidVisitorIdShouldThrow($t);

        // We create VISITOR A
        $t->setUrl('http://example.org/index.htm');
        $t->setVisitorId('a13b7c5a62f72dea');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // VISITOR B: few minutes later, we trigger the same tracker but with a custom visitor ID,
        // => this will create a new visit B
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.05)->getDatetime());
        $t->setUrl('http://example.org/index2.htm');
        $t->setVisitorId('f66bc315f2a01a79');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // This new visit B will have 2 page views
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(0.1)->getDatetime());
        $t->setUrl('http://example.org/index3.htm');
        self::checkResponse($t->doTrackPageView('incredible title!'));

    }

    private function trackVisits_setUserId()
    {
        // total = 2 visitors, 3 page views
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        // First, some basic tests
        self::settingInvalidUserIdShouldThrow($t);

        // A NEW VISIT
        // Setting both Visitor ID and User ID
        // -> User ID takes precedence
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2)->getDatetime());
        $t->setUrl('http://example.org/index.htm');

        // Set Visitor ID first.
        $generatedVisitorId = '6ccebef4faef4969';
        $t->setVisitorId($generatedVisitorId);
        $this->assertEquals($generatedVisitorId, $t->getVisitorId());

        // Set User ID
        $userId = 'email@example.com';
        $t->setUserId($userId);
        $this->assertEquals($userId, $t->getUserId());

        // User ID takes precedence over any previously set Visitor ID
        $hashUserId = $t->getIdHashed($userId);
        $this->assertEquals($hashUserId, $t->getVisitorId());

        // Track a pageview with this user id
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Track another pageview
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.1)->getDatetime());
        self::checkResponse($t->doTrackPageView('second page'));


        // A NEW VISIT
        // Change User ID -> This will create a new visit
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.2)->getDatetime());
        $t->setUserId('new-email@example.com');
        self::checkResponse($t->doTrackPageView('a new user id was set -> new visit'));
    }

    private static function settingInvalidVisitorIdShouldThrow(PiwikTracker $t)
    {
        try {
            $t->setVisitorId('test');
            $this->fail('should throw');
        } catch (Exception $e) {
            //OK
        }
        try {
            $t->setVisitorId('61e8');
            $this->fail('should throw');
        } catch (Exception $e) {
            //OK
        }
        try {
            $t->setVisitorId('61e8cc2d51fea26dabcabcabc');
            $this->fail('should throw');
        } catch (Exception $e) {
            //OK
        }
    }

    private static function settingInvalidUserIdShouldThrow(PiwikTracker $t)
    {
        try {
            $t->setUserId('');
            $this->fail('should throw');
        } catch (Exception $e) {
            //OK
        }
    }
}