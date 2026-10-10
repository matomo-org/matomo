<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Session;

use Piwik\AuthResult;
use Piwik\Date;
use Piwik\Piwik;
use Piwik\Plugins\Login\Auth;
use Piwik\Plugins\UsersManager\API as UsersManagerAPI;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Session\SessionInitializer;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class SessionInitializerTest extends IntegrationTestCase
{
    public const TEST_USER = 'sessioninituser';

    /**
     * @var string[]
     */
    private $successfulLogins = [];

    public function setUp(): void
    {
        parent::setUp();

        UsersManagerAPI::getInstance()->addUser(self::TEST_USER, 'sessioninit-Password1!', 'sessioninit@example.com');

        $this->successfulLogins = [];
        Piwik::addAction('Login.authenticate.successful', function ($login) {
            $this->successfulLogins[] = $login;
        });
    }

    public function testInitSessionPostsLoginOfPasswordAuthentication()
    {
        $auth = new Auth();
        $auth->setLogin(self::TEST_USER);
        $auth->setPassword('sessioninit-Password1!');

        (new TestSessionInitializer())->initSession($auth);

        $this->assertSame([self::TEST_USER], $this->successfulLogins);
    }

    public function testInitSessionPostsTokenOwnerLoginWhenAuthenticatingWithTokenOnly()
    {
        $model = new UsersModel();
        $token = $model->generateRandomTokenAuth();
        $model->addTokenAuth(self::TEST_USER, $token, 'test', Date::now()->getDatetime());

        // no login is set, only the token
        $auth = new Auth();
        $auth->setTokenAuth($token);

        (new TestSessionInitializer())->initSession($auth);

        $this->assertSame([self::TEST_USER], $this->successfulLogins);
    }
}

class TestSessionInitializer extends SessionInitializer
{
    protected function regenerateSessionId()
    {
        // no session in CLI tests
    }

    protected function processSuccessfulSession(AuthResult $authResult)
    {
        // skip session fingerprint and access reload, only the posted events are under test
    }
}
