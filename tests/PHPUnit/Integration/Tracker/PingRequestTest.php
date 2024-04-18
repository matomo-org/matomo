<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\Goals\API as GoalsAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class PingRequestTest extends IntegrationTestCase
{
    public const FIRST_VISIT_TIME = '2012-01-05 00:00:00';
    public const TEST_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
    public const TEST_BROWSER_LANGUAGE = 'en-gb';
    public const TEST_COUNTRY = 'nl';
    public const TEST_REGION = '06';
    public const CHANGED_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A';
    public const CHANGED_BROWSER_LANGUAGE = 'ja';
    public const CHANGED_COUNTRY = 'jp';
    public const CHANGED_REGION = '22';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2012-01-01 00:00:00');
        GoalsAPI::getInstance()->addGoal(1, 'Goal 1 - Thank you', 'title', $matchPattern = 'pong', 'contains', $caseSensitive = false, $revenue = 10, $allowMultipleConversions = 1);
    }

    public function test_PingWithinThirtyMinutes_ExtendsExistingVisitAndLastAction_WithoutNewAction()
    {
        $tracker = $this->getTracker();

        // track initial action
        $response = $tracker->doTrackPageView('pong');
        Fixture::checkResponse($response);
        $this->assertInitialVisitIsCorrect();

        // send a ping request within 30 minutes
        $pingTime = '2012-01-05 00:20:00';
        $this->doPingRequest($tracker, $pingTime, $setNewDimensionValues = false);

        $this->assertInitialVisitIsNotExtended(self::FIRST_VISIT_TIME, $checkModifiedDimensions = false, 1201);
    }

    public function test_PingWithinThirtyMinutes_AndChangedDimensionValues_ExtendsExistingVisit_AndChangesAppropriateDimensions()
    {
        $tracker = $this->getTracker();

        // track initial action
        $response = $tracker->doTrackPageView('pong');
        Fixture::checkResponse($response);
        $this->assertInitialVisitIsCorrect();

        // send a ping request within 30 minutes
        $pingTime = '2012-01-05 00:20:00';
        $this->doPingRequest($tracker, $pingTime, $setNewDimensionValues = true);

        $this->assertInitialVisitIsNotExtended(self::FIRST_VISIT_TIME, $checkModifiedDimensions = true, 1201);
    }

    public function test_PingWithinThirtyMinutes_DoesNotTriggerGoalConversion()
    {
        $tracker = $this->getTracker();

        // track initial action
        $response = $tracker->doTrackPageView('pong');
        Fixture::checkResponse($response);
        $this->assertInitialVisitIsCorrect();

        // send a ping request within 30 minutes
        $pingTime = '2012-01-05 00:20:00';

        // force trigger a goal within the ping request
        $tracker->setDebugStringAppend('&idgoal=1');
        $this->doPingRequest($tracker, $pingTime, $setNewDimensionValues = true);

        $this->assertInitialVisitIsNotExtended(self::FIRST_VISIT_TIME, $checkModifiedDimensions = true, 1201);
        $this->assertGoalConversionCount(1);
    }

    public function test_PingAfterThirtyMinutes_DoesNotCreateNewVisit()
    {
        $tracker = $this->getTracker();

        // track initial action
        $response = $tracker->doTrackPageView('pong');
        Fixture::checkResponse($response);
        $this->assertInitialVisitIsCorrect();

        // send a ping request after 30 minutes
        $pingTime = '2012-01-05 00:40:00';
        $this->doPingRequest($tracker, $pingTime, $setNewDimensionValues = false);

        $this->assertPingDidNotCreateNewVisit(self::FIRST_VISIT_TIME, $checkModifiedDimensions = false);
    }

    public function test_PingAfterThirtyMinutes_AndChangedDimensionValues_DoesNotCreateNewVisit()
    {
        $tracker = $this->getTracker();

        // track initial action
        $response = $tracker->doTrackPageView('pong');
        Fixture::checkResponse($response);
        $this->assertInitialVisitIsCorrect();

        // send a ping request after 30 minutes
        $pingTime = '2012-01-05 00:40:00';

        $this->doPingRequest($tracker, $pingTime, $setNewDimensionValues = true);

        $this->assertPingDidNotCreateNewVisit(self::FIRST_VISIT_TIME, $checkModifiedDimensions = true);
    }

    private function getTracker()
    {
        $tracker = Fixture::getTracker(1, self::FIRST_VISIT_TIME, $defaultInit = true, $useLocalTracker = true);
        $tracker->setTokenAuth(Fixture::getTokenAuth());

        // properties that cannot be changed on next action
        $tracker->setUserAgent(self::TEST_USER_AGENT);
        $tracker->setBrowserLanguage(self::TEST_BROWSER_LANGUAGE);

        // properties that can be changed on next action
        $tracker->setCountry(self::TEST_COUNTRY);
        $tracker->setRegion(self::TEST_REGION);

        return $tracker;
    }

    private function assertVisitCount($expected)
    {
        $visitCount = Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('log_visit'));
        $this->assertEquals($expected, $visitCount);
    }

    private function assertActionCount($expected)
    {
        $visitCount = Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('log_link_visit_action'));
        $this->assertEquals($expected, $visitCount);
    }

    private function assertGoalConversionCount($expected)
    {
        $visitCount = Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('log_conversion'));
        $this->assertEquals($expected, $visitCount);
    }

    private function getVisitLastActionTime($idVisit)
    {
        return $this->getVisitProperty('visit_last_action_time', $idVisit);
    }

    private function getLatestActionTime($idVisit)
    {
        return Db::fetchOne("SELECT MAX(server_time) FROM " . Common::prefixTable('log_link_visit_action') . " WHERE idvisit = ?", array($idVisit));
    }

    private function getVisitTotalTime($idVisit)
    {
        return Db::fetchOne("SELECT MAX(visit_total_time) FROM " . Common::prefixTable('log_visit') . " WHERE idvisit = ?", array($idVisit));
    }

    private function assertInitialVisitIsCorrect()
    {
        $this->assertVisitCount(1);
        $this->assertActionCount(1);
        $this->assertGoalConversionCount(1);
        $this->assertEquals(0, $this->getVisitTotalTime($idVisit = 1));

        $this->assertVisitPropertiesAreUnchanged($idVisit = 1);
    }

    private function getVisitProperty($columnName, $idVisit)
    {
        return Db::fetchOne("SELECT $columnName FROM " . Common::prefixTable('log_visit') . " WHERE idvisit = ?", array($idVisit));
    }

    private function doPingRequest(\MatomoTracker $tracker, $pingTime, $changeDimensionValues)
    {
        if ($changeDimensionValues) {
            $tracker->setUserAgent(self::CHANGED_USER_AGENT);
            $tracker->setBrowserLanguage(self::CHANGED_BROWSER_LANGUAGE);
            $tracker->setCountry(self::CHANGED_COUNTRY);
            $tracker->setRegion(self::CHANGED_REGION);
        }

        $tracker->setForceVisitDateTime($pingTime);
        $response = $tracker->doPing();
        Fixture::checkResponse($response);
        return $response;
    }

    private function assertInitialVisitIsNotExtended($firstActionTime, $checkPropertiesModified, $expectedTotalTime)
    {
        $this->assertVisitCount(1);
        $this->assertActionCount(1);
        $this->assertGoalConversionCount(1);

        $visitEndTime = $this->getVisitLastActionTime($idVisit = 1);
        $this->assertEquals($firstActionTime, $visitEndTime);

        $actionTime = $this->getLatestActionTime($idVisit = 1);
        $this->assertEquals($firstActionTime, $actionTime);

        $visitTotalTime = $this->getVisitTotalTime($idVisit = 1);
        $this->assertEquals($expectedTotalTime, $visitTotalTime);

        if ($checkPropertiesModified) {
            $this->assertVisitPropertiesAreChanged($idVisit = 1, $checkUnchangeable = false);
        } else {
            $this->assertVisitPropertiesAreUnchanged($idVisit = 1);
        }
    }

    private function assertVisitPropertiesAreUnchanged($idVisit)
    {
        $browserName = $this->getVisitProperty('config_browser_name', $idVisit);
        $this->assertEquals('CH', $browserName);

        $browserLanguage = $this->getVisitProperty('location_browser_lang', $idVisit);
        $this->assertEquals(self::TEST_BROWSER_LANGUAGE, $browserLanguage);

        $country = $this->getVisitProperty('location_country', $idVisit);
        $this->assertEquals(self::TEST_COUNTRY, $country);

        $region = $this->getVisitProperty('location_region', $idVisit);
        $this->assertEquals(self::TEST_REGION, $region);
    }

    private function assertVisitPropertiesAreChanged($idVisit, $checkUnchangeableDimensions)
    {
        // browser name & browser language cannot be changed
        if ($checkUnchangeableDimensions) {
            $browserName = $this->getVisitProperty('config_browser_name', $idVisit);
            $this->assertEquals('SF', $browserName);

            $browserLanguage = $this->getVisitProperty('location_browser_lang', $idVisit);
            $this->assertEquals(self::CHANGED_BROWSER_LANGUAGE, $browserLanguage);
        }

        // region and country cannot be changed
        $country = $this->getVisitProperty('location_country', $idVisit);
        $this->assertEquals(self::CHANGED_COUNTRY, $country);

        $region = $this->getVisitProperty('location_region', $idVisit);
        $this->assertEquals(self::CHANGED_REGION, $region);
    }

    private function assertPingDidNotCreateNewVisit($expectedFirstVisitTime, $checkPropertiesModified)
    {
        $this->assertVisitCount(1);
        $this->assertActionCount(1);

        $firstVisitEndTime = $this->getVisitLastActionTime($idVisit = 1);
        $this->assertEquals($expectedFirstVisitTime, $firstVisitEndTime);

        $firstVisitActionTime = $this->getLatestActionTime($idVisit = 1);
        $this->assertEquals($expectedFirstVisitTime, $firstVisitActionTime);
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
