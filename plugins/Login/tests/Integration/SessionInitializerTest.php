<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Auth;
use Piwik\AuthResult;
use Piwik\Container\StaticContainer;
use Piwik\Cookie;
use Piwik\Plugins\Login\SessionInitializer;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Since the original SessionInitializer is still in use, it needs to
 * work. These light tests ensure it's still working.
 */
class SessionInitializerTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        // AuthResult is in Auth.php, so we need to make sure that class gets loaded
        // by loading Auth.
        StaticContainer::get(Auth::class);
    }

    public function test_initSession_CreatesCookie_WhenAuthenticationIsSuccessful()
    {
        $this->assertAuthCookieIsAbsent();

        $sessionInitializer = new TestSessionInitializer();
        $sessionInitializer->initSession($this->makeMockAuth(AuthResult::SUCCESS), true);

        $this->assertAuthCookieIsCreated($sessionInitializer->cookie);
    }

    public function test_initSession_DeletesCookie_WhenAuthenticationFailed()
    {
        $this->createAuthCookie();

        try {
            $sessionInitializer = new TestSessionInitializer();
            $sessionInitializer->initSession($this->makeMockAuth(AuthResult::FAILURE), true);

            $this->fail('Expected exception to be thrown.');
        } catch (\Exception $ex) {
            // empty
        }

        $this->assertAuthCookieIsDeleted($sessionInitializer->cookie);
    }

    private function makeMockAuth($resultCode)
    {
        return new MockAuth($resultCode);
    }

    private function assertAuthCookieIsAbsent()
    {
        $this->assertArrayNotHasKey('piwik_auth', $_COOKIE);
    }

    private function assertAuthCookieIsCreated(Cookie $cookie)
    {
        $this->assertContains('login=czo5OiJ0ZXN0bG9naW4iOw==:token_auth=czozMjoiOWU5MDYxZjk2MDI0YTY3NWFmOGFkNWZmNmNiZGY2ZGMiOw==',
            $cookie->generateContentString());
    }

    private function createAuthCookie()
    {
        $_COOKIE['piwik_auth'] = 'login=czo5OiJ0ZXN0bG9naW4iOw==:token_auth=czozMjoiOWU5MDYxZjk2MDI0YTY3NWFmOGFkNWZmNmNiZGY2ZGMiOw==';
    }

    private function assertAuthCookieIsDeleted(Cookie $cookie)
    {
        $this->assertEquals('', $cookie->generateContentString());
    }
}

class TestSessionInitializer extends SessionInitializer
{
    /**
     * @var Cookie
     */
    public $cookie;

    protected function regenerateSessionId()
    {
        // empty
    }

    protected function getAuthCookie($rememberMe)
    {
        $this->cookie = parent::getAuthCookie($rememberMe);
        return $this->cookie;
    }
}

class MockAuth implements Auth
{
    private $result;

    public function __construct($resultCode)
    {
        $this->result = new AuthResult($resultCode, 'testlogin', 'dummytokenauth');
    }

    public function getName()
    {
        // empty
    }

    public function setTokenAuth($token_auth)
    {
        // empty
    }

    public function getLogin()
    {
        // empty
    }

    public function getTokenAuthSecret()
    {
        // empty
    }

    public function setLogin($login)
    {
        // empty
    }

    public function setPassword($password)
    {
        // empty
    }

    public function setPasswordHash($passwordHash)
    {
        // empty
    }

    public function authenticate()
    {
        return $this->result;
    }
}