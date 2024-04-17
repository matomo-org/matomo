<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        $getProxyConfiguration->setAccessible(true);

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
}
