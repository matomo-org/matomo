<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
class Core_CommonTest extends PHPUnit_Framework_TestCase
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
     * @group Common
     * @group isLookLikeUrl
     */
    public function testIsUrl($url, $isValid)
    {
        $this->assertEquals($isValid, Piwik_Common::isLookLikeUrl($url));
    }

    /**
     * Dataprovider for testSanitizeInputValues
     */
    public function getInputValues()
    {
        return array( // input, output
            // sanitize an array OK
            array(
                array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52),
                array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52)
            ),
            array(
                array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52,
                      array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52),
                      array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52),
                      array(array(array(array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52)))
                      )),
                array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52,
                      array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52),
                      array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52),
                      array(array(array(array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52)))
                      ))
            ),
            // sanitize an array with bad value level1
            array(
                array('test1' => 't1', 't45', 'tea1"ta"e', 568, 1 => array('t<e"st'), 1.52),
                array('test1' => 't1', 't45', 'tea1&quot;ta&quot;e', 568, 1 => array('t&lt;e&quot;st'), 1.52)
            ),
            // sanitize an array with bad value level2
            array(
                array('tea1"ta"e' => array('t<e"st' => array('tgeag454554"t')), 1.52),
                array('tea1&quot;ta&quot;e' => array('t&lt;e&quot;st' => array('tgeag454554&quot;t')), 1.52)
            ),
            // sanitize a string unicode => no change
            array(
                " Поиск в Интернете  Поgqegиск страниц на рgeqg8978усском",
                " Поиск в Интернете  Поgqegиск страниц на рgeqg8978усском"
            ),
            // sanitize a bad string
            array(
                '& " < > 123abc\'',
                '&amp; &quot; &lt; &gt; 123abc&#039;'
            ),
            // test filter - expect new line and null byte to be filtered out
            array(
                "New\nLine\rNull\0Byte",
                'NewLineNullByte'
            ),
            // double encoded - no change (document as user error)
            array(
                '%48%45%4C%00%4C%4F+%57%4F%52%4C%44',
                '%48%45%4C%00%4C%4F+%57%4F%52%4C%44'
            ),
            // sanitize an integer
            array('121564564', '121564564'),
            array('121564564.0121', '121564564.0121'),
            array(121564564.0121, 121564564.0121),
            array(12121, 12121),
            // sanitize HTML
            array(
                "<test toto='mama' piwik=\"cool\">Piwik!!!!!</test>",
                "&lt;test toto=&#039;mama&#039; piwik=&quot;cool&quot;&gt;Piwik!!!!!&lt;/test&gt;"
            ),
            // sanitize a SQL query
            array(
                "SELECT piwik FROM piwik_tests where test= 'super\"value' AND cool=toto #comment here",
                "SELECT piwik FROM piwik_tests where test= &#039;super&quot;value&#039; AND cool=toto #comment here"
            ),
            // sanitize php variables
            array(true, true),
            array(false, false),
            array(null, null),
            array("", ""),
        );
    }

    /**
     * @dataProvider getInputValues
     * @group Core
     * @group Common
     * @group sanitizeInputValues
     */
    public function testSanitizeInputValues($input, $output)
    {
        if (version_compare(PHP_VERSION, '5.4') < 0) {
            $this->assertTrue(@set_magic_quotes_runtime(1));
            $this->assertEquals(1, @get_magic_quotes_runtime());
            $this->assertEquals($output, Piwik_Common::sanitizeInputValues($input));

            $this->assertTrue(@set_magic_quotes_runtime(0));
            $this->assertEquals(0, @get_magic_quotes_runtime());
            $this->assertEquals($output, Piwik_Common::sanitizeInputValues($input));
        }
    }

    /**
     * emptyvarname => exception
     * @group Core
     * @group Common
     * @group getRequestVar
     */
    public function testGetRequestVarEmptyVarName()
    {
        try {
            $_GET[''] = 1;
            Piwik_Common::getRequestVar('');
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * nodefault Notype Novalue => exception
     * @group Core
     * @group Common
     * @group getRequestVar
     */
    public function testGetRequestVarNoDefaultNoTypeNoValue()
    {
        try {
            Piwik_Common::getRequestVar('test');
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * nodefault Notype WithValue => value
     * @group Core
     * @group Common
     * @group getRequestVar
     */
    public function testGetRequestVarNoDefaultNoTypeWithValue()
    {
        $_GET['test'] = 1413.431413;
        $this->assertEquals($_GET['test'], Piwik_Common::getRequestVar('test'));

    }

    /**
     * nodefault Withtype WithValue => exception cos type not matching
     * @group Core
     * @group Common
     * @group getRequestVar
     */
    public function testGetRequestVarNoDefaultWithTypeWithValue()
    {
        try {
            $_GET['test'] = 1413.431413;
            Piwik_Common::getRequestVar('test', null, 'string');
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');
    }

    /**
     * nodefault Withtype WithValue => exception cos type not matching
     * @group Core
     * @group Common
     * @group getRequestVar
     */
    public function testGetRequestVarNoDefaultWithTypeWithValue2()
    {
        try {
            Piwik_Common::getRequestVar('test', null, 'string');
        } catch (Exception $e) {
            return;
        }
        $this->fail('Expected exception not raised');

    }

    /**
     * Dataprovider for testGetRequestVar
     */
    public function getRequestVarValues()
    {
        return array( // value of request var, default value, var type, expected
            array(1413.431413, 2, 'int', 2), // withdefault Withtype WithValue => value casted as type
            array(null, 'default', null, 'default'), // withdefault Notype NoValue => default value
            array(null, 'default', 'string', 'default'), // withdefault Withtype NoValue =>default value casted as type
            // integer as a default value / types
            array('', 45, 'int', 45),
            array(1413.431413, 45, 'int', 45),
            array('', 45, 'integer', 45),
            array('', 45.0, 'float', 45.0),
            array('', 45.25, 'float', 45.25),
            // string as a default value / types
            array('1413.431413', 45, 'int', 45),
            array('1413.431413', 45, 'string', '1413.431413'),
            array('', 45, 'string', '45'),
            array('', 'geaga', 'string', 'geaga'),
            array('', '&#039;}{}}{}{}&#039;', 'string', '&#039;}{}}{}{}&#039;'),
            array('', 'http://url?arg1=val1&arg2=val2', 'string', 'http://url?arg1=val1&amp;arg2=val2'),
            array('http://url?arg1=val1&arg2=val2', 'http://url?arg1=val1&arg2=val4', 'string', 'http://url?arg1=val1&amp;arg2=val2'),
            array(array("test", 1345524, array("gaga")), array(), 'array', array("test", 1345524, array("gaga"))), // array as a default value / types
            array(array("test", 1345524, array("gaga")), 45, 'string', "45"),
            array(array("test", 1345524, array("gaga")), array(1), 'array', array("test", 1345524, array("gaga"))),
            array(array("test", 1345524, array("gaga")), 4, 'int', 4),
            array('', array(1), 'array', array(1)),
            array('', array(), 'array', array()),
            // we give a number in a string and request for a number => it should give the string casted as a number
            array('45645646', 1, 'int', 45645646),
            array('45645646', 45, 'integer', 45645646),
            array('45645646', '45454', 'string', '45645646'),
            array('45645646', array(), 'array', array()),
        );
    }

    /**
     * @dataProvider getRequestVarValues
     * @group Core
     * @group Common
     * @group getRequestVar
     */
    public function testGetRequestVar($varValue, $default, $type, $expected)
    {
        $_GET['test'] = $varValue;
        $return = Piwik_Common::getRequestVar('test', $default, $type);
        $this->assertEquals($expected, $return);
        // validate correct type
        switch ($type) {
            case 'int':
            case 'integer':
                $this->assertTrue(is_int($return));
                break;
            case 'float':
                $this->assertTrue(is_float($return));
                break;
            case 'string':
                $this->assertTrue(is_string($return));
                break;
            case 'array':
                $this->assertTrue(is_array($return));
                break;
        }
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
     * @group Common
     * @group getParameterFromQueryString
     */
    public function testGetParameterFromQueryString($queryString, $parameter, $expected)
    {
        $this->assertSame($expected, Piwik_Common::getParameterFromQueryString($queryString, $parameter));
    }

    /**
     * @group Core
     * @group Common
     * @group getPathAndQueryFromUrl
     */
    public function testGetPathAndQueryFromUrl()
    {
        $this->assertEquals('test/index.php?module=CoreHome', Piwik_Common::getPathAndQueryFromUrl('http://piwik.org/test/index.php?module=CoreHome'));
    }

    /**
     * @group Core
     * @group Common
     * @group getArrayFromQueryString
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
        $this->assertEquals(serialize($expected), serialize(Piwik_Common::getArrayFromQueryString('a&b=&c=1&d[]&e[]=&f[]=a&g[]=b&g[]=c')));
    }

    /**
     * @group Core
     * @group Common
     * @group isValidFilename
     */
    public function testIsValidFilenameValidValues()
    {
        $valid = array(
            "test", "test.txt", "test.......", "en-ZHsimplified",
        );
        foreach ($valid as $toTest) {
            $this->assertTrue(Piwik_Common::isValidFilename($toTest), $toTest . " not valid!");
        }
    }

    /**
     * @group Core
     * @group Common
     * @group isValidFilename
     */
    public function testIsValidFilenameNotValidValues()
    {
        $notvalid = array(
            "../test", "/etc/htpasswd", '$var', ';test', '[bizarre]', '', ".htaccess", "very long long eogaioge ageja geau ghaeihieg heiagie aiughaeui hfilename",
        );
        foreach ($notvalid as $toTest) {
            $this->assertFalse(Piwik_Common::isValidFilename($toTest), $toTest . " valid but shouldn't!");
        }
    }

    /**
     * Dataprovider for testGetBrowserLanguage
     */
    public function getBrowserLanguageData()
    {
        return array( // user agent, browser language
            array("en-gb", "en-gb"),

            // filter quality attribute
            array("en-us,en;q=0.5", "en-us,en"),

            // bad user agents
            array("en-us,chrome://global/locale/intl.properties", "en-us"),

            // unregistered language tag
            array("en,en-securid", "en"),
            array("en-securid,en", "en"),
            array("en-us,en-securid,en", "en-us,en"),

            // accept private sub tags
            array("en-us,x-en-securid", "en-us,x-en-securid"),
            array("en-us,en-x-securid", "en-us,en-x-securid"),

            // filter arbitrary white space
            array("en-us, en", "en-us,en"),
            array("en-ca, en-us ,en", "en-ca,en-us,en"),

            // handle comments
            array(" ( comment ) en-us (another comment) ", "en-us"),

            // handle quoted pairs (embedded in comments)
            array(" ( \( start ) en-us ( \) end ) ", "en-us"),
            array(" ( \) en-ca, \( ) en-us ( \) ,en ) ", "en-us"),
        );
    }

    /**
     * @dataProvider getBrowserLanguageData
     * @group Core
     * @group Common
     * @group getBrowserLanguage
     */
    public function testGetBrowserLanguage($useragent, $browserLanguage)
    {
        $res = Piwik_Common::getBrowserLanguage($useragent);
        $this->assertEquals($browserLanguage, $res);
    }

    /**
     * Dataprovider for testExtractCountryCodeFromBrowserLanguage
     */
    public function getCountryCodeTestData()
    {

        return array( // browser language, valid countries, expected result
            array("", array(), "xx"),
            array("", array("us" => 'amn'), "xx"),
            array("en", array("us" => 'amn'), "xx"),
            array("en-us", array("us" => 'amn'), "us"),
            array("en-ca", array("us" => 'amn'), "xx"),
            array("en-ca", array("us" => 'amn', "ca" => 'amn'), "ca"),
            array("fr-fr,fr-ca", array("us" => 'amn', "ca" => 'amn'), "ca"),
            array("fr-fr;q=1.0,fr-ca;q=0.9", array("us" => 'amn', "ca" => 'amn'), "ca"),
            array("fr-ca,fr;q=0.1", array("us" => 'amn', "ca" => 'amn'), "ca"),
            array("en-us,en;q=0.5", Piwik_Common::getCountriesList(), "us"),
            array("fr-ca,fr;q=0.1", array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "ca"),
            array("fr-fr,fr-ca", array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "fr")
        );
    }

    /**
     * @dataProvider getCountryCodeTestData
     * @group Core
     * @group Common
     * @group extractCountryCodeFromBrowserLanguage
     */
    public function testExtractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, $expected)
    {
        include 'DataFiles/LanguageToCountry.php';

        $this->assertEquals($expected, Piwik_Common::extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, true));
        $this->assertEquals($expected, Piwik_Common::extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, false));
    }

    /**
     * Dataprovider for testExtractCountryCodeFromBrowserLanguageInfer
     */
    public function getCountryCodeTestDataInfer()
    {

        return array( // browser language, valid countries, expected result (non-guess vs guess)
            array("fr,en-us", array("us" => 'amn', "ca" => 'amn'), "us", "fr"),
            array("fr,en-us", array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "us", "fr"),
            array("fr,fr-fr,en-us", array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "fr", "fr"),
            array("fr-fr,fr,en-us", array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "fr", "fr")
        );
    }

    /**
     * @dataProvider getCountryCodeTestDataInfer
     * @group Core
     * @group Common
     * @group extractCountryCodeFromBrowserLanguage
     */
    public function testExtractCountryCodeFromBrowserLanguageInfer($browserLanguage, $validCountries, $expected, $expectedInfer)
    {
        include "DataFiles/LanguageToCountry.php";

        // do not infer country from language
        $this->assertEquals($expected, Piwik_Common::extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, $enableLanguageToCountryGuess = false));

        // infer country from language
        $this->assertEquals($expectedInfer, Piwik_Common::extractCountryCodeFromBrowserLanguage($browserLanguage, $validCountries, $enableLanguageToCountryGuess = true));
    }

    /**
     * Dataprovider for testExtractLanguageCodeFromBrowserLanguage
     */
    public function getLanguageDataToExtract()
    {
        return array( // browser language, valid languages, expected result
            array("fr-ca", array("fr"), "fr"),
            array("", array(), "xx"),
            array("", array("en"), "xx"),
            array("fr", array("en"), "xx"),
            array("en", array("en"), "en"),
            array("en-ca", array("en-ca"), "en-ca"),
            array("en-ca", array("en"), "en"),
            array("fr,en-us", array("fr", "en"), "fr"),
            array("fr,en-us", array("en", "fr"), "fr"),
            array("fr-fr,fr-ca", array("fr"), "fr"),
            array("fr-fr,fr-ca", array("fr-ca"), "fr-ca"),
            array("fr-fr;q=1.0,fr-ca;q=0.9", array("fr-ca"), "fr-ca"),
            array("fr-ca,fr;q=0.1", array("fr-ca"), "fr-ca"),
            array("r5,fr;q=1,de", array("fr", "de"), "fr"),
            array("Zen§gq1", array("en"), "xx"),
        );
    }

    /**
     * @dataProvider getLanguageDataToExtract
     * @group Core
     * @group Common
     * @group extractLanguageCodeFromBrowserLanguage
     */
    public function testExtractLanguageCodeFromBrowserLanguage($browserLanguage, $validLanguages, $expected)
    {
        $this->assertEquals($expected, Piwik_Common::extractLanguageCodeFromBrowserLanguage($browserLanguage, $validLanguages), "test with {$browserLanguage} failed, expected {$expected}");
    }

    /**
     * @group Core
     * @group Common
     */
    public function testSearchEnginesDefinedCorrectly()
    {
        include "DataFiles/SearchEngines.php";

        $searchEngines = array();
        foreach ($GLOBALS['Piwik_SearchEngines'] as $host => $info) {
            if (isset($info[2]) && $info[2] !== false) {
                $this->assertTrue(strrpos($info[2], "{k}") !== false, $host . " search URL is not defined correctly, must contain the macro {k}");
            }

            if (!array_key_exists($info[0], $searchEngines)) {
                $searchEngines[$info[0]] = true;

                $this->assertTrue(strpos($host, '{}') === false, $host . " search URL is the master record and should not contain {}");
            }

            if (isset($info[3]) && $info[3] !== false) {
                $this->assertTrue(is_array($info[3]) || is_string($info[3]), $host . ' encoding must be either a string or an array');

                if (is_string($info[3])) {
                    $this->assertTrue(trim($info[3]) !== '', $host . ' encoding cannot be an empty string');
                    $this->assertTrue(strpos($info[3], ' ') === false, $host . ' encoding cannot contain spaces');

                }

                if (is_array($info[3])) {
                    $this->assertTrue(count($info[3]) > 0, $host . ' encodings cannot be an empty array');
                    $this->assertTrue(strpos(serialize($info[3]), '""') === false, $host . ' encodings in array cannot be empty stringss');
                    $this->assertTrue(strpos(serialize($info[3]), ' ') === false, $host . ' encodings in array cannot contain spaces');
                }
            }
        }
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
     * @group Common
     * @group extractSearchEngineInformationFromUrl
     */
    public function testExtractSearchEngineInformationFromUrl($url, $engine, $keywords)
    {
        $this->includeDataFilesForSearchEngineTest();
        $returnedValue = Piwik_Common::extractSearchEngineInformationFromUrl($url);

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
     * @group Common
     * @group getLossyUrl
     */
    public function testGetLossyUrl($input, $expected)
    {
        $this->assertEquals($expected, Piwik_Common::getLossyUrl($input));
    }
    
    private function includeDataFilesForSearchEngineTest()
    {
        include "DataFiles/SearchEngines.php";
        include "DataFiles/Countries.php";
    }
}
