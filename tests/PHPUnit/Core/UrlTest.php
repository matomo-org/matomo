<?php
use Piwik\Config;
use Piwik\Url;

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group Core
     */
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
     * @group Core
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
     * @dataProvider getLocalUrls
     * @group Core
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
     * @group Core
     */
    public function testGetCurrentUrlWithoutFilename($expected, $https, $host, $path)
    {
        $names = array('PATH_INFO', 'REQUEST_URI', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'argv', 'HTTPS', 'HTTP_HOST', 'QUERY_STRING', 'HTTP_REFERER');

        foreach ($names as $name) {
            unset($_SERVER[$name]);
        }

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

    /**
     * @group Core
     */
    public function test_getCurrentScriptName()
    {
        $names = array('PATH_INFO', 'REQUEST_URI', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'argv', 'HTTPS', 'HTTP_HOST', 'QUERY_STRING', 'HTTP_REFERER');

        foreach ($names as $name) {
            unset($_SERVER[$name]);
        }

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
        );
    }

    /**
     * @dataProvider getValidHostData
     * @group Core
     */
    public function testIsValidHost($expected, $host, $trustedHosts, $description)
    {
        Config::getInstance()->General['enable_trusted_host_check'] = 1;
        Config::getInstance()->General['trusted_hosts'] = $trustedHosts;
        $this->assertEquals($expected, Url::isValidHost($host), $description);
    }

    /**
     * @group Core
     */
    public function testGetReferrer()
    {
        $_SERVER['HTTP_REFERER'] = 'http://www.piwik.org';
        $this->assertEquals('http://www.piwik.org', Url::getReferrer());
    }

    /**
     * @group Core
     * 
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


}
