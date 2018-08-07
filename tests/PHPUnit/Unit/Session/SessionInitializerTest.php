<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Unit\Session;

use Piwik\Auth;
use Piwik\AuthResult;
use Piwik\Session\SessionFingerprint;
use Piwik\Session\SessionInitializer;

class SessionInitializerTest extends \PHPUnit_Framework_TestCase
{
    private $oldUnitTestValue;

    public function setUp()
    {
        parent::setUp();

        $this->oldUnitTestValue = \Zend_Session::$_unitTestEnabled;
        \Zend_Session::$_unitTestEnabled = true;
    }

    public function tearDown()
    {
        \Zend_Session::$_unitTestEnabled = $this->oldUnitTestValue;

        parent::tearDown();
    }

    /**
     * @dataProvider getTestDataForInitSession
     * @expectedExceptionMessage Login_LoginPasswordNotCorrect
     */
    public function test_initSession_Throws_IfAuthenticationFailed($rememberMe)
    {
        $sessionInitializer = new TestSessionInitializer();
        $sessionInitializer->initSession($this->makeMockAuth(AuthResult::SUCCESS), $rememberMe);
    }

    /**
     * @dataProvider getTestDataForInitSession
     */
    public function test_initSession_InitializesTheSessionCorrectly_IfAuthenticationSucceeds($rememberMe)
    {
        $sessionInitializer = new TestSessionInitializer();
        $sessionInitializer->initSession($this->makeMockAuth(AuthResult::SUCCESS), $rememberMe);

        $this->assertSessionCreatedCorrectly();
    }

    public function getTestDataForInitSession()
    {
        return [
            [true],
            [false],
        ];
    }

    private function makeMockAuth($resultCode)
    {
        return new MockAuth($resultCode);
    }

    private function assertSessionCreatedCorrectly()
    {
        $fingerprint = new SessionFingerprint();
        $this->assertEquals('testlogin', $fingerprint->getUser());
        $this->assertNotEmpty($fingerprint->getSessionStartTime());
        $this->assertEquals(['ts', 'ua'], array_keys($fingerprint->getUserInfo()));
    }
}

class TestSessionInitializer extends SessionInitializer
{
    protected function regenerateSessionId()
    {
        // empty
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