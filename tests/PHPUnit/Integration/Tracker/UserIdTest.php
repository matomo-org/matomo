<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\TrackerConfig;

/**
 @group trackerUserIdTest
 */
class UserIdTest extends IntegrationTestCase
{
    const FIRST_VISIT_TIME = '2012-01-05 00:00:00';
    const TEST_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
    const TEST_BROWSER_LANGUAGE = 'en-gb';
    const TEST_COUNTRY = 'nl';
    const TEST_REGION = '06';
    const CHANGED_USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A';
    const CHANGED_BROWSER_LANGUAGE = 'ja';
    const CHANGED_COUNTRY = 'jp';
    const CHANGED_REGION = '22';

    public function setUp(): void
    {
        parent::setUp();
        Fixture::createWebsite('2012-01-01 00:00:00');
    }

    public function test_VisitBeforeUserIdSetIsReusedAfterUserIdSet_withUserIdOverwritesVisitorId_withTrustCookies()
    {
        TrackerConfig::setConfigValue('enable_userid_overwrites_visitorid', 1);
        TrackerConfig::setConfigValue('trust_visitors_cookies', 1);
        $this->doVisitActions();
    }

    public function test_VisitBeforeUserIdSetIsReusedAfterUserIdSet_withoutUserIdOverwritesVisitorId_withTrustCookies()
    {
        TrackerConfig::setConfigValue('enable_userid_overwrites_visitorid', 0);
        TrackerConfig::setConfigValue('trust_visitors_cookies', 1);
        $this->doVisitActions();
    }

    public function test_VisitBeforeUserIdSetIsReusedAfterUserIdSet_withUserIdOverwritesVisitorId_withoutTrustCookies()
    {
        TrackerConfig::setConfigValue('enable_userid_overwrites_visitorid', 1);
        TrackerConfig::setConfigValue('trust_visitors_cookies', 0);
        $this->doVisitActions();
    }

    public function test_VisitBeforeUserIdSetIsReusedAfterUserIdSet_withoutUserIdOverwritesVisitorId_withoutTrustCookies()
    {
        TrackerConfig::setConfigValue('enable_userid_overwrites_visitorid', 0);
        TrackerConfig::setConfigValue('trust_visitors_cookies', 0);
        $this->doVisitActions();
    }

    /**
     * Simulate three actions, one without a user id, one with a user id and then one more without a user id
     * All three actions should be allocated to the same visit
     *
     * @throws \Exception
     */
    private function doVisitActions(): void
    {
        $tracker = $this->getTracker();

        // Before user id set
        $response = $tracker->doTrackPageView('Welcome');
        Fixture::checkResponse($response);
        $this->assertVisitCount(1);
        $this->assertVisitActionCount(1);

        // Set user id - same visit should be used
        $tracker->setUserId('user@example.org');
        $response = $tracker->doTrackPageView('Logged in');
        Fixture::checkResponse($response);
        $this->assertVisitCount(1);
        $this->assertVisitActionCount(2);

        // Remove user id (maybe the page didn't set it?) - same visit should be used
        $tracker->setUserId(null);
        $response = $tracker->doTrackPageView('Product page');
        Fixture::checkResponse($response);
        $this->assertVisitCount(1);
        $this->assertVisitActionCount(3);
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

    /**
     * Check visit count
     *
     * @param int $expectedVisits
     *
     * @throws \Exception
     */
    private function assertVisitCount(int $expectedVisits): void
    {
        $visitCount = Db::fetchOne("SELECT COUNT(*) FROM " . Common::prefixTable('log_visit'));
        $this->assertEquals($expectedVisits, $visitCount);
    }

    /**
     * Check visit action count
     *
     * @param int $expectedActions
     *
     * @throws \Exception
     */
    private function assertVisitActionCount(int $expectedActions): void
    {
        $actionCount = Db::fetchOne("SELECT visit_total_actions FROM " . Common::prefixTable('log_visit') .
                                    " ORDER BY idvisit DESC LIMIT 1");
        $this->assertEquals($expectedActions, $actionCount);
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

}