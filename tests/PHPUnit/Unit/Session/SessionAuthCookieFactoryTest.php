<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Unit\Session;

use Piwik\Cookie;
use Piwik\Session\SessionAuthCookieFactory;

class SessionAuthCookieFactoryTest extends \PHPUnit_Framework_TestCase
{
    const COOKIE_NAME = 'test_auth';
    const COOKIE_VALID_TIME = '60';
    const COOKIE_PATH = '';
    const TEST_NOW = 1000;

    /**
     * @var SessionAuthCookieFactory
     */
    private $testInstance;

    public function setUp()
    {
        parent::setUp();

        $this->testInstance = new SessionAuthCookieFactory(
            self::COOKIE_NAME, self::COOKIE_VALID_TIME, self::COOKIE_PATH, self::TEST_NOW);
    }

    public function test_getCookie_CreatesCorrectlyConfiguredCookieInstance()
    {
        $expectedCookieStr = 'COOKIE test_auth, rows count: 0, cookie size = 0 bytes, path: , expire: 1060
array (
)';

        $cookie = $this->testInstance->getCookie(true);
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals($expectedCookieStr, $cookie->__toString());
    }

    public function test_getCookie_CreatesCookieWithImmediateExpiry_IfRememberMeIsFalse()
    {
        $expectedCookieStr = 'COOKIE test_auth, rows count: 0, cookie size = 0 bytes, path: , expire: 0
array (
)';

        $cookie = $this->testInstance->getCookie(false);
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals($expectedCookieStr, $cookie->__toString());
    }

    public function test_isCookieInRequest_ReturnsTrue_IfConfiguredCookieIsPresent()
    {
        $_COOKIE[self::COOKIE_NAME] = 'sldkjfsdf';
        $this->assertTrue($this->testInstance->isCookieInRequest());
    }

    public function test_isCookieInRequest_ReturnsFalse_IfConfiguredCookieIsNotPresent()
    {
        $this->assertFalse($this->testInstance->isCookieInRequest());
    }
}
