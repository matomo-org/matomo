<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\BulkTracking\Tracker\Requests;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\TrackerConfig;

/**
 * @group BulkTracking
 * @group RequestsTest
 * @group Plugins
 * @group Tracker
 */
class RequestsTest extends IntegrationTestCase
{
    /**
     * @var Requests
     */
    private $requests;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2014-01-02 03:04:05');

        $this->requests = new Requests();
    }

    public function tearDown(): void
    {
        // clean up your test here if needed

        parent::tearDown();
    }

    public function test_requiresAuthentication_shouldReturnTrue_IfEnabled()
    {
        $oldConfig = TrackerConfig::getConfigValue('bulk_requests_require_authentication');
        TrackerConfig::setConfigValue('bulk_requests_require_authentication', 1);

        $this->assertTrue($this->requests->requiresAuthentication());

        TrackerConfig::setConfigValue('bulk_requests_require_authentication', $oldConfig);
    }

    public function test_requiresAuthentication_shouldReturnFalse_IfDisabled()
    {
        $oldConfig = TrackerConfig::getConfigValue('bulk_requests_require_authentication');
        TrackerConfig::setConfigValue('bulk_requests_require_authentication', 0);

        $this->assertFalse($this->requests->requiresAuthentication());

        TrackerConfig::setConfigValue('bulk_requests_require_authentication', $oldConfig);
    }

    public function test_authenticateRequests_shouldThrowAnException_IfTokenAuthIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('token_auth must be specified when using Bulk Tracking Import');

        $requests = array($this->buildDummyRequest());
        $this->requests->authenticateRequests($requests);
    }

    public function test_authenticateRequests_shouldThrowAnException_IfAnyTokenAuthIsEmpty()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('token_auth must be specified when using Bulk Tracking Import');

        $requests = array($this->buildDummyRequest($this->getSuperUserToken()), $this->buildDummyRequest());
        $this->requests->authenticateRequests($requests);
    }

    public function test_authenticateRequests_shouldThrowAnException_IfTokenIsNotValid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('token_auth specified does not have Admin permission for idsite=1');

        $dummyToken = StaticContainer::get(Model::class)->generateRandomTokenAuth();
        $superUserToken = $this->getSuperUserToken();

        $requests = array($this->buildDummyRequest($superUserToken), $this->buildDummyRequest($dummyToken));
        $this->requests->authenticateRequests($requests);
    }

    public function test_authenticateRequests_shouldNotFail_IfAllTokensAreValid()
    {
        self::expectNotToPerformAssertions();

        $superUserToken = $this->getSuperUserToken();

        $requests = array($this->buildDummyRequest($superUserToken), $this->buildDummyRequest($superUserToken));
        $this->requests->authenticateRequests($requests);
    }

    public function test_authenticateRequests_shouldNotFail_IfEmptyRequestSetGiven()
    {
        self::expectNotToPerformAssertions();

        $this->requests->authenticateRequests(array());
    }

    private function getSuperUserToken()
    {
        Fixture::createSuperUser(false);
        return Fixture::getTokenAuth();
    }

    private function buildDummyRequest($token = false)
    {
        return new Request(array('idsite' => '1'), $token);
    }

}
