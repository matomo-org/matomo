<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Session;

use Piwik\Config;
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

    private $originalIdleTimeout;

    public function setUp(): void
    {
        parent::setUp();

        $this->testInstance = new SessionFingerprint();
        $this->originalIdleTimeout = Config::getInstance()->General['login_session_not_remembered_idle_timeout'] ?? null;
    }

    public function tearDown(): void
    {
        Date::$now = null;
        Config::getInstance()->General['login_session_not_remembered_idle_timeout'] = $this->originalIdleTimeout;

        parent::tearDown();
    }

    public function testGetUserReturnsUserNameSessionVarWhenSessionVarIsSet()
    {
        $_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME] = 'testuser';
        $this->assertEquals('testuser', $this->testInstance->getUser());
    }

    public function testGetUserReturnsNullWhenSessionVarIsNotSet()
    {
        $this->assertNull($this->testInstance->getUser());
    }

    public function testGetUserInfoReturnsUserInfoSessionVarWhenSessionVarIsSet()
    {
        $sessionVarValue = [
            'ip' => 'someip',
        ];

        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = $sessionVarValue;
        $this->assertEquals($sessionVarValue, $this->testInstance->getUserInfo());
    }

    public function testGetUserInfoReturnsNullWhenSessionVarIsNotSet()
    {
        $this->assertNull($this->testInstance->getUserInfo());
    }

    public function testInitializeSetsSessionVarsToCurrentRequest()
    {
        $this->testInstance->initialize('testuser', Fixture::ADMIN_USER_TOKEN, true, self::TEST_TIME_VALUE);

        $this->assertEquals('testuser', $_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME]);
        $this->assertEquals(Fixture::ADMIN_USER_TOKEN, $_SESSION[SessionFingerprint::SESSION_INFO_TEMP_TOKEN_AUTH]);
        $this->assertEquals(
            ['ts' => self::TEST_TIME_VALUE, 'remembered' => true, 'expiration' => self::TEST_TIME_VALUE + 3600],
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]
        );
    }

    public function testInitializeHasVerifiedTwoFactor()
    {
        $this->testInstance->initialize('testuser', Fixture::ADMIN_USER_TOKEN, self::TEST_TIME_VALUE);

        // after logging in, the user has by default not verified two factor, important
        $this->assertFalse($this->testInstance->hasVerifiedTwoFactor());

        $this->testInstance->setTwoFactorAuthenticationVerified();

        $this->assertSame('testuser', $this->testInstance->getVerifiedTwoFactorUser());
        $this->assertTrue($this->testInstance->hasVerifiedTwoFactor());
    }

    public function testHasVerifiedTwoFactorReturnsFalseWhenVerifiedForDifferentSessionUser()
    {
        $this->testInstance->initialize('testuser', Fixture::ADMIN_USER_TOKEN, self::TEST_TIME_VALUE);
        $this->testInstance->setTwoFactorAuthenticationVerified('testuser');

        $_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME] = 'otheruser';

        $this->assertFalse($this->testInstance->hasVerifiedTwoFactor());
    }

    public function testInitializeClearsVerifiedTwoFactorUser()
    {
        $this->testInstance->initialize('testuser', Fixture::ADMIN_USER_TOKEN, self::TEST_TIME_VALUE);
        $this->testInstance->setTwoFactorAuthenticationVerified('testuser');

        $this->testInstance->initialize('otheruser', Fixture::ADMIN_USER_TOKEN, self::TEST_TIME_VALUE);

        $this->assertFalse($this->testInstance->hasVerifiedTwoFactor());
        $this->assertNull($this->testInstance->getVerifiedTwoFactorUser());
    }

    public function testUpdateSessionExpireTimeSetsANewExpirationTime()
    {
        $this->testInstance->initialize('testuser', Fixture::ADMIN_USER_TOKEN, false, self::TEST_TIME_VALUE);

        Date::$now = self::TEST_TIME_VALUE + 100;

        $this->testInstance->updateSessionExpirationTime();

        $this->assertEquals(
            self::TEST_TIME_VALUE + 3700,
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]['expiration']
        );
    }

    public function testUpdateSessionExpirationTimeKeepsStoredValueWhenItBarelyMoved()
    {
        $this->setStoredSessionInfo(self::TEST_TIME_VALUE + 3600);

        Date::$now = self::TEST_TIME_VALUE + 59;

        $this->testInstance->updateSessionExpirationTime();

        $this->assertEquals(
            self::TEST_TIME_VALUE + 3600,
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]['expiration']
        );
    }

    public function testUpdateSessionExpirationTimeStoresValueOnceItMovedFarEnough()
    {
        $this->setStoredSessionInfo(self::TEST_TIME_VALUE + 3600);

        Date::$now = self::TEST_TIME_VALUE + SessionFingerprint::EXPIRATION_WRITE_THRESHOLD;

        $this->testInstance->updateSessionExpirationTime();

        $this->assertEquals(
            self::TEST_TIME_VALUE + SessionFingerprint::EXPIRATION_WRITE_THRESHOLD + 3600,
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]['expiration']
        );
    }

    public function testUpdateSessionExpirationTimeStoresValueWhenNoneIsStoredYet()
    {
        $this->setStoredSessionInfo(null);

        Date::$now = self::TEST_TIME_VALUE;

        $this->testInstance->updateSessionExpirationTime();

        $this->assertEquals(
            self::TEST_TIME_VALUE + 3600,
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]['expiration']
        );
    }

    public function testUpdateSessionExpirationTimeStoresAShorterExpirationStraightAway()
    {
        // a stored value further out than the current window has to be brought back in, otherwise
        // the session would stay usable for longer than it is configured to
        $this->setStoredSessionInfo(self::TEST_TIME_VALUE + 1209600);

        Date::$now = self::TEST_TIME_VALUE;

        $this->testInstance->updateSessionExpirationTime();

        $this->assertEquals(
            self::TEST_TIME_VALUE + 3600,
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]['expiration']
        );
    }

    public function testUpdateSessionExpirationTimeClampsTheThresholdForAShortIdleTimeout()
    {
        // 300s window means a 15s threshold, not the default 60s
        Config::getInstance()->General['login_session_not_remembered_idle_timeout'] = 300;
        $this->setStoredSessionInfo(self::TEST_TIME_VALUE + 300);

        Date::$now = self::TEST_TIME_VALUE + 10;
        $this->testInstance->updateSessionExpirationTime();
        $this->assertEquals(
            self::TEST_TIME_VALUE + 300,
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]['expiration'],
            'a 10s move should still be skipped'
        );

        Date::$now = self::TEST_TIME_VALUE + 15;
        $this->testInstance->updateSessionExpirationTime();
        $this->assertEquals(
            self::TEST_TIME_VALUE + 15 + 300,
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]['expiration'],
            'a 15s move should be stored'
        );
    }

    private function setStoredSessionInfo($expiration)
    {
        $sessionInfo = [
            'ts' => self::TEST_TIME_VALUE,
            'remembered' => false,
        ];

        if ($expiration !== null) {
            $sessionInfo['expiration'] = $expiration;
        }

        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = $sessionInfo;
    }

    public function testGetSessionStartTimeReturnsCorrectValue()
    {
        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = [
            'ts' => 123,
        ];
        $this->assertEquals(123, $this->testInstance->getSessionStartTime());
    }

    public function testGetSessionStartTimeReturnsNullIfThereIsNoSessionInfo()
    {
        $this->assertNull($this->testInstance->getSessionStartTime());
    }

    public function testGetSessionStartTimeReturnsNullIfThereIsNoSessionStartTime()
    {
        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = [];
        $this->assertNull($this->testInstance->getSessionStartTime());
    }

    public function testDestroyRemovesSessionFingerprintSessionVars()
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
