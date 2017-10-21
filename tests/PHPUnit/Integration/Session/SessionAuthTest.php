<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\Session;

use Piwik\AuthResult;
use Piwik\Container\StaticContainer;
use Piwik\Session\SessionAuth;
use Piwik\Session\SessionFingerprint;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;

class SessionAuthTest extends IntegrationTestCase
{
    const TEST_IP = '11.22.33.44';
    const TEST_UA = 'test-user-agent';
    const TEST_OTHER_USER = 'testuser';

    /**
     * @var SessionAuth
     */
    private $testInstance;

    public function setUp()
    {
        parent::setUp();

        UsersManagerAPI::getInstance()->addUser(self::TEST_OTHER_USER, 'testpass', 'test@example.com');

        $this->testInstance = StaticContainer::get(SessionAuth::class);
    }

    public function test_authenticate_ReturnsFailure_IfRequestIpDiffersFromSessionIp()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);
        $this->initializeRequest('55.55.55.55', self::TEST_UA);

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfRequestUserAgentDiffersFromSessionUserAgent()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);
        $this->initializeRequest(self::TEST_IP, 'some-other-user-agent');

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function test_authenticate_ReturnsSuccess_IfRequestIpAndUserAgentMatchSession()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, self::TEST_OTHER_USER);
        $this->initializeRequest(self::TEST_IP, self::TEST_UA);

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::SUCCESS, $result->getCode());
    }

    public function test_authenticate_ReturnsSuperUserSuccess_IfRequestIpAndUserAgentMatchSession()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);
        $this->initializeRequest(self::TEST_IP, self::TEST_UA);

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfNoSessionExists()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);
        $this->initializeRequest(self::TEST_IP, self::TEST_UA);

        $this->destroySession();

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfAuthenticatedSession_AndPasswordChangedAfterSessionCreated()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, self::TEST_OTHER_USER);
        $this->initializeRequest(self::TEST_IP, self::TEST_UA);

        sleep(1);

        UsersManagerAPI::getInstance()->updateUser(self::TEST_OTHER_USER, 'testpass2');

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());

        $this->assertEmpty($_SESSION);
    }

    private function initializeRequest($ip, $userAgent)
    {
        $_SERVER['REMOTE_ADDR'] = $ip;
        $_SERVER['HTTP_USER_AGENT'] = $userAgent;
    }

    private function initializeSession($ip, $userAgent, $userLogin)
    {
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($userLogin, $time = null, $ip, $userAgent);
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->createSuperUser = true;
    }

    private function destroySession()
    {
        unset($_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]);
        unset($_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME]);
    }

    public function provideContainerConfig()
    {
        return [
            SessionAuth::class => \DI\object()
                ->constructorParameter('shouldDestroySession', false),
        ];
    }
}
