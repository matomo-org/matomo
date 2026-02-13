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
use Piwik\Db;
use Piwik\Plugins\UsersManager\UserUpdater;
use Piwik\Session\SessionAuth;
use Piwik\Session\SessionFingerprint;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Session;
use Piwik\Session\SaveHandler\DbTable;

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

    public function testAuthenticateReturnsFailureIfNoSessionExists()
    {
        $this->initializeSession(Fixture::ADMIN_USER_LOGIN);

        $this->destroySession();

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());
    }

    public function testAuthenticateReturnsFailureIfAuthenticatedSessionAndPasswordChangedAfterSessionCreated()
    {
        $this->initializeSession(self::TEST_OTHER_USER);

        sleep(1);

        $userUpdater = new UserUpdater();
        $userUpdater->updateUserWithoutCurrentPassword(self::TEST_OTHER_USER, 'testpass2');

        $result = $this->testInstance->authenticate();
        $this->assertEquals(AuthResult::FAILURE, $result->getCode());

        $this->assertEmpty($_SESSION, 'Expected $_SESSION to be empty. Instead got: ' . var_export($_SESSION, true));
    }

    public function testAuthenticateReturnsFailureIfUsersModelReturnsIncorrectUser()
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
    public function testAuthenticateReturnsSuccessIfUserDataHasNoPasswordModifiedTimestamp()
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

    public function testAuthenticateReturnsFailureIfSessionIsExpiredWhenRememberMeUsed()
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

    public function testAuthenticateReturnsFailureIfSessionIsExpiredWhenRememberMeNotUsed()
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

    public function testDestroyAllSessions()
    {
        $this->initializeSession(self::TEST_OTHER_USER);

        $tableConfig = Session::getDbTableConfig();
        $dbTable = new DbTable($tableConfig);

        $numberOfSessions = 5;

        for ($i = 0; $i < $numberOfSessions; $i++) {
            $dbTable->write("testId$i", 'testSessionData');
        }

        $this->assertSame($numberOfSessions, $this->countActiveSessions());
        $this->assertNotEmpty($_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]);

        Session::destroyAllSessions();
        $this->assertSame(0, $this->countActiveSessions());
        $this->assertEmpty($_SESSION);
    }

    private function countActiveSessions(): int
    {
        $tableConfig = Session::getDbTableConfig();
        $sql = "SELECT count(id) as count FROM `" . $tableConfig['name'] . "`";
        $res = Db::fetchOne($sql, []);
        return (int) $res;
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

    public function getUser($userLogin, $pending = false): array
    {
        return $this->userData;
    }
}
