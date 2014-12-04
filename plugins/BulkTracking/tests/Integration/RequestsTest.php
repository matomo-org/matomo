<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkTracking\tests\Integration;

use Piwik\Plugins\BulkTracking\Tracker\Requests;
use Piwik\Plugins\UsersManager\API;
use Piwik\Plugins\UsersManager\UsersManager;
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

    public function setUp()
    {
        parent::setUp();

        $this->requests = new Requests();
    }

    public function tearDown()
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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage token_auth must be specified when using Bulk Tracking Import
     */
    public function test_authenticateRequests_shouldThrowAnException_IfTokenAuthIsEmpty()
    {
        $requests = array($this->buildDummyRequest());
        $this->requests->authenticateRequests($requests);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage token_auth must be specified when using Bulk Tracking Import
     */
    public function test_authenticateRequests_shouldThrowAnException_IfAnyTokenAuthIsEmpty()
    {
        $requests = array($this->buildDummyRequest($this->getSuperUserToken()), $this->buildDummyRequest());
        $this->requests->authenticateRequests($requests);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage token_auth specified does not have Admin permission for idsite=1
     */
    public function test_authenticateRequests_shouldThrowAnException_IfTokenIsNotValid()
    {
        $dummyToken = API::getInstance()->getTokenAuth('test', UsersManager::getPasswordHash('2'));
        $superUserToken = $this->getSuperUserToken();

        $requests = array($this->buildDummyRequest($superUserToken), $this->buildDummyRequest($dummyToken));
        $this->requests->authenticateRequests($requests);
    }

    public function test_authenticateRequests_shouldNotFail_IfAllTokensAreValid()
    {
        $superUserToken = $this->getSuperUserToken();

        $requests = array($this->buildDummyRequest($superUserToken), $this->buildDummyRequest($superUserToken));
        $this->requests->authenticateRequests($requests);

        $this->assertTrue(true);
    }

    public function test_authenticateRequests_shouldNotFail_IfEmptyRequestSetGiven()
    {
        $this->requests->authenticateRequests(array());

        $this->assertTrue(true);
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
