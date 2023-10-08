<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Config;
use Piwik\Url;

/**
 * @backupGlobals enabled
 * @group Core
 * @group UrlTest
 */
class UrlTest extends \PHPUnit\Framework\TestCase
{
    public function testAllMethods()
    {
        $this->assertEquals(Url::getCurrentQueryStringWithParametersModified([]), Url::getCurrentQueryString());
        $this->assertEquals(Url::getCurrentUrl(), Url::getCurrentUrlWithoutQueryString());
        $this->assertEquals(Url::getCurrentUrl(), Url::getCurrentScheme() . '://' . Url::getCurrentHost() . Url::getCurrentScriptName());

        $_SERVER['QUERY_STRING'] = 'q=test';

        $parameters = array_keys(Url::getArrayFromCurrentQueryString());
        $parametersNameToValue = [];
        foreach ($parameters as $name) {
            $parametersNameToValue[$name] = null;
        }
        $this->assertEquals('', Url::getCurrentQueryStringWithParametersModified($parametersNameToValue));
    }

    /**
     * Dataprovider for testGetCurrentHost()
     */
    public function getCurrentHosts()
    {
        return [
            ['localhost IPv4', ['127.0.0.1', null, null, null, '127.0.0.1']],
            ['localhost IPv6', ['[::1]', null, null, null, '[::1]']],
            ['localhost name', ['localhost', null, null, null, 'localhost']],

            ['IPv4 without proxy', ['128.1.2.3', null, null, null, '128.1.2.3']],
            ['IPv6 without proxy', ['[2001::b0b]', null, null, null, '[2001::b0b]']],
            ['name without proxy', ['example.com', null, null, null, 'example.com']],

            ['IPv4 with one proxy', ['127.0.0.1', '128.1.2.3', 'HTTP_X_FORWARDED_HOST', null, '128.1.2.3']],
            ['IPv6 with one proxy', ['[::1]', '[2001::b0b]', 'HTTP_X_FORWARDED_HOST', null, '[2001::b0b]']],
            ['name with one IPv4 proxy', ['192.168.1.10', 'example.com', 'HTTP_X_FORWARDED_HOST', null, 'example.com']],
            ['name with one IPv6 proxy', ['[::10]', 'www.example.com', 'HTTP_X_FORWARDED_HOST', null, 'www.example.com']],
            ['name with one named proxy', ['dmz.example.com', 'www.example.com', 'HTTP_X_FORWARDED_HOST', null, 'www.example.com']],

            ['IPv4 with multiple proxies', ['127.0.0.1', '128.1.2.3, 192.168.1.10', 'HTTP_X_FORWARDED_HOST', '192.168.1.*', '128.1.2.3']],
            ['IPv6 with multiple proxies', ['[::1]', '[2001::b0b], [::ffff:192.168.1.10]', 'HTTP_X_FORWARDED_HOST', '::ffff:192.168.1.0/124', '[2001::b0b]']],
            ['name with multiple proxies', ['dmz.example.com', 'www.example.com, dmz.example.com', 'HTTP_X_FORWARDED_HOST', 'dmz.example.com', 'www.example.com']],
        ];
    }

    /**
     * @dataProvider getCurrentHosts
     */
    public function testGetCurrentHost($description, $test)
    {
        Url::setHost($test[0]);
        $_SERVER['HTTP_X_FORWARDED_HOST'] = $test[1];
        Config::getInstance()->General['proxy_host_headers'] = [$test[2]];
        Config::getInstance()->General['proxy_ips'] = [$test[3]];
        Config::getInstance()->General['enable_trusted_host_check'] = 0;
        $this->assertEquals($test[4], Url::getCurrentHost(), $description);
    }

    /**
     * @dataProvider getProtocol
     */
    public function testGetCurrentSchemeProtoHeaderShouldPrecedenceHttpsHeader($proto)
    {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = $proto;
        $this->assertEquals($proto, Url::getCurrentScheme());

        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
        unset($_SERVER['HTTPS']);
    }

    /**
     * @dataProvider getProtocol
     */
    public function testGetCurrentSchemeShouldDetectSecureFromHttpsHeader()
    {
        $_SERVER['HTTPS'] = 'on';
        $this->assertEquals('https', Url::getCurrentScheme());

        unset($_SERVER['HTTPS']);
    }

