<?php
use Piwik\UrlHelper;

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class Core_UrlHelperTest extends PHPUnit_Framework_TestCase
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
            // invalid urls
            array('it doesnt look like url', false),
            array('/index?page=test', false),
            array('test.html', false),
            array('/\/\/\/\/\/\\\http://test.com////', false),
            array('jmleslangues.php', false),
            array('http://', false),
            array(' http://', false),
            array('testhttp://test.com', false),
        );
    }

    /**
     * @dataProvider getUrls
     * @group Core
     */
    public function testIsUrl($url, $isValid)
    {
        $this->assertEquals($isValid, UrlHelper::isLookLikeUrl($url));
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
     * Dataprovider for testExtractSearchEngineInformationFromUrl
     */
    public function getSearchEngineUrls()
    {
        return Spyc::YAMLLoad(PIWIK_PATH_TEST_TO_ROOT .'/tests/resources/extractSearchEngineInformationFromUrlTests.yml');
    }

    /**
     * @dataProvider getSearchEngineUrls
     * @group Core
     */
    public function testExtractSearchEngineInformationFromUrl($url, $engine, $keywords)
    {
        $this->includeDataFilesForSearchEngineTest();
        $returnedValue = UrlHelper::extractSearchEngineInformationFromUrl($url);

        $exptectedValue = false;

        if (!empty($engine)) {
            $exptectedValue = array('name' => $engine, 'keywords' => $keywords);
        }

        $this->assertEquals($exptectedValue, $returnedValue);
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



    private function includeDataFilesForSearchEngineTest()
    {
        include "DataFiles/SearchEngines.php";
        include "DataFiles/Countries.php";
    }
}