<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;
use MatomoTracker;
use Exception;

/**
 * Adds one site and tracks a couple visits using a custom visitor ID.
 */
class FewVisitsWithSetVisitorId extends Fixture
{
    public $idSite = 1;
    public $idGoal = 1;
    public $dateTime = '2010-03-06 11:22:33';

    const USER_ID_EXAMPLE_COM = 'email@example.com';

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits_setVisitorId();
        $this->trackVisits_setUserId();

        // generate data for the period = week, month, year use cases
        $this->trackVisits_oneWeekLater_setUserId();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        // tests run in UTC, the Tracker in UTC
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime, 1);
        }
        if (!self::goalExists($this->idSite, $this->idGoal)) {
            API::getInstance()->addGoal($this->idSite, 'triggered js', 'manually', '', '');
        }
    }

    private function trackVisits_setVisitorId()
    {
        // total = 2 visitors, 3 page views
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        // First, some basic tests
        $this->settingInvalidVisitorIdShouldThrow($t);

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
        $userId = self::USER_ID_EXAMPLE_COM;
        // total = 2 visitors, 3 page views
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        // First, some basic tests
        $this->settingInvalidUserIdShouldThrow($t);

        // We create a visit with no User ID.
        // When User ID  will be set below, then it will UPDATE this visit here that starts without UserID
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1.9)->getDatetime());
        $t->setVisitorId('6be121d126d93581');
        $t->setUrl('http://example.org/no-user-id-set-but-should-appear-in-user-id-visit');
        self::checkResponse($t->doTrackPageView('no User Id set but it should appear in ' . $userId . '!'));

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
        $t->setUserId($userId);

        // User ID does not take precedence over any previously set Visitor ID
        $this->assertEquals($generatedVisitorId, $t->getVisitorId());
        $this->assertEquals($userId, $t->getUserId());

        // Track a pageview with this user id
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Track another pageview
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.1)->getDatetime());
        $this->assertEquals($userId, $t->getUserId());
        self::checkResponse($t->doTrackPageView('second page'));

        // A NEW VISIT WITH A SET USER ID
        // Change User ID -> This will create a new visit
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(2.2)->getDatetime());
        $t->setVisitorId('2f16b4d842cc294d');
        $secondUserId = 'new-email@example.com';
        $t->setUserId($secondUserId);
        self::checkResponse($t->doTrackPageView('a new user id was set -> new visit'));

        // A NEW VISIT BY THE SAME USER
        // Few hours later, the same user ID comes in from a different place and computer
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setVisitorId('7dcebef4faef4969'); // set manually so tests are not random
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(5)->getDatetime());
        // Make sure the computer and IP look really different from previous visit
        $t->setIp('67.51.31.21');
        $t->setUserAgent("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 (.NET CLR 3.5.30729)");
        $t->setBrowserLanguage('fr');
        $t->setUserId($secondUserId);
        $t->setUrl('http://example.org/home');
        self::checkResponse($t->doTrackPageView('same user id was set -> this is the same unique user'));

        // Do not pass User ID in this request, it should still attribute to previous visit
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(5.1)->getDatetime());
        self::checkResponse($t->doTrackPageView('second pageview - by this user id'));

        // Request from a different computer not yet logged in, this should not be added to our User ID session
        $t->setUserId(false);
        // make sure the Id is not so random as to not fail the test
        $t->setVisitorId('5e15b4d842cc294d');

        $t->setIp('1.2.4.7');
        $t->setUserAgent("New unique device");
        self::checkResponse($t->doTrackPageView('pageview - should not be tracked by our user id but in a new visit'));

        // User has now logged in so we measure her interactions to her User ID
        $t->setUserId($secondUserId);

        // Trigger a goal conversion
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(5.2)->getDatetime());
        self::checkResponse($t->doTrackGoal(1));

        // An ecommerce add to cart
        // (helpful to test that &segment=userId==x will return all items purchased by a specific user ID
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(5.3)->getDatetime());
        $t->setUrl('http://nsa.gov/buy/prism');
        $t->addEcommerceItem('sku-007-PRISM', 'My secret spy tech', 'Surveillance', '10000000000');
        $t->doTrackEcommerceCartUpdate(10000000000 + 500 /* add some for shipping PRISM */);
    }

    private function trackVisits_oneWeekLater_setUserId()
    {
        $oneWeekLater = Date::factory($this->dateTime)->addDay(8);

        // Set User ID to a known user id
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setVisitorId('7dcebef4faef4325'); // set manually so tests are not random
        $t->setForceVisitDateTime($oneWeekLater->getDatetime());
        $t->setUrl('http://example.org/index.htm');
        $t->setUserId(self::USER_ID_EXAMPLE_COM);
        self::checkResponse($t->doTrackPageView('Page view by ' . self::USER_ID_EXAMPLE_COM));

        // Set a new User ID not set before
        $t->setForceVisitDateTime($oneWeekLater->addHour(0.4)->getDatetime());
        $t->setUrl('http://example.org/index.htm');
        $userId = 'new-user-id@one-weeklater';
        $t->setUserId($userId);
        $t->setVisitorId('6ccebef4faef4969'); // this should not be ignored
        self::checkResponse($t->doTrackPageView('A page view by ' . $userId));
        $t->setForceVisitDateTime($oneWeekLater->addHour(0.8)->getDatetime());
    }

    private function settingInvalidVisitorIdShouldThrow(MatomoTracker $t)
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

    private function settingInvalidUserIdShouldThrow(MatomoTracker $t)
    {
        try {
            $t->setUserId('');
            $this->fail('should throw');
        } catch (Exception $e) {
            //OK
        }
    }
}
