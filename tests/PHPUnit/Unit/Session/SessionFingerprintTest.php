<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Unit\Session;


use Piwik\Date;
use Piwik\Session\SessionFingerprint;
use Piwik\Tests\Framework\Fixture;

class SessionFingerprintTest extends \PHPUnit\Framework\TestCase
{
    public const TEST_TIME_VALUE = 4567;

    /**
     * @var SessionFingerprint
     */
    private $testInstance;

    public function setUp(): void
    {
        parent::setUp();

        $this->testInstance = new SessionFingerprint();
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

    public function test_getUser_ReturnsUserNameSessionVar_WhenSessionVarIsSet()
    {
        $_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME] = 'testuser';
        $this->assertEquals('testuser', $this->testInstance->getUser());
    }

    public function test_getUser_ReturnsNull_WhenSessionVarIsNotSet()
    {
        $this->assertNull($this->testInstance->getUser());
    }

    public function test_getUserInfo_ReturnsUserInfoSessionVar_WhenSessionVarIsSet()
    {
        $sessionVarValue = [
            'ip' => 'someip',
        ];

        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = $sessionVarValue;
        $this->assertEquals($sessionVarValue, $this->testInstance->getUserInfo());
    }

    public function test_getUserInfo_ReturnsNull_WhenSessionVarIsNotSet()
    {
        $this->assertNull($this->testInstance->getUserInfo());
    }

    public function test_initialize_SetsSessionVarsToCurrentRequest()
    {
        $this->testInstance->initialize('testuser', Fixture::ADMIN_USER_TOKEN, true, self::TEST_TIME_VALUE);

        $this->assertEquals('testuser', $_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME]);
        $this->assertEquals(Fixture::ADMIN_USER_TOKEN, $_SESSION[SessionFingerprint::SESSION_INFO_TEMP_TOKEN_AUTH]);
        $this->assertEquals(
            ['ts' => self::TEST_TIME_VALUE, 'remembered' => true, 'expiration' => self::TEST_TIME_VALUE + 3600],
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]
        );
    }

    public function test_initialize_hasVerifiedTwoFactor()
    {
        $this->testInstance->initialize('testuser', Fixture::ADMIN_USER_TOKEN, self::TEST_TIME_VALUE);

        // after logging in, the user has by default not verified two factor, important
        $this->assertFalse($this->testInstance->hasVerifiedTwoFactor());

        $this->testInstance->setTwoFactorAuthenticationVerified();

        $this->assertTrue($this->testInstance->hasVerifiedTwoFactor());
    }

    public function test_updateSessionExpireTime_SetsANewExpirationTime()
    {
        $this->testInstance->initialize('testuser', Fixture::ADMIN_USER_TOKEN, false, self::TEST_TIME_VALUE);

        Date::$now = self::TEST_TIME_VALUE + 100;

        $this->testInstance->updateSessionExpirationTime();

        $this->assertEquals(
            self::TEST_TIME_VALUE + 3700,
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]['expiration']
        );
    }

    public function test_getSessionStartTime_ReturnsCorrectValue()
    {
        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = [
            'ts' => 123,
        ];
        $this->assertEquals(123, $this->testInstance->getSessionStartTime());
    }

    public function test_getSessionStartTime_ReturnsNull_IfThereIsNoSessionInfo()
    {
        $this->assertNull($this->testInstance->getSessionStartTime());
    }

    public function test_getSessionStartTime_ReturnsNull_IfThereIsNoSessionStartTime()
    {
        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = [];
        $this->assertNull($this->testInstance->getSessionStartTime());
    }

    public function test_destroy_RemovesSessionFingerprintSessionVars()
    {
        $_SESSION['someotherdata'] = 'somedata';
        $_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME] = 'someuser';
        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = [
            'some' => 'data',
        ];

        $this->testInstance->clear();

        $this->assertEquals(['someotherdata' => 'somedata'], $_SESSION);
    }
}
