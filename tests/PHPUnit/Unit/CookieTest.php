<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use DateTime;
use Piwik\Common;
use Piwik\Cookie;
use Piwik\SettingsPiwik;

/**
 * @group Core
 */
class CookieTest extends \PHPUnit\Framework\TestCase
{
    const TEST_COOKIE_NAME = 'fooBarTest';

    /**
     * @var Cookie
     */
    private $cookie;

    public function setUp(): void
    {
        parent::setUp();
        $this->cookie = $this->makeCookie();
    }

    public function tearDown(): void
    {
        unset($_COOKIE[self::TEST_COOKIE_NAME]);
        parent::tearDown();
    }

    private function makeCookie()
    {
        return new Cookie(self::TEST_COOKIE_NAME);
    }

    public function test_loadContentFromCookie()
    {
        $_COOKIE[self::TEST_COOKIE_NAME] = 'hello=1.2:ignore=Kg==:foo=:bar=dGVzdDp2YWx1ZQ==';
        $this->cookie                    = $this->makeCookie();
        $this->assertEquals('1.2', $this->cookie->get('hello'));
        $this->assertEquals('*', $this->cookie->get('ignore'));
        $this->assertEquals('', $this->cookie->get('foo'));
        $this->assertEquals('test:value', $this->cookie->get('bar'));
    }

    public function test_loadContentFromCookie_wontUnserialiseContentIfNotSigned()
    {
        $val                             = serialize(['foobar']);
        $_COOKIE[self::TEST_COOKIE_NAME] = 'hello=' . base64_encode($val) . ':_=foobar';
        $this->cookie                    = $this->makeCookie();
        $this->assertEquals(Common::sanitizeInputValues($val), $this->cookie->get('hello'));
    }

    public function test_loadContentFromCookie_willUnserialiseContentIfSigned()
    {
        $val                             = serialize(['foobar']);
        $cookieStr                       = 'hello=' . base64_encode($val) . ':_=';
        $_COOKIE[self::TEST_COOKIE_NAME] = $cookieStr . sha1($cookieStr . SettingsPiwik::getSalt());
        $this->cookie                    = $this->makeCookie();
        $this->assertEquals(['foobar'], $this->cookie->get('hello'));
    }

    public function test_get_set()
    {
        $this->cookie->set('ignore', '*f1');
        $this->assertEquals('*f1', $this->cookie->get('ignore'));
    }

    public function test_delete_unsetsValues()
    {
        $_COOKIE[self::TEST_COOKIE_NAME] = 'hello=1.2';
        $this->cookie                    = $this->makeCookie();
        $this->assertEquals('1.2', $this->cookie->get('hello'));

        $this->cookie->delete();

        $this->assertEquals(false, $this->cookie->get('hello'));
    }

    public function test_generateContentString_usesBase64encode_string()
    {
        $this->cookie->set('ignore', '*');
        $this->assertEquals('ignore=Kg==', $this->cookie->generateContentString());
    }

    public function test_generateContentString_usesPlainTextNumber()
    {
        $this->cookie->set('hello', '1.2');
        $this->assertEquals('hello=1.2', $this->cookie->generateContentString());

        $this->cookie->set('hello', 1.2);
        $this->assertEquals('hello=1.2', $this->cookie->generateContentString());
    }

    public function test_generateContentString_multipleFields()
    {
        $this->cookie->set('hello', '1.2');
        $this->cookie->set('ignore', '*');
        $this->cookie->set('foo', '');
        $this->cookie->set('bar', 'test:value');
        $this->assertEquals('hello=1.2:ignore=Kg==:foo=:bar=dGVzdDp2YWx1ZQ==', $this->cookie->generateContentString());
    }

    public function test_generateContentString_throwsExceptionWhenNotStringOrNumber()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only strings and numbers can be used in cookies. Value is of type array');
        $this->cookie->set('ignore', ['foo']);
        $this->cookie->generateContentString();
    }

    /**
     * Dataprovider for testJsonSerialize
     */
    public function getJsonSerializeData()
    {
        return [
            ['null', null],
            ['bool false', false],
            ['bool true', true],
            ['negative int', -42],
            ['zero', 0],
            ['positive int', 42],
            ['float', 1.25],
            ['empty string', ''],
            ['nul in string', "\0"],
            ['carriage return in string', "first line\r\nsecond line"],
            ['utf7 in string', 'hello, world'],
            ['utf8 in string', '是'],
            ['empty array', []],
            ['single element array', ["test"]],
            ['associative array', ["alpha", 2 => "beta"]],
            ['mixed keys', ['first' => 'john', 'last' => 'doe', 10 => 'age']],
            ['nested arrays', ['top' => ['middle' => 2, ['bottom'], 'last'], 'the end' => true]],
            ['array confusion', ['"', "'", '}', ';', ':']],
        ];
    }

    /**
     * @dataProvider getJsonSerializeData
     */
    public function testJsonSerialize($id, $testData)
    {
        $this->assertEquals($testData, json_decode(json_encode($testData), $assoc = true), $id);
    }

    public function test_isCookieInRequest_ReturnsTrueIfCookieExists()
    {
        $_COOKIE['abc'] = 'value';
        $this->assertTrue(Cookie::isCookieInRequest('abc'));
    }

    public function test_isCookieInRequest_ReturnsFalseIfCookieExists()
    {
        $this->assertFalse(Cookie::isCookieInRequest('abc'));
    }

    public function test_formatCookieExpire()
    {
        //assert + 30 years
        $checkTime = $this->cookie->formatExpireTime("+ 30 years");
        $years     = $this->diffInYears($checkTime);
        $this->assertTrue($years >= 29);

        // assert Empty
        $checkTime = $this->cookie->formatExpireTime();
        $years     = $this->diffInYears($checkTime);
        $this->assertTrue($years >= 1);

        // assert timestamp
        $checkTime = $this->cookie->formatExpireTime(time() + (86400 * 365 * 3));
        $years     = $this->diffInYears($checkTime);
        $this->assertTrue($years >= 2);
    }

    private function diffInYears($checkTime)
    {
        $today = new DateTime();
        $time  = DateTime::createFromFormat('l, d-M-Y H:i:s T', $checkTime);
        $diff  = $time->diff($today);
        return $diff->format('%y');
    }
}
