<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Session;

use Piwik\AuthResult;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Session\SessionAuth;
use Piwik\Session\SessionFingerprint;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model as UsersModel;

class SessionAuthTest extends IntegrationTestCase
{
    public const TEST_OTHER_USER = 'testuser';

    /**
     * @var SessionAuth
     */
    private $testInstance;

    public function setUp(): void
    {
        parent::setUp();

        UsersManagerAPI::getInstance()->addUser(self::TEST_OTHER_USER, 'testpass', 'test@example.com');

        $this->testInstance = StaticContainer::get(SessionAuth::class);
    }

    public function test_authenticate_ReturnsFailure_IfNoSessionExists()
    {
        $this->initializeSession(Fixture::ADMIN_USER_LOGIN);

        $this->destroySession();

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfAuthenticatedSession_AndPasswordChangedAfterSessionCreated()
    {
        $this->initializeSession(self::TEST_OTHER_USER);

        sleep(1);

        $userUpdater = new UserUpdater();
        $userUpdater->updateUserWithoutCurrentPassword(self::TEST_OTHER_USER, 'testpass2');

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());

        $this->assertEmpty($_SESSION);
    }

    public function test_authenticate_ReturnsFailure_IfUsersModelReturnsIncorrectUser()
    {
        $this->initializeSession(self::TEST_OTHER_USER);

        $sessionAuth = new SessionAuth(new MockUsersModel([
            'login' => 'wronguser',
        ]));
        $result = $sessionAuth->authenticate();

        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    /**
     * @runInSeparateProcess
     */
    public function test_authenticate_ReturnsSuccess_IfUserDataHasNoPasswordModifiedTimestamp()
    {
        $this->initializeSession(self::TEST_OTHER_USER);

        $sessionFingerprint = new SessionFingerprint();
        $expireTime = $sessionFingerprint->getExpirationTime();
        $this->assertNotNull($expireTime);

        $usersModel = new UsersModel();
        $user = $usersModel->getUser(self::TEST_OTHER_USER);
        unset($user['ts_password_modified']);

        sleep(1);

        $sessionAuth = new SessionAuth(new MockUsersModel($user));
        $result = $sessionAuth->authenticate();

        $this->assertGreaterThan($expireTime, $sessionFingerprint->getExpirationTime());

        $this->assertEquals(AuthResult::SUCCESS, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfSessionIsExpiredWhenRememberMeUsed()
    {
        Date::$now = strtotime('2012-02-03 04:55:44');
        $this->initializeSession(self::TEST_OTHER_USER, true);

        Date::$now = strtotime('2012-03-03 04:55:44');

        $usersModel = new UsersModel();
        $user = $usersModel->getUser(self::TEST_OTHER_USER);

        $sessionAuth = new SessionAuth(new MockUsersModel($user));
        $result = $sessionAuth->authenticate();

        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function test_authenticate_ReturnsFailure_IfSessionIsExpiredWhenRememberMeNotUsed()
    {
        Date::$now = strtotime('2012-02-03 04:55:44');
        $this->initializeSession(self::TEST_OTHER_USER);

        Date::$now = strtotime('2012-02-04 04:56:44');

        $usersModel = new UsersModel();
        $user = $usersModel->getUser(self::TEST_OTHER_USER);

        $sessionAuth = new SessionAuth(new MockUsersModel($user));
        $result = $sessionAuth->authenticate();

        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    private function initializeSession($userLogin, $isRemembered = false)
    {
        $sessionFingerprint = new SessionFingerprint();
        $sessionFingerprint->initialize($userLogin, Fixture::getTokenAuth(), $isRemembered);
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
            SessionAuth::class => \Piwik\DI::autowire()
                ->constructorParameter('shouldDestroySession', false),
        ];
    }
}

class MockUsersModel extends UsersModel
{
    /**
     * @var array
     */
    private $userData;

    public function __construct(array $userData)
    {
        parent::__construct();
        $this->userData = $userData;
    }

    public function getUser($userLogin, $pending = false)
    {
        return $this->userData;
    }
}
