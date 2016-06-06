<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\Config;
use Piwik\Url;

/**
 * @backupGlobals enabled
 * @group Core
 */
class UrlTest extends \PHPUnit_Framework_TestCase
{
    public function testAllMethods()
    {
        $this->assertEquals(Url::getCurrentQueryStringWithParametersModified(array()), Url::getCurrentQueryString());
        $this->assertEquals(Url::getCurrentUrl(), Url::getCurrentUrlWithoutQueryString());
        $this->assertEquals(Url::getCurrentUrl(), Url::getCurrentScheme() . '://' . Url::getCurrentHost() . Url::getCurrentScriptName());

        $_SERVER['QUERY_STRING'] = 'q=test';

        $parameters = array_keys(Url::getArrayFromCurrentQueryString());
        $parametersNameToValue = array();
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
        return array(
            array('localhost IPv4', array('127.0.0.1', null, null, null, '127.0.0.1')),
            array('localhost IPv6', array('[::1]', null, null, null, '[::1]')),
            array('localhost name', array('localhost', null, null, null, 'localhost')),

            array('IPv4 without proxy', array('128.1.2.3', null, null, null, '128.1.2.3')),
            array('IPv6 without proxy', array('[2001::b0b]', null, null, null, '[2001::b0b]')),
            array('name without proxy', array('example.com', null, null, null, 'example.com')),

            array('IPv4 with one proxy', array('127.0.0.1', '128.1.2.3', 'HTTP_X_FORWARDED_HOST', null, '128.1.2.3')),
            array('IPv6 with one proxy', array('[::1]', '[2001::b0b]', 'HTTP_X_FORWARDED_HOST', null, '[2001::b0b]')),
            array('name with one IPv4 proxy', array('192.168.1.10', 'example.com', 'HTTP_X_FORWARDED_HOST', null, 'example.com')),
            array('name with one IPv6 proxy', array('[::10]', 'www.example.com', 'HTTP_X_FORWARDED_HOST', null, 'www.example.com')),
            array('name with one named proxy', array('dmz.example.com', 'www.example.com', 'HTTP_X_FORWARDED_HOST', null, 'www.example.com')),

            array('IPv4 with multiple proxies', array('127.0.0.1', '128.1.2.3, 192.168.1.10', 'HTTP_X_FORWARDED_HOST', '192.168.1.*', '128.1.2.3')),
            array('IPv6 with multiple proxies', array('[::1]', '[2001::b0b], [::ffff:192.168.1.10]', 'HTTP_X_FORWARDED_HOST', '::ffff:192.168.1.0/124', '[2001::b0b]')),
            array('name with multiple proxies', array('dmz.example.com', 'www.example.com, dmz.example.com', 'HTTP_X_FORWARDED_HOST', 'dmz.example.com', 'www.example.com')),
        );
    }

    /**
     * @dataProvider getCurrentHosts
     */
    public function testGetCurrentHost($description, $test)
    {
        $_SERVER['HTTP_HOST'] = $test[0];
        $_SERVER['HTTP_X_FORWARDED_HOST'] = $test[1];
        Config::getInstance()->General['proxy_host_headers'] = array($test[2]);
        Config::getInstance()->General['proxy_ips'] = array($test[3]);
        Config::getInstance()->General['enable_trusted_host_check'] = 0;
        $this->assertEquals($test[4], Url::getCurrentHost(), $description);
    }