    /**
     * @dataProvider getProtocol
     */
    public function testGetCurrentSchemeShouldBeHttpByDefault()
    {
        $this->assertEquals('http', Url::getCurrentScheme());
    }

    public function getProtocol()
    {
        return [['http'], ['https']];
    }

    /**
     * Dataprovider for testIsLocalUrl
     */
    public function getLocalUrls()
    {
        return [
            // simple cases
            ['www.example.com', 'http://www.example.com/path/index.php', '/path/index.php', 'http://www.example.com/path/index.php', true],
            ['www.example.com', 'http://www.example.com/path/index.php?module=X', '/path/index.php', 'http://www.example.com/path/', true],
            ['www.example.com', 'http://www.example.com/path/', '/path/index.php', 'http://www.example.com/path/index.php?module=Y', true],
            ['www.example.com', 'http://www.example.com/path/#anchor', '/path/index.php', 'http://www.example.com/path/?query', true],
            ['localhost:8080', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true],
            ['www.example.com', 'http://www.example.com/path/', '/path/', 'http://www.example.com/path2/', true],

            // ignore port
            ['www.example.com', 'http://www.example.com:80/path/index.php', '/path/index.php', 'http://www.example.com/path/index.php', true],
            ['www.example.com', 'http://www.example.com/path/index.php', '/path/index.php', 'http://www.example.com:80/path/index.php', true],

            ['localhost', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true],
            ['localhost', 'http://localhost/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true],
            ['localhost', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost/path/index.php', true],

            ['localhost:8080', 'http://localhost/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true],
            ['localhost:8080', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost/path/index.php', true],
            ['localhost:8080', 'http://localhost/path/index.php', '/path/index.php', 'http://localhost/path/index.php', true],
            ['localhost:8080', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true],

            // IPv6
            ['[::1]', 'http://[::1]/path/index.php', '/path/index.php', 'http://[::1]/path/index.php', true],
            ['[::1]:8080', 'http://[::1]:8080/path/index.php', '/path/index.php', 'http://[::1]/path/index.php', true],
            ['[::1]:8080', 'http://[::1]/path/index.php', '/path/index.php', 'http://[::1]:8080/path/index.php', true],

            // undefined SCRIPT URI
            ['www.example.com', null, '/path/index.php', 'http://www.example.com/path/index.php', true],
            ['localhost:8080', null, '/path/index.php', 'http://localhost:8080/path/index.php', true],
            ['127.0.0.1:8080', null, '/path/index.php', 'http://127.0.0.1:8080/path/index.php', true],
            ['[::1]', null, '/path/index.php', 'http://[::1]/path/index.php', true],
            ['[::1]:8080', null, '/path/index.php', 'http://[::1]:8080/path/index.php', true],

            // Apache+Rails anomaly in SCRIPT_URI
            ['www.example.com', 'http://www.example.com/path/#anchor', 'http://www.example.com/path/index.php', 'http://www.example.com/path/?query', true],

            // mangled HTTP_HOST
            ['www.example.com', 'http://example.com/path/#anchor', '/path/index.php', 'http://example.com/path/referrer', true],

            // suppressed Referrer
            ['www.example.com', 'http://www.example.com/path/#anchor', '/path/index.php', null, true],
            ['www.example.com', 'http://www.example.com/path/#anchor', '/path/index.php', '', true],

            // mismatched scheme or host
            ['www.example.com', 'http://www.example.com/path/?module=X', '/path/index.php', 'ftp://www.example.com/path/index.php', false],
            ['www.example.com', 'http://www.example.com/path/?module=X', '/path/index.php', 'http://example.com/path/index.php', false],
            ['www.example.com', 'http://www.example.com/path/', '/path/', 'http://crsf.example.com/path/', false],
        ];
    }

    /**
     * @dataProvider getIsLocalHost
     */
    public function testIsLocalHost($expectedIsLocal, $host)
    {
        $this->assertSame($expectedIsLocal, Url::isLocalHost($host));
    }

    public function getIsLocalHost()
    {
        return [
            [$isLocal = false, '127.0.0.2'],
            [$isLocal = false, '192.168.1.1'],
            [$isLocal = false, '10.1.1.1'],
            [$isLocal = false, '172.30.1.1'],

            [$isLocal = true, 'localhost'],
            [$isLocal = true, '127.0.0.1'],
            [$isLocal = true, '::1'],
            [$isLocal = true, '[::1]'],

            // with port
            [$isLocal = false, '172.30.1.1:80'],
            [$isLocal = false, '3ffe:1900:4545:3:200:f8ff:fe21:67cf:1005'],
            [$isLocal = true, 'localhost:3000'],
            [$isLocal = true, '127.0.0.1:213424'],
            [$isLocal = true, '::1:345'],
            [$isLocal = true, '[::1]:443'],
        ];
    }

    /**
     * @dataProvider getLocalUrls
     */
    public function testIsLocalUrl($httphost, $scripturi, $requesturi, $testurl, $result)
    {
        Url::setHost($httphost);
        $_SERVER['SCRIPT_URI'] = $scripturi;
        $_SERVER['REQUEST_URI'] = $requesturi;
        Config::getInstance()->General['enable_trusted_host_check'] = 1;
        Config::getInstance()->General['trusted_hosts'] = [$httphost];
        $urlToTest = $testurl;
        $this->assertEquals($result, Url::isLocalUrl($urlToTest));
    }

    /**
     * Dataprovider for testGetCurrentUrlWithoutFilename
     */
    public function getCurrentUrlWithoutFilename()
    {
        return [
            ['http://example.com/', false, 'example.com', '/'],
            ['https://example.org/', true, 'example.org', '/'],
            ['https://example.net/piwik/', 'on', 'example.net', '/piwik/'],
        ];
    }

    /**
     * @dataProvider getCurrentUrlWithoutFilename
     */
    public function testGetCurrentUrlWithoutFilename($expected, $https, $host, $path)
    {
        $this->resetGlobalVariables();

        if ($https) {
            $_SERVER['HTTPS'] = $https;
        } else {
            unset($_SERVER['HTTPS']);
        }

        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['HTTP_HOST'] = $host;

        Config::getInstance()->General['trusted_hosts'] = [$host];

        $url = Url::getCurrentUrlWithoutFilename();
        $this->assertEquals($expected, $url);
    }

    public function testGetCurrentScriptName()
    {
        $this->resetGlobalVariables();

        $tests = [
            ['/', 'http://example.com/', null],
            ['/', '/', null],
            ['/index.php', '/index.php', null],
            ['/index.php', '/index.php?module=Foo', null],
            ['/index.php', '/index.php/route/1', '/route/1'],
            ['/index.php', '/index.php#<img src=http://matomo.org', ''],
            ['/index.php', '/index.php/route/2?module=Bar', '/route/2'],
            ['/path/index.php', '/path/index.php/route/3/?module=Fu&action=Bar#Hash', '/route/3/'],
        ];

        foreach ($tests as $test) {
            list($expected, $uri, $pathInfo) = $test;

            $_SERVER['REQUEST_URI'] = $uri;
            $_SERVER['PATH_INFO'] = $pathInfo;

            $scriptPathName = Url::getCurrentScriptName();
            $this->assertEquals($expected, $scriptPathName);
        }
    }

    /**
     * Dataprovider for valid hosts
     */
    public function getValidHostData()
    {
        return [
            // $expected, $host, $trustedHosts, $description
            [true, 'example.com', ['example.com'], 'Naked domain'],
            [true, 'example.net', ['example.com', 'example.net'], 'Multiple domains'],
            [true, 'piwik.example.com', ['piwik.example.com'], 'Fully qualified domain name'],
            [true, 'piwik.example.com', ['example.com'], 'Valid subdomain'],
            [false, 'example.net', ['example.com'], 'Invalid domain'],
            [false, '.example.com', ['piwik.example.com'], 'Invalid subdomain'],
            [false, 'example-com', ['example.com'], 'Regex should match . literally'],
            [false, 'www.attacker.com?example.com', ['example.com'], 'Spoofed host'],
            [false, 'example.com.attacker.com', ['example.com'], 'Spoofed subdomain'],
            [true, 'example.com.', ['example.com'], 'Trailing . on host is actually valid'],
            [true, 'www-dev.example.com', ['example.com'], 'host with dashes is valid'],
            [false, 'www.example.com:8080', ['example.com'], 'host:port is valid'],
            [true, 'www.example.com:8080', ['example.com:8080'], 'host:port is valid'],
            [false, 'www.whatever.com', ['*.whatever.com'], 'regex char is escaped'],
            [false, 'www.whatever.com', ['www.whatever.com/abc'], 'with path starting with /a does not throw error'],
            [false, 'www.whatever.com', ['www.whatever.com/path/here'], 'with path starting with /p does not throw error'],
        ];
    }

    /**
     * @dataProvider getValidHostData
     */
    public function testIsValidHost($expected, $host, $trustedHosts, $description)
    {
        Config::getInstance()->General['enable_trusted_host_check'] = 1;
        Config::getInstance()->General['trusted_hosts'] = $trustedHosts;
        $this->assertEquals($expected, Url::isValidHost($host), $description);
    }

    public function testGetReferrer()
    {
        $_SERVER['HTTP_REFERER'] = 'http://www.piwik.org';
        $this->assertEquals('http://www.piwik.org', Url::getReferrer());
    }

    /**
     * @dataProvider getQueryParameters
     */
    public function testGetQueryStringFromParameters($params, $queryString)
    {
        $this->assertEquals($queryString, Url::getQueryStringFromParameters($params));
    }

    public function getQueryParameters()
    {
        return [
            [[], ''],
            [['v1', 'v2'], '0=v1&1=v2'],
            [['key' => 'val'], 'key=val'],
            [['key' => 'val', 'k2' => 'v2'], 'key=val&k2=v2'],
            [['key' => 'val', 'k2' => false], 'key=val'],  // remove false values
            [['key' => 'val', 'k2' => null], 'key=val'],   // remove null values
            [['key' => 'val', 'k2' => ['v1', 'v2']], 'key=val&k2[]=v1&k2[]=v2'],
            [['key' => 'val', 'k2' => ['k1' => 'v1', 'k2' => 'v2']], 'key=val&k2[]=v1&k2[]=v2'],
        ];
    }

    /**
     * @dataProvider getHostsFromUrl
     */
    public function testGetHostsFromUrl($url, $expectedHost)
    {
        $this->assertEquals($expectedHost, Url::getHostFromUrl($url));
    }

    public function getHostsFromUrl()
    {
        return [
            [null, null],
            ['http://', null],
            ['http://www.example.com', 'www.example.com'],
            ['http://www.ExaMplE.cOm', 'www.example.com'],
            ['http://www.example.com/test/foo?bar=xy', 'www.example.com'],
            ['http://127.0.0.1', '127.0.0.1'],
            ['example.com', null],
        ];
    }

    public function testGetRFCValidHostname()
    {
        $_SERVER['HTTP_HOST'] = 'demo.matomo.org';
        $this->assertEquals('demo.matomo.org', Url::getRFCValidHostname());
        unset($_SERVER['HTTP_HOST']);
        $this->assertEquals('matomo.org', Url::getRFCValidHostname('matomo.org'));
        $this->assertEquals(false, Url::getRFCValidHostname('matomo org'));
        $this->assertEquals(false, Url::getRFCValidHostname('matomo.org;<script'));
    }

    /**
     * @dataProvider getIsHostInUrls
     */
    public function testIsHostInUrlsl($isHost, $host, $urls)
    {
        $this->assertEquals($isHost, Url::isHostInUrls($host, $urls));
    }

    public function getIsHostInUrls()
    {
        return [
            [false, null, null],
            [false, 'http://', []],
            [false, 'example.com', []],
            [false, 'www.example.com', []],
            [false, 'example.com', ['www.example.com']], // not a domain so no "host"
            [true, 'example.com', ['example.com']],
            [true, 'eXamPle.com', ['exaMple.com']],
            [true, 'eXamPle.com', ['http://exaMple.com']],
            [true, 'eXamPle.com', ['http://piwik.org', 'http://www.exaMple.com', 'http://exaMple.com']], // multiple urls one or more are valid but not first one
            [true, 'example.com', ['http://example.com/test']], // url with path but correct host
            [true, 'example.com', ['http://www.example.com']], // subdomains are allowed
            [false, 'example.com', ['http://wwwexample.com']], // it should not be possible to create a similar host and make redirects work again. we allow only subdomains
            [true, 'example.com', ['http://ftp.exAmple.com/test']],
            [true, 'example.com', ['http://www.exAmple.com/test']],
            [false, 'ftp.example.com', ['http://www.example.com/test']],
            [true, '127.0.0.1', []], // always trusted host
        ];
    }

    public function urlProvider()
    {
        return [
            ['http://example.com/'],
            ['https://example.com/'],
            ['http://example.com/piwik/'],
            ['http://example.com/index.php'],
            ['http://example.com/index.php?foo=bar'],
            ['http://example.com/index.php/.html?foo=bar', '/.html'],
        ];
    }

    /**
     * @dataProvider urlProvider
     */
    public function testGetCurrentUrl($url, $pathInfo = null)
    {
        $this->resetGlobalVariables();
        $this->setGlobalVariablesFromUrl($url, $pathInfo);

        $this->assertEquals($url, Url::getCurrentUrl());
    }

    public function urlWithoutQueryStringProvider()
    {
        return [
            ['http://example.com/', 'http://example.com/'],
            ['https://example.com/', 'https://example.com/'],
            ['http://example.com/piwik/', 'http://example.com/piwik/'],
            ['http://example.com/index.php', 'http://example.com/index.php'],
            ['http://example.com/index.php?foo=bar', 'http://example.com/index.php'],
            ['http://example.com/index.php/.html?foo=bar', 'http://example.com/index.php/.html', '/.html'],
        ];
    }

    /**
     * @dataProvider urlWithoutQueryStringProvider
     */
    public function testGetCurrentUrlWithoutQueryString($url, $expected, $pathInfo = null)
    {
        $this->resetGlobalVariables();
        $this->setGlobalVariablesFromUrl($url, $pathInfo);

        $this->assertEquals($expected, Url::getCurrentUrlWithoutQueryString());
    }

    /**
     * Tests a use case that was reported by some users: Nginx is not properly configured and passes
     * incorrect PATH_INFO values in $_SERVER.
     * @link https://github.com/piwik/piwik/issues/6491
     */
    public function testMisconfiguredNginxPathInfo()
    {
        $this->resetGlobalVariables();

        // these variables where taken from a bug report
        $_SERVER = [
            'QUERY_STRING' => 'foo=bar',
            'PATH_INFO' => '/test.php', // Nginx passed a wrong value here (should be empty)
            'SCRIPT_NAME' => '/test.php',
            'REQUEST_URI' => '/test.php?foo=bar',
            'DOCUMENT_URI' => '/test.php',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'example.com',
            'HTTP_HOST' => 'example.com',
            'PHP_SELF' => '/test.php/test.php', // Nginx passed a wrong value here (should be /test.php)
        ];

        $expectedUrl = 'http://example.com/test.php?foo=bar';

        $this->assertEquals($expectedUrl, Url::getCurrentUrl());
    }


    /**
     * @group AddCampaignParametersToMatomoLink
     */
    public function testAddCampaignParametersToMatomoLink_exceptIfDisabled()
    {
        $this->resetGlobalVariables();
        $_GET['module'] = 'CoreHomeAdmin';
        $_GET['action'] = 'trackingCodeGenerator';

        Config::getInstance()->General['disable_tracking_matomo_app_links'] = 1;
        $this->assertEquals('https://matomo.org/faq/123',
            Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/123'));

        Config::getInstance()->General['disable_tracking_matomo_app_links'] = 0;
        $this->assertEquals('https://matomo.org/faq/123?mtm_campaign=Matomo_App&mtm_source=Matomo_App_OnPremise&mtm_medium=CoreHomeAdmin.trackingCodeGenerator',
            Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/123'));
    }

    /**
     * @dataProvider getCampaignParametersToMatomoLink
     * @group AddCampaignParametersToMatomoLink
     */
    public function testAddCampaignParametersToMatomoLink($url, $expected, $campaign, $source, $medium)
    {
        $this->resetGlobalVariables();
        $_GET['module'] = 'CoreHomeAdmin';
        $_GET['action'] = 'trackingCodeGenerator';
        $this->assertEquals($expected, Url::addCampaignParametersToMatomoLink($url, $campaign, $source, $medium));
    }

    public function getCampaignParametersToMatomoLink()
    {
        return [
            // Matomo url
            ['https://matomo.org/faq/123',
             'https://matomo.org/faq/123?mtm_campaign=Matomo_App&mtm_source=Matomo_App_OnPremise&mtm_medium=CoreHomeAdmin.trackingCodeGenerator',
             null, null, null
            ],

            // Matomo url, trailing ?
            ['https://matomo.org/faq/123?',
             'https://matomo.org/faq/123?mtm_campaign=Matomo_App&mtm_source=Matomo_App_OnPremise&mtm_medium=CoreHomeAdmin.trackingCodeGenerator',
             null, null, null
            ],

            // Matomo url, trailing ? and /
            ['https://matomo.org/faq/123/?',
             'https://matomo.org/faq/123/?mtm_campaign=Matomo_App&mtm_source=Matomo_App_OnPremise&mtm_medium=CoreHomeAdmin.trackingCodeGenerator',
             null, null, null
            ],

            // Matomo url, anchor
            ['https://matomo.org/faq/123#anchor',
             'https://matomo.org/faq/123#anchor?mtm_campaign=Matomo_App&mtm_source=Matomo_App_OnPremise&mtm_medium=CoreHomeAdmin.trackingCodeGenerator',
             null, null, null
            ],

            // Matomo url, anchor and parameter
            ['https://matomo.org/faq/123/#anchor?abc=123',
             'https://matomo.org/faq/123/#anchor?abc=123&mtm_campaign=Matomo_App&mtm_source=Matomo_App_OnPremise&mtm_medium=CoreHomeAdmin.trackingCodeGenerator',
             null, null, null
            ],

            // Matomo url, one parameter
            ['https://matomo.org/faq/123?abc=123',
             'https://matomo.org/faq/123?abc=123&mtm_campaign=Matomo_App&mtm_source=Matomo_App_OnPremise&mtm_medium=CoreHomeAdmin.trackingCodeGenerator',
             null, null, null
            ],

            // Matomo url, two parameters
            ['https://matomo.org/faq/123?abc=123&def=456',
             'https://matomo.org/faq/123?abc=123&def=456&mtm_campaign=Matomo_App&mtm_source=Matomo_App_OnPremise&mtm_medium=CoreHomeAdmin.trackingCodeGenerator',
             null, null, null
            ],

            // Matomo url with www subdomain, anchor and two parameters
            ['https://www.matomo.org/faq/123#anchor?abc=123&def=456',
             'https://www.matomo.org/faq/123#anchor?abc=123&def=456&mtm_campaign=Matomo_App&mtm_source=Matomo_App_OnPremise&mtm_medium=CoreHomeAdmin.trackingCodeGenerator',
             null, null, null
            ],

            // Non-matomo URL, two parameters and anchor - no change expected
            ['https://example.org/faq/123#anchor?abc=123&def=456',
             'https://example.org/faq/123#anchor?abc=123&def=456',
             null, null, null
            ],

            // Matomo url, two parameters, campaign overrides
            ['https://matomo.org/faq/123?abc=123&def=456',
             'https://matomo.org/faq/123?abc=123&def=456&mtm_campaign=SomeCampaign&mtm_source=SomeSource&mtm_medium=SomeMedium',
             'SomeCampaign', 'SomeSource', 'SomeMedium'
            ],

        ];
    }

    private function resetGlobalVariables()
    {
        $names = ['PATH_INFO', 'REQUEST_URI', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'argv', 'HTTPS',
            'HTTP_HOST', 'QUERY_STRING', 'HTTP_REFERER'];

        foreach ($names as $name) {
            unset($_SERVER[$name]);
        }

        Config::getInstance()->General['enable_trusted_host_check'] = 0;
    }

    /**
     * @param string $url
     * @param string $pathInfo The PATH_INFO has to be provided because parse_url() doesn't parse that
     */
    private function setGlobalVariablesFromUrl($url, $pathInfo)
    {
        if (parse_url($url, PHP_URL_SCHEME) === 'https') {
            $_SERVER['HTTPS'] = true;
        }

        $_SERVER['HTTP_HOST'] = parse_url($url, PHP_URL_HOST);
        $_SERVER['REQUEST_URI'] = parse_url($url, PHP_URL_PATH);

        $queryString = parse_url($url, PHP_URL_QUERY);
        if ($queryString) {
            $_SERVER['REQUEST_URI'] .= '?' . $queryString;
            $_SERVER['QUERY_STRING'] = $queryString;
        }

        if ($pathInfo) {
            $_SERVER['PATH_INFO'] = $pathInfo;
        }
    }
}
