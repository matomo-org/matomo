<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit;

use Piwik\UrlHelper;

/**
 * @group UrlHelperTest
 */
class UrlHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Dataprovider for testIsUrl
     */
    public function getUrls()
    {
        return array(
            // valid urls
            array('http://piwik.org', true),
            array('http://www.piwik.org', true),
            array('https://piwik.org', true),
            array('https://piwik.org/dir/dir2/?oeajkgea7aega=&ge=a', true),
            array('ftp://www.pi-wik.org', true),
            array('news://www.pi-wik.org', true),
            array('https://www.tëteâ.org', true),
            array('http://汉语/漢語.cn', true), //chinese
            array('news://www.javascript.org', true),

            array('rtp://whatever.com', true),
            array('testhttp://test.com', true),
            array('cylon://3.hmn', true),
            array('://something.com', true),

            // valid network-path reference RFC3986
            array('//piwik.org', true),
            array('//piwik/hello?world=test&test', true),
            array('//piwik.org/hello?world=test&test', true),

            // invalid urls
            array('it doesnt look like url', false),
            array('/index?page=test', false),
            array('http:/index?page=test', false),
            array('http/index?page=test', false),
            array('test.html', false),
            array('/\/\/\/\/\/\\\http://test.com////', false),
            array('jmleslangues.php', false),
            array('http://', false),
            array(' http://', false),
            array('2fer://', false),
            array('javascript://test.com/test', false),
            array('javascript://alert', false),
            array('vbscript://alert', false),
            array('vbscript://alert', false),
            array('data://example.com/test', false),
            array('jaVascRipt://test.com/test', false),
            array('VBscrIpt://alert', false),
            array('dAtA://example.com/test', false),
        );
    }

    /**
     * @dataProvider getUrls
     * @group Core
     */
    public function testIsUrl($url, $isValid)
    {
        $this->assertEquals($isValid, UrlHelper::isLookLikeUrl($url), "$url failed test");
    }

    /**
     * @dataProvider getTestDataForIsLookLikeSafeUrl
     */
    public function test_isLookLikeSafeUrl($url, $isSafe)
    {
        $this->assertEquals($isSafe, UrlHelper::isLookLikeSafeUrl($url));
    }

    public function getTestDataForIsLookLikeSafeUrl()
    {
        return [
            // valid
            array('http://piwik.org', true),
            array('http://www.piwik.org', true),
            array('https://piwik.org', true),
            array('https://piwik.org/dir/dir2/?oeajkgea7aega=&ge=a', true),
            array('tel:12345', true),
            array('sms:456543', true),

            // invalid
            array('rtp://whatever.com', false),
            array('testhttp://test.com', false),
            array('cylon://3.hmn', false),
            array('://something.com', false),
            array('data://example.com/test', false),
            array('jaVascRipt://test.com/test', false),
            array('VBscrIpt://alert', false),
            array('dAtA://example.com/test', false),
            array('data://tel.org/http', false),
            array('smstest:456543', false),
            array(urldecode('javascript://%0D%0Aalert(1)'), false),
            array(urldecode('http://%0D%0Aalert(1)'), false),
        ];
    }

    /**
     * Dataprovider for testGetParameterFromQueryString
     */
    public function getQueryStrings()
    {
        return array( // querystring, parameter, expected value
            array('x=1', 'x', '1'),
            array('?x=1', 'x', '1'),
            array('?x=y==1', 'x', 'y==1'),
            array('x[]=', 'x', array('')),
            array('x[]=1', 'x', array('1')),
            array('x[]=y==1', 'x', array('y==1')),
            array('?x[]=1&x[]=2', 'x', array('1', '2')),
            array('?x%5b%5d=3&x[]=4', 'x', array('3', '4')),
            array('?x%5B]=5&x[%5D=6', 'x', array('5', '6')),
            array('toto=mama&mama=&tuytyt=teaoi&toto=mama second value', 'tuytyt', 'teaoi'),

            // don't unescape the value, otherwise it becomes
            //   ?x[]=A&y=1
            array('?x%5B%5D=A%26y%3D1', 'x', array('A%26y%3D1')),
            //   ?z=y&x[]=1
            array('?z=y%26x%5b%5d%3d1', 'x', null),

            // strange characters
            array('toto=mama&mama=&tuytyt=Поиск в Интернете  Поиск страниц на русском _*()!$!£$^!£$%&toto=mama second value', 'tuytyt', 'Поиск в Интернете  Поиск страниц на русском _*()!$!£$^!£$%'),

            // twice the parameter => returns the last value in the url
            array('toto=mama&mama=&tuytyt=teaoi&toto=mama second value', 'toto', 'mama second value'),

            // empty param
            array('toto=mama&mama=&tuytyt=teaoi', 'mama', ''),

            // missing parameter value => returns false
            array('x', 'x', false),
            array('toto=mama&mama&tuytyt=teaoi', 'mama', false),

            // param not found => null
            array('toto=mama&mama=titi', 'tot', null),

            // empty query string => null
            array('', 'test', null),
        );
    }

    /**
     * @dataProvider getQueryStrings
     * @group Core
     */
    public function testGetParameterFromQueryString($queryString, $parameter, $expected)
    {
        $this->assertSame($expected, UrlHelper::getParameterFromQueryString($queryString, $parameter));
    }

    /**
     * @group Core
     */
    public function testGetPathAndQueryFromUrl()
    {
        $this->assertEquals('test/index.php?module=CoreHome', UrlHelper::getPathAndQueryFromUrl('http://piwik.org/test/index.php?module=CoreHome'));

        // Add parameters to existing params
        $this->assertEquals(
            'test/index.php?module=CoreHome&abc=123&def=456',
            UrlHelper::getPathAndQueryFromUrl('http://piwik.org/test/index.php?module=CoreHome', ['abc' => '123', 'def' => '456'])
        );

        // Add parameters with no existing params
        $this->assertEquals(
            'test/index.php?abc=123&def=456',
            UrlHelper::getPathAndQueryFromUrl('http://piwik.org/test/index.php', ['abc' => '123', 'def' => '456'])
        );

        // Preserve anchor
        $this->assertEquals(
            'test/index.php#anchor',
            UrlHelper::getPathAndQueryFromUrl('http://piwik.org/test/index.php#anchor', [], true)
        );

        // Do not preserve anchor
        $this->assertEquals(
            'test/index.php',
            UrlHelper::getPathAndQueryFromUrl('http://piwik.org/test/index.php#anchor', [], false)
        );

        // Add parameters with existing params, preserve anchor
        $this->assertEquals(
            'test/index.php#anchor?abc=123&def=456',
            UrlHelper::getPathAndQueryFromUrl('http://piwik.org/test/index.php#anchor', ['abc' => '123', 'def' => '456'], true)
        );
    }

    /**
     * @group Core
     */
    public function testGetPathAndQueryFromNonUrl()
    {
        $this->assertEquals('Others', UrlHelper::getPathAndQueryFromUrl('Others'));
    }

    /**
     * @group Core
     */
    public function testGetArrayFromQueryString()
    {
        $expected = array(
            'a' => false,
            'b' => '',
            'c' => '1',
            'd' => array(false),
            'e' => array(''),
            'f' => array('a'),
            'g' => array('b', 'c'),
        );
        $this->assertEquals(serialize($expected), serialize(UrlHelper::getArrayFromQueryString('a&b=&c=1&d[]&e[]=&f[]=a&g[]=b&g[]=c')));
    }

    /**
     * Dataprovider for testGetLossyUrl
     */
    public function getLossyUrls()
    {
        return array(
            array('example.com', 'example.com'),
            array('m.example.com', 'example.com'),
            array('www.example.com', 'example.com'),
            array('search.example.com', 'example.com'),
            array('example.ca', 'example.{}'),
            array('us.example.com', '{}.example.com'),
            array('www.m.example.ca', 'example.{}'),
            array('www.google.com.af', 'google.{}'),
            array('www.google.co.uk', 'google.{}'),
            array('images.de.ask.com', 'images.{}.ask.com'),
        );
    }

    /**
     * @dataProvider getLossyUrls
     * @group Core
     */
    public function testGetLossyUrl($input, $expected)
    {
        $this->assertEquals($expected, UrlHelper::getLossyUrl($input));
    }

    /**
     * @group Core
     */
    public function test_getHostFromUrl()
    {
        $this->assertEquals('', UrlHelper::getHostFromUrl(''));
        $this->assertEquals('', UrlHelper::getHostFromUrl(null));
        $this->assertEquals('localhost', UrlHelper::getHostFromUrl('http://localhost'));
        $this->assertEquals('localhost', UrlHelper::getHostFromUrl('http://localhost/path'));
        $this->assertEquals('localhost', UrlHelper::getHostFromUrl('localhost/path'));
        $this->assertEquals('sub.localhost', UrlHelper::getHostFromUrl('sub.localhost/path'));
        $this->assertEquals('sub.localhost', UrlHelper::getHostFromUrl('http://sub.localhost/path/?query=test'));
        $this->assertEquals('localhost', UrlHelper::getHostFromUrl('//localhost/path'));
        $this->assertEquals('localhost', UrlHelper::getHostFromUrl('//localhost/path?test=test2'));
        $this->assertEquals('example.org', UrlHelper::getHostFromUrl('//example.org/path'));
        $this->assertEquals('example.org', UrlHelper::getHostFromUrl('//example.org/path?test=test2'));
    }

    /**
     * @group Core
     */
    public function test_getQueryFromUrl_ShouldReturnEmtpyString_IfNoQuery()
    {
        $this->assertEquals('', UrlHelper::getQueryFromUrl('', array()));
        $this->assertEquals('', UrlHelper::getQueryFromUrl(null, array()));
        $this->assertEquals('', UrlHelper::getQueryFromUrl('http://localhost/path', array()));
    }

    /**
     * @group Core
     */
    public function test_getQueryFromUrl_ShouldReturnOnlyTheQueryPartOfTheUrl_IfNoAdditionalParamsGiven()
    {
        $this->assertEquals('foo=bar&foo2=bar2&test[]=1', UrlHelper::getQueryFromUrl('http://example.com/?foo=bar&foo2=bar2&test[]=1', array()));
        $this->assertEquals('foo=bar&foo2=bar2&test[]=1', UrlHelper::getQueryFromUrl('/?foo=bar&foo2=bar2&test[]=1', array()));
        $this->assertEquals('segment=pageTitle!@%40Hello%20World;pageTitle!@Peace%20Love%20', UrlHelper::getQueryFromUrl('/?segment=pageTitle!@%40Hello%20World;pageTitle!@Peace%20Love%20', array()));
    }

    public function test_getQueryFromUrl_whenUrlParameterIsDuplicatedInQueryString_returnsLastFoundValue()
    {
        // Currently when the same parameter is used several times in the query string,
        // only the last set value is returned by UrlHelper::getParameterFromQueryString
        // refs https://github.com/piwik/piwik/issues/9842#issue-136043409
        $this->assertEquals('blue', UrlHelper::getParameterFromQueryString('selected_colors=red&selected_colors=blue&par3=1', 'selected_colors'));
        $this->assertEquals('selected_colors=red&selected_colors=blue&par3=1', UrlHelper::getQueryFromUrl('http:/mydomain.com?selected_colors=red&selected_colors=blue&par3=1', array()));
    }

    /**
     * @group Core
     */
    public function test_getQueryFromUrl_ShouldAddAdditionalParams_IfGiven()
    {
        $this->assertEquals('foo=bar&foo2=bar2&test[]=1&add=foo', UrlHelper::getQueryFromUrl('http://example.com/?foo=bar&foo2=bar2&test[]=1', array('add' => 'foo')));
        $this->assertEquals('add=foo', UrlHelper::getQueryFromUrl('/', array('add' => 'foo')));
        $this->assertEquals('add[]=foo&add[]=test', UrlHelper::getQueryFromUrl('/', array('add' => array('foo', 'test'))));
    }


    /**
     * Dataprovider for testGetQueryStringWithExcludedParameters
     */
    public function getQueryParameters()
    {
        return array(
            array(
                'p1=v1&p2=v2',                      //expected
                array('p1' => 'v1', 'p2' => 'v2'),      //queryParameters
                array()                             //parametersToExclude
            ),
            array(
                'p2=v2',
                array('p1' => 'v1', 'p2' => 'v2'),
                array('p1')
            ),
            array(
                'p1=v1&p2=v2',
                array('p1' => 'v1', 'p2' => 'v2', 'sessionId' => 'HHSJHERTG'),
                array('sessionId')
            ),
            array(
                'p1=v1&p2=v2',
                array('p1' => 'v1', 'p2' => 'v2', 'sessionId' => 'HHSJHERTG'),
                array('/session/')
            ),
            array(
                'p1=v1&p2=v2',
                array('p1' => 'v1', 'sessionId' => 'HHSJHERTG', 'p2' => 'v2', 'token' => 'RYUN36HSAO'),
                array('/[session|token]/')
            ),
            array(
                '',
                array('p1' => 'v1', 'p2' => 'v2', 'sessionId' => 'HHSJHERTG', 'token' => 'RYUN36HSAO'),
                array('/.*/')
            ),
            array(
                'p2=v2&p4=v4',
                array('p1' => 'v1', 'p2' => 'v2', 'p3' => 'v3', 'p4' => 'v4'),
                array('/p[1|3]/')
            ),
            array(
                'p2=v2&p4=v4',
                array('p1' => 'v1', 'p2' => 'v2', 'p3' => 'v3', 'p4' => 'v4', 'utm_source' => 'gekko', 'utm_medium' => 'email', 'utm_campaign' => 'daily'),
                array('/p[1|3]/', '/utm_/')
            )
        );
    }

    /**
     * @dataProvider getQueryParameters
     * @group Core
     */
    public function testGetQueryStringWithExcludedParameters($expected, $queryParameters, $parametersToExclude)
    {
        $this->assertEquals($expected, UrlHelper::getQueryStringWithExcludedParameters($queryParameters, $parametersToExclude));
    }
}
