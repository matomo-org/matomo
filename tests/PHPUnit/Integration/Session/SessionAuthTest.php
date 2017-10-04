<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Integration\Session;

use Piwik\Auth\Password;
use Piwik\AuthResult;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\UsersManager\Model;
use Piwik\Session\SessionAuth;
use Piwik\Session\SessionAuthCookieFactory;
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

    public static function beforeTableDataCached()
    {
        parent::beforeTableDataCached();

        UsersManagerAPI::getInstance()->addUser(self::TEST_OTHER_USER, 'testpass', 'test@example.com');
    }

    public function setUp()
    {
        parent::setUp();

        $this->testInstance = StaticContainer::get(SessionAuth::class);
    }

    public function test_authenticate_ReturnsFailure_IfUsernameInSessionCookieIsInvalid()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, 'inavliduser');
        $this->initializeRequest(self::TEST_IP, self::TEST_UA, 'inavliduser', 'invalidtokenauth');

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfCookieUsernameDiffersFromSessionUsername()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);
        $this->initializeRequest(self::TEST_IP, self::TEST_UA, self::TEST_OTHER_USER);

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfRequestIpDiffersFromSessionIp()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);
        $this->initializeRequest('55.55.55.55', self::TEST_UA, Fixture::ADMIN_USER_LOGIN);

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfRequestUserAgentDiffersFromSessionUserAgent()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);
        $this->initializeRequest(self::TEST_IP, 'some-other-user-agent', Fixture::ADMIN_USER_LOGIN);

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function test_authenticate_ReturnsSuccess_IfRequestIpAndUserAgentMatchSession_AndCookieUsernameMatchesSessionUsername()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, self::TEST_OTHER_USER);
        $this->initializeRequest(self::TEST_IP, self::TEST_UA, self::TEST_OTHER_USER);

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::SUCCESS, $result->getCode());
    }

    public function test_authenticate_ReturnsSuperUserSuccess_IfRequestIpAndUserAgentMatchSession_AndCookieUsernameMatchesSessionUsername()
    {
        $this->initializeSession(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);
        $this->initializeRequest(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfReauthenticating_AndAuthHashIsInvalid()
    {
        // no session initialized
        $this->initializeRequest(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN, 'inavlidtokenauth');

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());

        $sessionFingerprint = new SessionFingerprint();
        $this->assertNull($sessionFingerprint->getUser());
    }

    public function test_authenticate_ReturnsSuccess_IfReauthenticating_AndAuthHashIsValid()
    {
        // no session initialized
        $this->initializeRequest(self::TEST_IP, self::TEST_UA, Fixture::ADMIN_USER_LOGIN);

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::SUCCESS_SUPERUSER_AUTH_CODE, $result->getCode());

        $sessionFingerprint = new SessionFingerprint();
        $this->assertEquals(Fixture::ADMIN_USER_LOGIN, $sessionFingerprint->getUser());
        $this->assertEquals(['ip' => self::TEST_IP, 'ua' => self::TEST_UA], $sessionFingerprint->getUserInfo());
    }

    private function initializeRequest($ip, $userAgent, $userName, $tokenAuth = false)
    {
        if (empty($tokenAuth)) {
            $model = new Model();
            $user = $model->getUser($userName);
            $tokenAuth = $user['token_auth'];
        }

        $sessionCookieFactory = StaticContainer::get(SessionAuthCookieFactory::class);
        $passwordHelper = new Password();

        $cookie = $sessionCookieFactory->getCookie(false);
        $cookie->set('id', $passwordHelper->hash($tokenAuth));
        $cookie->set('user', $userName);

        $cookieName = Config::getInstance()->General['login_cookie_name'];
        $_COOKIE[$cookieName] = $cookie->generateContentString();

        $_SERVER['REMOTE_ADDR'] = $ip;
        $_SERVER['HTTP_USER_AGENT'] = $userAgent;
    }

    private function initializeSession($ip, $userAgent, $userLogin)
    {
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($userLogin, $ip, $userAgent);
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);

        $fixture->createSuperUser = true;
    }
}
