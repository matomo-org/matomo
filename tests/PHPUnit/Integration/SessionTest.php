<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Http;
use Piwik\Session;
use Piwik\Tests\Framework\Fixture;

/**
 * @group Core
 * @group Session
 * @group SessionTest
 */
class SessionTest extends \PHPUnit\Framework\TestCase
{
    public function testSessionShouldNotBeStartedIfItWasAlreadyStarted()
    {
        $url = Fixture::getRootUrl() . '/tests/resources/sessionStarter.php';
        $result = Http::sendHttpRequest($url, 5);
        $this->assertSame('ok', trim($result));
    }

    /**
     * @dataProvider getCookieTests
     */
    public function testWriteCookie($expected, $name, $value, $expires, $path, $domain, $secure, $httpOnly, $sameSite)
    {
        $result = Session::writeCookie($name, $value, $expires, $path, $domain, $secure, $httpOnly, $sameSite);
        $this->assertEquals($expected, $result);
    }

    public function getCookieTests()
    {
        return [
            [
                'Set-Cookie: myname=myvalue; expires=Tue, 03-May-2022 02:27:34 GMT; path=/; domain=my.test.domain; secure; httponly; SameSite=lax',
                'myname', 'myvalue', 1651544854, '/', 'my.test.domain', true, true, 'lax'
            ],
            [
                'Set-Cookie: myname=myvalue; expires=Tue, 03-May-2022 02:27:34 GMT; path=/; domain=my.test.domain; httponly; SameSite=none',
                'myname', 'myvalue', 1651544854, '/', 'my.test.domain', false, true, 'none'
            ],
            [
                'Set-Cookie: %3Cxss%3Emyname%26%24=my%3Cxss%3E%27%24%25value; expires=Tue, 03-May-2022 02:27:34 GMT; path=/; domain=ma%3Cf0r3%24%25%25.tld; SameSite=lax',
                '<xss>myname&$', 'my<xss>\'$%value', 1651544854, '/', 'ma<f0r3$%%.tld', false, false, 'lax'
            ],
            [
                'Set-Cookie: myname=myvalue; expires=Tue, 03-May-2022 02:27:34 GMT; path=/; domain=my.test.domain',
                'myname', 'myvalue', 1651544854, '/', 'my.test.domain', false, false, ''
            ],
            [
                'Set-Cookie: myname=myvalue; expires=Tue, 03-May-2022 02:27:34 GMT; path=/',
                'myname', 'myvalue', 1651544854, '/', '', false, false, ''
            ],
            [
                'Set-Cookie: myname=myvalue; path=/',
                'myname', 'myvalue', 0, '/', '', false, false, ''
            ],
            [
                'Set-Cookie: myname=myvalue',
                'myname', 'myvalue', 0, '', '', false, false, ''
            ],
        ];
    }
}
