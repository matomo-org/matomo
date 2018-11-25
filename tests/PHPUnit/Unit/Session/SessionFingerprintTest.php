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
        $this->testInstance->initialize('testuser', true, self::TEST_TIME_VALUE);

        $this->assertEquals('testuser', $_SESSION[SessionFingerprint::USER_NAME_SESSION_VAR_NAME]);
        $this->assertEquals(
            ['ts' => self::TEST_TIME_VALUE, 'remembered' => true],
            $_SESSION[SessionFingerprint::SESSION_INFO_SESSION_VAR_NAME]
        );
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
