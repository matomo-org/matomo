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
use Piwik\Http\EgressBlockedException;
use ReflectionMethod;

/**
 * @group Core
 */
class HttpTest extends \PHPUnit\Framework\TestCase
{
    /** @var array|null */
    private $originalProxyConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalProxyConfig = Config::getInstance()->proxy;
    }

    protected function tearDown(): void
    {
        // Several tests mutate the proxy config, restore it so nothing leaks into later tests.
        Config::getInstance()->proxy = $this->originalProxyConfig;

        parent::tearDown();
    }

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

    public function testSendHttpRequestByWithEgressValidationRejectsNonCurlTransport()
    {
        $this->expectException(EgressBlockedException::class);
        $this->expectExceptionMessage('SSRF-safe HTTP requests require the curl transport.');

        $this->sendEgressValidatedRequest('socket', 'http://example.com/');
    }

    public function testSendHttpRequestByWithEgressValidationRejectsNonHttpScheme()
    {
        $originalProtocols = Config::getInstance()->General['allowed_outgoing_protocols'] ?? 'http,https';
        // allow ftp through the generic protocol check so the SSRF-safe scheme guard is what rejects it
        Config::getInstance()->General['allowed_outgoing_protocols'] = 'http,https,ftp';

        try {
            $this->sendEgressValidatedRequest('curl', 'ftp://example.com/');
            $this->fail('Expected an exception for a non-http scheme');
        } catch (EgressBlockedException $e) {
            $this->assertSame('SSRF-safe HTTP requests only support the http and https schemes.', $e->getMessage());
        } finally {
            Config::getInstance()->General['allowed_outgoing_protocols'] = $originalProtocols;
        }
    }

    public function testSendHttpRequestByWithEgressValidationRejectsConfiguredProxy()
    {
        Config::getInstance()->proxy['host'] = 'proxy.example';
        Config::getInstance()->proxy['port'] = '8080';
        Config::getInstance()->proxy['username'] = '';
        Config::getInstance()->proxy['password'] = '';
        Config::getInstance()->proxy['exclude'] = '';

        $this->expectException(EgressBlockedException::class);
        $this->expectExceptionMessage('SSRF-safe HTTP requests cannot be routed through a configured proxy.');

        $this->sendEgressValidatedRequest('curl', 'http://example.com/');
    }

    public function testSendHttpRequestByWithEgressValidationRejectsPrivateIpTarget()
    {
        $this->expectException(EgressBlockedException::class);
        $this->expectExceptionMessage('Refusing to fetch: host resolves to a private or reserved address.');

        $this->sendEgressValidatedRequest('curl', 'http://10.0.0.1/');
    }

    private function sendEgressValidatedRequest($transport, $url)
    {
        return Http::sendHttpRequestBy(
            $transport,
            $url,
            5,
            null,
            null,
            null,
            0,
            false,
            false,
            false,
            false,
            'GET',
            null,
            null,
            null,
            array(),
            null,
            false, // $checkHostIsAllowed: skip the container-based blocklist in a unit test
            true // $validateEgressIp
        );
    }

    /**
     * @dataProvider getUrlsSameOriginTestData
     */
    public function testUrlsSameOrigin($urlA, $urlB, $expected)
    {
        $method = new ReflectionMethod('\\Piwik\\Http', 'urlsSameOrigin');

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
