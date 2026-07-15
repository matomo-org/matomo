<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Config;
use Piwik\Http;
use ReflectionMethod;

/**
 * @group Core
 */
class HttpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getProxyConfigurationTestData
     */
    public function testgetProxyConfiguration($url, $proxyConfiguration, $expected)
    {
        $getProxyConfiguration = new ReflectionMethod('\\Piwik\\Http', 'getProxyConfiguration');

        Config::getInstance()->proxy['host'] = $proxyConfiguration[0];
        Config::getInstance()->proxy['port'] = $proxyConfiguration[1];
        Config::getInstance()->proxy['username'] = '';
        Config::getInstance()->proxy['password'] = '';
        Config::getInstance()->proxy['exclude'] = $proxyConfiguration[2];

        $this->assertEquals($expected, $getProxyConfiguration->invoke(new Http(), $url));
    }

    public function getProxyConfigurationTestData()
    {
        return array(
            array('http://localhost/', array('', '', ''), array(null, null, null, null)),
            array('http://localhost/', array('localhost', '8080', ''), array(null, null, null, null)),
            array('http://example.com/', array('', '', ''), array('', '', '', '')),
            array('http://example.com/', array('localhost', '8080', ''), array('localhost', '8080', '', '')),
            array('http://example.com/', array('localhost', '8080', ''), array('localhost', '8080', '', '')),
            array('http://example.com/', array('localhost', '8080', 'example.com'), array(null, null, null, null)),
            // Ensure that accidental whitespace is ignored
            array('http://example.com/', array('localhost', '8080', ' example.com '), array(null, null, null, null)),
            array('http://example.com/', array('localhost', '8080', 'a.example.com,b.example.net'), array('localhost', '8080', '', '')),
        );
    }

    /**
     * @dataProvider getResolveRedirectUrlTestData
     */
    public function testResolveRedirectUrl($baseUrl, $location, $expected)
    {
        $method = new ReflectionMethod('\\Piwik\\Http', 'resolveRedirectUrl');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertSame($expected, $method->invoke(null, $baseUrl, $location));
    }

    public function getResolveRedirectUrlTestData()
    {
        return array(
            // absolute URL is used as-is
            'absolute' => array('http://a.example/x/y', 'https://b.example/z', 'https://b.example/z'),
            // protocol-relative keeps the base scheme
            'protocol relative' => array('https://a.example/x/y', '//b.example/z', 'https://b.example/z'),
            // absolute-path replaces the whole path, keeps host and port
            'absolute path' => array('https://a.example:8443/x/y', '/z/w', 'https://a.example:8443/z/w'),
            // relative reference resolves against the base directory
            'relative' => array('https://a.example/x/y', 'z', 'https://a.example/x/z'),
            'relative from dir' => array('https://a.example/x/', 'z', 'https://a.example/x/z'),
            // documented RFC 3986 limitation: a query-only reference resolves against the base
            // directory instead of retaining the full base path. Still same host, still re-validated.
            'query only (documented limitation)' => array('https://a.example/x/y', '?p=2', 'https://a.example/x/?p=2'),
        );
    }

    /**
     * @dataProvider getUrlsSameOriginTestData
     */
    public function testUrlsSameOrigin($urlA, $urlB, $expected)
    {
        $method = new ReflectionMethod('\\Piwik\\Http', 'urlsSameOrigin');

        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertSame($expected, $method->invoke(null, $urlA, $urlB));
    }

    public function getUrlsSameOriginTestData()
    {
        return array(
            'identical origin' => array('https://a.example/x', 'https://a.example/y', true),
            'same origin case-insensitive host' => array('https://A.Example/x', 'https://a.example/y', true),
            'same origin explicit default port' => array('https://a.example/x', 'https://a.example:443/y', true),
            'different host' => array('https://a.example/x', 'https://b.example/y', false),
            'subdomain differs' => array('https://a.example/x', 'https://sub.a.example/y', false),
            // scheme downgrade keeps the host but must count as a different origin (cleartext exposure)
            'scheme downgrade https to http' => array('https://a.example/x', 'http://a.example/y', false),
            // same host, different port is a different origin
            'different port' => array('https://a.example/x', 'https://a.example:8443/y', false),
            // parse failure or missing scheme fails closed
            'missing scheme in target' => array('https://a.example/x', '//a.example/y', false),
        );
    }
}