    /**
     * Dataprovider for testIsLocalUrl
     */
    public function getLocalUrls()
    {
        return array(
            // simple cases
            array('www.example.com', 'http://www.example.com/path/index.php', '/path/index.php', 'http://www.example.com/path/index.php', true),
            array('www.example.com', 'http://www.example.com/path/index.php?module=X', '/path/index.php', 'http://www.example.com/path/', true),
            array('www.example.com', 'http://www.example.com/path/', '/path/index.php', 'http://www.example.com/path/index.php?module=Y', true),
            array('www.example.com', 'http://www.example.com/path/#anchor', '/path/index.php', 'http://www.example.com/path/?query', true),
            array('localhost:8080', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),
            array('www.example.com', 'http://www.example.com/path/', '/path/', 'http://www.example.com/path2/', true),

            // ignore port
            array('www.example.com', 'http://www.example.com:80/path/index.php', '/path/index.php', 'http://www.example.com/path/index.php', true),
            array('www.example.com', 'http://www.example.com/path/index.php', '/path/index.php', 'http://www.example.com:80/path/index.php', true),

            array('localhost', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),
            array('localhost', 'http://localhost/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),
            array('localhost', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost/path/index.php', true),

            array('localhost:8080', 'http://localhost/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),
            array('localhost:8080', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost/path/index.php', true),
            array('localhost:8080', 'http://localhost/path/index.php', '/path/index.php', 'http://localhost/path/index.php', true),
            array('localhost:8080', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),

            // IPv6
            array('[::1]', 'http://[::1]/path/index.php', '/path/index.php', 'http://[::1]/path/index.php', true),
            array('[::1]:8080', 'http://[::1]:8080/path/index.php', '/path/index.php', 'http://[::1]/path/index.php', true),
            array('[::1]:8080', 'http://[::1]/path/index.php', '/path/index.php', 'http://[::1]:8080/path/index.php', true),

            // undefined SCRIPT URI
            array('www.example.com', null, '/path/index.php', 'http://www.example.com/path/index.php', true),
            array('localhost:8080', null, '/path/index.php', 'http://localhost:8080/path/index.php', true),
            array('127.0.0.1:8080', null, '/path/index.php', 'http://127.0.0.1:8080/path/index.php', true),
            array('[::1]', null, '/path/index.php', 'http://[::1]/path/index.php', true),
            array('[::1]:8080', null, '/path/index.php', 'http://[::1]:8080/path/index.php', true),

            // Apache+Rails anomaly in SCRIPT_URI
            array('www.example.com', 'http://www.example.com/path/#anchor', 'http://www.example.com/path/index.php', 'http://www.example.com/path/?query', true),

            // mangled HTTP_HOST
            array('www.example.com', 'http://example.com/path/#anchor', '/path/index.php', 'http://example.com/path/referrer', true),

            // suppressed Referrer
            array('www.example.com', 'http://www.example.com/path/#anchor', '/path/index.php', null, true),
            array('www.example.com', 'http://www.example.com/path/#anchor', '/path/index.php', '', true),

            // mismatched scheme or host
            array('www.example.com', 'http://www.example.com/path/?module=X', '/path/index.php', 'ftp://www.example.com/path/index.php', false),
            array('www.example.com', 'http://www.example.com/path/?module=X', '/path/index.php', 'http://example.com/path/index.php', false),
            array('www.example.com', 'http://www.example.com/path/', '/path/', 'http://crsf.example.com/path/', false),
        );
    }

    /**
     * @dataProvider getIsLocalHost
     */
    public function test_isLocalHost($expectedIsLocal, $host)
    {
        $this->assertSame($expectedIsLocal, Url::isLocalHost($host));
    }

    public function getIsLocalHost()
    {
        return array(
            array($isLocal = false, '127.0.0.2'),
            array($isLocal = false, '192.168.1.1'),
            array($isLocal = false, '10.1.1.1'),
            array($isLocal = false, '172.30.1.1'),

            array($isLocal = true, 'localhost'),
            array($isLocal = true, '127.0.0.1'),
            array($isLocal = true, '::1'),
            array($isLocal = true, '[::1]'),
        );
    }

    /**
     * @dataProvider getLocalUrls
     */
    public function testIsLocalUrl($httphost, $scripturi, $requesturi, $testurl, $result)
    {
        $_SERVER['HTTP_HOST'] = $httphost;
        $_SERVER['SCRIPT_URI'] = $scripturi;
        $_SERVER['REQUEST_URI'] = $requesturi;
        Config::getInstance()->General['enable_trusted_host_check'] = 1;
        Config::getInstance()->General['trusted_hosts'] = array($httphost);
        $urlToTest = $testurl;
        $this->assertEquals($result, Url::isLocalUrl($urlToTest));
    }

    /**
     * Dataprovider for testGetCurrentUrlWithoutFilename
     */
    public function getCurrentUrlWithoutFilename()
    {
        return array(
            array('http://example.com/', false, 'example.com', '/'),
            array('https://example.org/', true, 'example.org', '/'),
            array('https://example.net/piwik/', 'on', 'example.net', '/piwik/'),
        );

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

        Config::getInstance()->General['trusted_hosts'] = array($host);

        $url = Url::getCurrentUrlWithoutFilename();
        $this->assertEquals($expected, $url);
    }

    public function test_getCurrentScriptName()
    {
        $this->resetGlobalVariables();

        $tests = array(
            array('/', 'http://example.com/', null),
            array('/', '/', null),
            array('/index.php', '/index.php', null),
            array('/index.php', '/index.php?module=Foo', null),
            array('/index.php', '/index.php/route/1', '/route/1'),
            array('/index.php', '/index.php/route/2?module=Bar', '/route/2'),
            array('/path/index.php', '/path/index.php/route/3/?module=Fu&action=Bar#Hash', '/route/3/'),
        );

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
        return array(
            // $expected, $host, $trustedHosts, $description
            array(true, 'example.com', array('example.com'), 'Naked domain'),
            array(true, 'example.net', array('example.com', 'example.net'), 'Multiple domains'),
            array(true, 'piwik.example.com', array('piwik.example.com'), 'Fully qualified domain name'),
            array(true, 'piwik.example.com', array('example.com'), 'Valid subdomain'),
            array(false, 'example.net', array('example.com'), 'Invalid domain'),
            array(false, '.example.com', array('piwik.example.com'), 'Invalid subdomain'),
            array(false, 'example-com', array('example.com'), 'Regex should match . literally'),
            array(false, 'www.attacker.com?example.com', array('example.com'), 'Spoofed host'),
            array(false, 'example.com.attacker.com', array('example.com'), 'Spoofed subdomain'),
            array(true, 'example.com.', array('example.com'), 'Trailing . on host is actually valid'),
            array(true, 'www-dev.example.com', array('example.com'), 'host with dashes is valid'),
            array(false, 'www.example.com:8080', array('example.com'), 'host:port is valid'),
            array(true, 'www.example.com:8080', array('example.com:8080'), 'host:port is valid'),
            array(false, 'www.whatever.com', array('*.whatever.com'), 'regex char is escaped'),
            array(false, 'www.whatever.com', array('www.whatever.com/abc'), 'with path starting with /a does not throw error'),
            array(false, 'www.whatever.com', array('www.whatever.com/path/here'), 'with path starting with /p does not throw error'),
        );
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
        return array(
            array(array(), ''),
            array(array('v1', 'v2'), '0=v1&1=v2'),
            array(array('key' => 'val'), 'key=val'),
            array(array('key' => 'val', 'k2' => 'v2'), 'key=val&k2=v2'),
            array(array('key' => 'val', 'k2' => false), 'key=val'),  // remove false values
            array(array('key' => 'val', 'k2' => null), 'key=val'),   // remove null values
            array(array('key' => 'val', 'k2' => array('v1', 'v2')), 'key=val&k2[]=v1&k2[]=v2'),
            array(array('key' => 'val', 'k2' => array('k1' => 'v1', 'k2' => 'v2')), 'key=val&k2[]=v1&k2[]=v2'),
        );
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
        return array(
            array(null, null),
            array('http://', null),
            array('http://www.example.com', 'www.example.com'),
            array('http://www.ExaMplE.cOm', 'www.example.com'),
            array('http://www.example.com/test/foo?bar=xy', 'www.example.com'),
            array('http://127.0.0.1', '127.0.0.1'),
            array('example.com', null),
        );
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
        return array(
            array(false, null, null),
            array(false, 'http://', array()),
            array(false, 'example.com', array()),
            array(false, 'www.example.com', array()),
            array(false, 'example.com', array('www.example.com')), // not a domain so no "host"
            array(true, 'example.com', array('example.com')),
            array(true, 'eXamPle.com', array('exaMple.com')),
            array(true, 'eXamPle.com', array('http://exaMple.com')),
            array(true, 'eXamPle.com', array('http://piwik.org', 'http://www.exaMple.com', 'http://exaMple.com')), // multiple urls one or more are valid but not first one
            array(true, 'example.com', array('http://example.com/test')), // url with path but correct host
            array(true, 'example.com', array('http://www.example.com')), // subdomains are allowed
            array(false, 'example.com', array('http://wwwexample.com')), // it should not be possible to create a similar host and make redirects work again. we allow only subdomains
            array(true, 'example.com', array('http://ftp.exAmple.com/test')),
            array(true, 'example.com', array('http://www.exAmple.com/test')),
            array(false, 'ftp.example.com', array('http://www.example.com/test')),
            array(true, '127.0.0.1', array()), // always trusted host
        );
    }

    public function urlProvider()
    {
        return array(
            array('http://example.com/'),
            array('https://example.com/'),
            array('http://example.com/piwik/'),
            array('http://example.com/index.php'),
            array('http://example.com/index.php?foo=bar'),
            array('http://example.com/index.php/.html?foo=bar', '/.html'),
        );
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
        return array(
            array('http://example.com/', 'http://example.com/'),
            array('https://example.com/', 'https://example.com/'),
            array('http://example.com/piwik/', 'http://example.com/piwik/'),
            array('http://example.com/index.php', 'http://example.com/index.php'),
            array('http://example.com/index.php?foo=bar', 'http://example.com/index.php'),
            array('http://example.com/index.php/.html?foo=bar', 'http://example.com/index.php/.html', '/.html'),
        );
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
        $_SERVER = array(
            'QUERY_STRING' => 'foo=bar',
            'PATH_INFO' => '/test.php', // Nginx passed a wrong value here (should be empty)
            'SCRIPT_NAME' => '/test.php',
            'REQUEST_URI' => '/test.php?foo=bar',
            'DOCUMENT_URI' => '/test.php',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'SERVER_NAME' => 'example.com',
            'HTTP_HOST' => 'example.com',
            'PHP_SELF' => '/test.php/test.php', // Nginx passed a wrong value here (should be /test.php)
        );

        $expectedUrl = 'http://example.com/test.php?foo=bar';

        $this->assertEquals($expectedUrl, Url::getCurrentUrl());
    }

    private function resetGlobalVariables()
    {
        $names = array('PATH_INFO', 'REQUEST_URI', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'argv', 'HTTPS',
            'HTTP_HOST', 'QUERY_STRING', 'HTTP_REFERER');

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
