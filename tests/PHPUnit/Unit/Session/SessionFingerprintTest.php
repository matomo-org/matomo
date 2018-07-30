<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Unit\Session;


use Piwik\Session\SessionFingerprint;

class SessionFingerprintTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TIME_VALUE = 4567;

    /**
     * @var SessionFingerprint
     */
    private $testInstance;

    public function setUp()
    {
        parent::setUp();

        $this->testInstance = new SessionFingerprint();
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
            'ua' => 'someua',
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
        $_SERVER['HTTP_USER_AGENT'] = 'test-user-agent';
        $this->testInstance->initialize('testuser', self::TEST_TIME_VALUE);

        $this->assertEquals('testuser', $_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME]);
        $this->assertEquals(
            ['ts' => self::TEST_TIME_VALUE, 'ua' => 'test-user-agent'],
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]
        );
    }

    public function test_initialize_DoesNotSetUserAgent_IfUserAgentIsNotInHttpRequest()
    {
        unset($_SERVER['HTTP_USER_AGENT']);
        $this->testInstance->initialize('testuser', self::TEST_TIME_VALUE);

        $this->assertEquals('testuser', $_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME]);
        $this->assertEquals(
            ['ts' => self::TEST_TIME_VALUE, 'ua' => null],
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]
        );
    }

    /**
     * @dataProvider getTestDataForIsMatchingCurrentRequest
     */
    public function test_isMatchingCurrentRequest_ChecksIfSessionVarsMatchRequest(
        $sessionUa, $requestUa, $expectedResult
    ) {
        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = [
            'ua' => $sessionUa,
        ];

        $_SERVER['HTTP_USER_AGENT'] = $requestUa;

        $this->assertEquals($expectedResult, $this->testInstance->isMatchingCurrentRequest());
    }

    public function getTestDataForIsMatchingCurrentRequest()
    {
        return [
            ['test ua', 'test ua', true],
            ['nontest ua', 'test ua', false],
            [null, 'test ua', false],
        ];
    }

    public function test_isMatchingCurrentRequest_ReturnsFalse_IfUserInfoSessionVarDoesNotExist()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'test-ua';

        $this->assertEquals(false, $this->testInstance->isMatchingCurrentRequest());
    }

    public function test_isMatchingCurrentRequest_ReturnsFalse_IfRequestDetailsDoNotExist()
    {
        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = [
            'ua' => 'test-ua',
        ];

        $this->assertEquals(false, $this->testInstance->isMatchingCurrentRequest());
    }

    public function test_getSessionStartTime_()
    {
        $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME] = [
            'ts' => 123.
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
