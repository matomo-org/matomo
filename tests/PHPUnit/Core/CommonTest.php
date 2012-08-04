<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
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
                            array( array(array(array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52)))
                            )),
                          array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52,
                            array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52),
                            array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52),
                            array( array(array(array('test1' => 't1', 't45', "teatae", 4568, array('test'), 1.52)))
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
                    array( '121564564', '121564564' ),
                    array( '121564564.0121', '121564564.0121' ),
                    array( 121564564.0121, 121564564.0121 ),
                    array( 12121, 12121 ),
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
                    array( true, true ),
                    array( false, false ),
                    array( null, null ),
                    array( "", "" ),
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
        if (version_compare(PHP_VERSION, '5.4') < 0)
        {
            $this->assertTrue(@set_magic_quotes_runtime(1));
            $this->assertEquals(1, @get_magic_quotes_runtime());
            $this->assertEquals( $output, Piwik_Common::sanitizeInputValues($input));
        
            $this->assertTrue(@set_magic_quotes_runtime(0));
            $this->assertEquals(0, @get_magic_quotes_runtime());
            $this->assertEquals( $output, Piwik_Common::sanitizeInputValues($input));
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
            $_GET['']=1;
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
        switch($type) {
            case 'int':
            case 'integer':
                $this->assertTrue( is_int($return) );
                break;
            case 'float':
                $this->assertTrue( is_float($return) );
                break;
            case 'string':
                $this->assertTrue( is_string($return) );
                break;
            case 'array':
                $this->assertTrue( is_array($return) );
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
            'd' => array( false ),
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
                 "test", "test.txt","test.......", "en-ZHsimplified",
            );
        foreach($valid as $toTest)
        {
            $this->assertTrue(Piwik_Common::isValidFilename($toTest), $toTest." not valid!");
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
        foreach($notvalid as $toTest)
        {
            $this->assertFalse(Piwik_Common::isValidFilename($toTest), $toTest." valid but shouldn't!");
        }
    }

    /**
     * Dataprovider for testGetBrowserLanguage
     */
    public function getBrowserLanguageData()
    {
        return array( // user agent, browser language
                array( "en-gb", "en-gb" ),

                // filter quality attribute
                array( "en-us,en;q=0.5", "en-us,en" ),

                // bad user agents
                array( "en-us,chrome://global/locale/intl.properties", "en-us" ),

                // unregistered language tag
                array( "en,en-securid", "en" ),
                array( "en-securid,en", "en" ),
                array( "en-us,en-securid,en", "en-us,en" ),

                // accept private sub tags
                array( "en-us,x-en-securid", "en-us,x-en-securid" ),
                array( "en-us,en-x-securid", "en-us,en-x-securid" ),

                // filter arbitrary white space
                array( "en-us, en", "en-us,en" ),
                array( "en-ca, en-us ,en", "en-ca,en-us,en" ),

                // handle comments
                array( " ( comment ) en-us (another comment) ", "en-us" ),

                // handle quoted pairs (embedded in comments)
                array( " ( \( start ) en-us ( \) end ) ", "en-us" ),
                array( " ( \) en-ca, \( ) en-us ( \) ,en ) ", "en-us" ),
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
        $res = Piwik_Common::getBrowserLanguage( $useragent );
        $this->assertEquals( $browserLanguage, $res );
    }
    
    /**
     * Dataprovider for testExtractCountryCodeFromBrowserLanguage
     */
    public function getCountryCodeTestData() {
        
        return array( // browser language, valid countries, expected result
                array( "",                        array(),                                            "xx" ),
                array( "",                        array("us" => 'amn'),                               "xx" ),
                array( "en",                      array("us" => 'amn'),                               "xx" ),
                array( "en-us",                   array("us" => 'amn'),                               "us" ),
                array( "en-ca",                   array("us" => 'amn'),                               "xx" ),
                array( "en-ca",                   array("us" => 'amn', "ca" => 'amn'),                "ca" ),
                array( "fr-fr,fr-ca",             array("us" => 'amn', "ca" => 'amn'),                "ca" ),
                array( "fr-fr;q=1.0,fr-ca;q=0.9", array("us" => 'amn', "ca" => 'amn'),                "ca" ),
                array( "fr-ca,fr;q=0.1",          array("us" => 'amn', "ca" => 'amn'),                "ca" ),
                array( "en-us,en;q=0.5",          Piwik_Common::getCountriesList(),                   "us" ),
                array( "fr-ca,fr;q=0.1",          array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "ca" ),
                array( "fr-fr,fr-ca",             array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "fr" )
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
        
        $this->assertEquals( $expected, Piwik_Common::extractCountryCodeFromBrowserLanguage( $browserLanguage, $validCountries, true ));
        $this->assertEquals( $expected, Piwik_Common::extractCountryCodeFromBrowserLanguage( $browserLanguage, $validCountries, false ));
    }

    /**
     * Dataprovider for testExtractCountryCodeFromBrowserLanguageInfer
     */
    public function getCountryCodeTestDataInfer() {
        
        return array( // browser language, valid countries, expected result (non-guess vs guess)
                array( "fr,en-us",       array("us" => 'amn', "ca" => 'amn'),                "us", "fr" ),
                array( "fr,en-us",       array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "us", "fr" ),
                array( "fr,fr-fr,en-us", array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "fr", "fr" ),
                array( "fr-fr,fr,en-us", array("fr" => 'eur', "us" => 'amn', "ca" => 'amn'), "fr", "fr" )
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
        $this->assertEquals( $expected, Piwik_Common::extractCountryCodeFromBrowserLanguage( $browserLanguage, $validCountries, $enableLanguageToCountryGuess = false ));

        // infer country from language
        $this->assertEquals( $expectedInfer, Piwik_Common::extractCountryCodeFromBrowserLanguage( $browserLanguage, $validCountries, $enableLanguageToCountryGuess = true ));
    }

    /**
     * Dataprovider for testExtractLanguageCodeFromBrowserLanguage
     */
    public function getLanguageDataToExtract()
    {
        return array( // browser language, valid languages, expected result
                array( "fr-ca",                   array("fr"),       "fr"    ),
                array( "",                        array(),           "xx"    ),
                array( "",                        array("en"),       "xx"    ),
                array( "fr",                      array("en"),       "xx"    ),
                array( "en",                      array("en"),       "en"    ),
                array( "en-ca",                   array("en-ca"),    "en-ca" ),
                array( "en-ca",                   array("en"),       "en"    ),
                array( "fr,en-us",                array("fr", "en"), "fr"    ),
                array( "fr,en-us",                array("en", "fr"), "fr"    ),
                array( "fr-fr,fr-ca",             array("fr"),       "fr"    ),
                array( "fr-fr,fr-ca",             array("fr-ca"),    "fr-ca" ),
                array( "fr-fr;q=1.0,fr-ca;q=0.9", array("fr-ca"),    "fr-ca" ),
                array( "fr-ca,fr;q=0.1",          array("fr-ca"),    "fr-ca" ),
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
        $this->assertEquals( $expected, Piwik_Common::extractLanguageCodeFromBrowserLanguage( $browserLanguage, $validLanguages ), "test with {$browserLanguage} failed, expected {$expected}");
    }
    
    /**
     * @group Core
     * @group Common
     */
    public function testSearchEnginesDefinedCorrectly()
    {
        include "DataFiles/SearchEngines.php";

        $searchEngines = array();
        foreach($GLOBALS['Piwik_SearchEngines'] as $host => $info)
        {
            if(isset($info[2]) && $info[2] !== false)
            {
                $this->assertTrue(strrpos($info[2], "{k}") !== false, $host . " search URL is not defined correctly, must contain the macro {k}");
            }

            if(!array_key_exists($info[0], $searchEngines))
            {
                $searchEngines[$info[0]] = true;

                $this->assertTrue(strpos($host, '{}') === false, $host . " search URL is the master record and should not contain {}");
            }

            if(isset($info[3]) && $info[3] !== false)
            {
                $this->assertTrue(is_array($info[3]) || is_string($info[3]), $host . ' encoding must be either a string or an array');

                if (is_string($info[3]))
                {
                    $this->assertTrue(trim($info[3]) !== '', $host . ' encoding cannot be an empty string');
                    $this->assertTrue(strpos($info[3], ' ') === false, $host . ' encoding cannot contain spaces');

                }

                if (is_array($info[3]))
                {
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
        return array(
            // normal case
            array('http://uk.search.yahoo.com/search?p=piwik&ei=UTF-8&fr=moz2',
                  array('name' => 'Yahoo!', 'keywords' => 'piwik')),

            // test request trimmed and capitalized
            array('http://www.google.com/search?hl=en&q=+piWIk+&btnG=Google+Search&aq=f&oq=',
                  array('name' => 'Google', 'keywords' => 'piwik')),

            // testing special case of Google images
            array('http://images.google.com/imgres?imgurl=http://www.linux-corner.info/snapshot1.png&imgrefurl=http://www.oxxus.net/blog/archives/date/2007/10/page/41/&usg=__-xYvnp1IKpRZKjRDQVhpfExMkuM=&h=781&w=937&sz=203&hl=en&start=1&tbnid=P9LqKMIbdhlg-M:&tbnh=123&tbnw=148&prev=/images%3Fq%3Dthis%2Bmy%2Bquery%2Bwith%2Bhttp://domain%2Bname%2Band%2Bstrange%2Bcharacters%2B%2526%2B%255E%2B%257C%2B%253C%253E%2B%2525%2B%2522%2B%2527%2527%2BEOL%26gbv%3D2%26hl%3Den%26sa%3DG',
                  array('name' => 'Google Images', 'keywords' => 'this my query with http://domain name and strange characters & ^ | <> % " \'\' eol')),

            array('http://www.google.fr/search?hl=en&q=%3C%3E+%26test%3B+piwik+%26quot%3B&ei=GcXJSb-VKoKEsAPmnIjzBw&sa=X&oi=revisions_inline&ct=unquoted-query-link',
                  array('name' => 'Google', 'keywords' => '<> &test; piwik &quot;')),

            // testing Baidu special case (several variable names possible, and custom encoding)
            // see http://dev.piwik.org/trac/ticket/589

            // keyword is in "wd"
            array('http://www.baidu.com/s?ie=gb2312&bs=%BF%D5%BC%E4+hao123+%7C+%B8%FC%B6%E0%3E%3E&sr=&z=&cl=3&f=8&tn=baidu&wd=%BF%D5%BC%E4+%BA%C3123+%7C+%B8%FC%B6%E0%3E%3E&ct=0',
                  array('name' => 'Baidu', 'keywords' => '空间 好123 | 更多>>')),

            // keyword is in "word"
            array('http://www.baidu.com/s?kw=&sc=web&cl=3&tn=sitehao123&ct=0&rn=&lm=&ie=gb2312&rs2=&myselectvalue=&f=&pv=&z=&from=&word=%B7%E8%BF%F1%CB%B5%D3%A2%D3%EF+%D4%DA%CF%DF%B9%DB%BF%B4',
                  array('name' => 'Baidu', 'keywords' => '疯狂说英语 在线观看')),

            array('http://www.baidu.com/s?wd=%C1%F7%D0%D0%C3%C0%D3%EF%CF%C2%D4%D8',
                  array('name' => 'Baidu', 'keywords' => '流行美语下载')),

            // baidu utf-8
            array('http://www.baidu.com/s?ch=14&ie=utf-8&wd=%E4%BA%8C%E5%BA%A6%E5%AE%AB%E9%A2%88%E7%B3%9C%E7%83%82%E8%83%BD%E6%B2%BB%E5%A5%BD%E5%90%97%3F&searchRadio=on',
                  array('name' => 'Baidu', 'keywords' => '二度宫颈糜烂能治好吗?')),

            array('http://web.gougou.com/search?search=%E5%85%A8%E9%83%A8&id=1',
                  array('name' => 'Baidu', 'keywords' => '全部')),

            array('http://www.google.cn/search?hl=zh-CN&q=%E6%B5%8F%E8%A7%88%E5%85%AC%E4%BA%A4%E5%9C%B0%E9%93%81%E7%AB%99%E7%82%B9%E4%BF%A1%E6%81%AF&btnG=Google+%E6%90%9C%E7%B4%A2&meta=cr%3DcountryCN&aq=f&oq=',
                  array('name' => 'Google', 'keywords' => '浏览公交地铁站点信息')),

            // testing other exotic unicode characters
            array('http://www.yandex.com/yandsearch?text=%D1%87%D0%B0%D1%81%D1%82%D0%BE%D1%82%D0%B0+%D1%80%D0%B0%D1%81%D0%BF%D0%B0%D0%B4%D0%B0+%D1%81%D1%82%D0%B5%D0%BA%D0%BB%D0%B0&stpar2=%2Fh1%2Ftm11%2Fs1&stpar4=%2Fs1&stpar1=%2Fu0%27,%20%27%D1%87%D0%B0%D1%81%D1%82%D0%BE%D1%82%D0%B0+%D1%80%D0%B0%D1%81%D0%BF%D0%B0%D0%B4%D0%B0+%D1%81%D1%82%D0%B5%D0%BA%D0%BB%D0%B0',
                  array('name' => 'Yandex', 'keywords' => 'частота распада стекла')),

            array('http://www.yandex.ru/yandsearch?text=%D1%81%D0%BF%D0%BE%D1%80%D1%82%D0%B7%D0%B4%D1%80%D0%B0%D0%B2',
                  array('name' => 'Yandex', 'keywords' => 'спортздрав')),

            array('http://www.google.ge/search?hl=en&q=%E1%83%A1%E1%83%90%E1%83%A5%E1%83%90%E1%83%A0%E1%83%97%E1%83%95%E1%83%94%E1%83%9A%E1%83%9D&btnG=Google+Search',
                  array('name' => 'Google', 'keywords' => 'საქართველო')),

            // test multiple encodings per search engine (UTF-8, then Windows-1251)
            array('http://go.mail.ru/search?rch=e&q=%D0%B3%D0%BB%D1%83%D0%B1%D0%BE%D0%BA%D0%B8%D0%B5+%D0%BC%D0%B8%D0%BC%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%B8%D0%B5+%D0%BC%D0%BE%D1%80%D1%89%D0%B8%D0%BD%D1%8B',
                  array('name' => 'Mailru', 'keywords' => 'глубокие мимические морщины')),
            array('http://go.mail.ru/search?q=%F5%E8%EC%F1%EE%F1%F2%E0%E2%20%F0%E0%F1%F2%EE%F0%EE%EF%F8%E8',
                  array('name' => 'Mailru', 'keywords' => 'химсостав расторопши')),

            // new Google url formats
            array('http://www.google.com/url?sa=t&source=web&ct=res&cd=7&url=http%3A%2F%2Fwww.example.com%2Fmypage.htm&ei=0SjdSa-1N5O8M_qW8dQN&rct=j&q=flowers&usg=AFQjCNHJXSUh7Vw7oubPaO3tZOzz-F-u_w&sig2=X8uCFh6IoPtnwmvGMULQfw',
                  array('name' => 'Google', 'keywords' => 'flowers')),
            array('http://www.google.com/webhp?tab=mw#hl=en&source=hp&q=test+hash&btnG=Google+Search&aq=f&aqi=&aql=&oq=&fp=22b4dcbb1403dc0f',
                  array('name' => 'Google', 'keywords' => 'test hash')),
            array('http://www.google.com/#hl=en&source=hp&q=test+hash&aq=f&aqi=n1g5g-s1g1g-s1g2&aql=&oq=&fp=22b4dcbb1403dc0f',
                  array('name' => 'Google', 'keywords' => 'test hash')),
            array('http://www.google.com/reader/view/',
                  false),

            // new Google image format
            array('http://www.google.com/imgres?imgurl=http://www.imagedomain.com/zoom/34782_ZOOM.jpg&imgrefurl=http://www.mydomain.com/product/Omala-Govindra-Tank-XS-Brown-and-Chile.html&usg=__BD6z_JrJRAFjScDRhj4Tp8Vm_Zo=&h=610&w=465&sz=248&hl=en&start=3&itbs=1&tbnid=aiNVNce9-ZYAPM:&tbnh=136&tbnw=104&prev=/images%3Fq%3DFull%2BSupport%2BTummy%26hl%3Den%26safe%3Doff%26sa%3DG%26gbv%3D2%26tbs%3Disch:1',
                  array('name' => 'Google Images', 'keywords' => 'full support tummy')),

            array('http://www.google.com/imgres?imgurl=http://www.piwik-connector.com/en/wp-content/themes/analytics/images/piwik-connector.png&imgrefurl=http://www.piwik-connector.com/en/&usg=__ASwTaKUfneQEPcSMyGHp6PslPRo=&h=700&w=900&sz=40&hl=en&start=0&zoom=1&tbnid=K7nGMPzsg3iTHM:&tbnh=131&tbnw=168&ei=r9OpTc1lh96BB4bAgOsI&prev=/images%3Fq%3Dpiwik%26hl%3Den%26safe%3Doff%26biw%3D1280%26bih%3D828%26gbv%3D2%26tbm%3Disch&itbs=1&iact=rc&dur=1400&oei=r9OpTc1lh96BB4bAgOsI&page=1&ndsp=23&ved=1t:429,r:0,s:0&tx=125&ty=88',
                  array('name' => 'Google Images', 'keywords' => 'piwik')),

            array('http://www.google.com/search?tbm=isch&hl=en&source=hp&biw=1280&bih=793&q=piwik&gbv=2&oq=piwik&aq=f&aqi=g5g-s1g4&aql=&gs_sm=e&gs_upl=1526l2065l0l2178l5l4l0l0l0l0l184l371l1.2l3l0',
                  array('name' => 'Google Images', 'keywords' => 'piwik')),

            array('http://www.google.fr/imgres?q=piwik&um=1&hl=fr&client=firefox-a&sa=N&rls=org.mozilla:fr:official&tbm=isch&tbnid=Xmlv3vfl6ost2M:&imgrefurl=http://example.com&docid=sCbh1P0moOANNM&w=500&h=690&ei=3OFpTpjvH4T6sgbosYTiBA&zoom=1&iact=hc&vpx=176&vpy=59&dur=299&hovh=264&hovw=191&tx=108&ty=140&page=1&tbnh=140&tbnw=103&start=0&ndsp=39&ved=1t:429,r:0,s:0&biw=1280&bih=885',
                  array('name' => 'Google Images', 'keywords' => 'piwik')),

            // Other google URL
            array('http://www.google.fr/webhp?hl=fr&tab=ww#hl=fr&gs_nf=1&pq=dahab%20securite&cp=5&gs_id=2g&xhr=t&q=dahab&pf=p&sclient=tablet-gws&safe=off&tbo=d&site=webhp&oq=dahab&gs_l=&pbx=1&bav=on.2,or.r_gc.r_pw.&fp=f8f370e996c0cd5f&biw=768&bih=928&bs=1',
                  array('name' => 'Google', 'keywords' => 'dahab')),

            // Google CSE is not standard google
            array('http://www.google.com/cse?cx=006944612449134755049%3Ahq5up-97k4u&cof=FORID%3A10&q=piwik&ad=w9&num=10&rurl=http%3A%2F%2Fwww.homepagle.com%2Fsearch.php%3Fcx%3D006944612449134755049%253Ahq5up-97k4u%26cof%3DFORID%253A10%26q%3D89',
                  array('name' => 'Google Custom Search', 'keywords' => 'piwik')),
            array('http://www.google.com/cse?cx=012634963936527368460%3Aqdoghy8xaco&cof=FORID%3A11%3BNB%3A1&ie=UTF-8&query=geoip&form_id=google_cse_searchbox_form&sa=Search&ad=w9&num=10&rurl=http%3A%2F%2Fpiwik.org%2Fsearch%2F%3Fcx%3D012634963936527368460%253Aqdoghy8xaco%26cof%3DFORID%253A11%253BNB%253A1%26ie%3DUTF-8%26query%3Dgeoip%26form_id%3Dgoogle_cse_searchbox_form%26sa%3DSearch',
                  array('name' => 'Google Custom Search', 'keywords' => 'geoip')),
            array('http://www.google.com.hk/custom?cx=012634963936527368460%3Aqdoghy8xaco&cof=AH%3Aleft%3BCX%3APiwik%252Eorg%3BDIV%3A%23cccccc%3BFORID%3A11%3BL%3Ahttp%3A%2F%2Fwww.google.com%2Fintl%2Fen%2Fimages%2Flogos%2Fcustom_search_logo_sm.gif%3BLH%3A30%3BLP%3A1%3BVLC%3A%23551a8b%3B&ie=UTF-8&query=mysqli.so&form_id=google_cse_searchbox_form&sa=Search&ad=w9&num=10&adkw=AELymgUTLKONpMqPGM-LbgTWRFfzo9uEj92nMyhi08lOA-wvJ9odphte3hfn5Nz13067or397hodwjlupE3ziTpE1uCKhvuTfzH8e8OHp_IAz7YoBQU6YvuSD-YiwcdcfrGRLxrLPUI3&hl=en&oe=UTF-8&client=google-coop-np&boostcse=0&rurl=http://piwik.org/search/%3Fcx%3D012634963936527368460%253Aqdoghy8xaco%26cof%3DFORID%253A11%253BNB%253A1%26ie%3DUTF-8%26query%3Dmysqli.so%26form_id%3Dgoogle_cse_searchbox_form%26sa%3DSearch',
                  array('name' => 'Google Custom Search', 'keywords' => 'mysqli.so')),

            // Powered by Google CSE
            array('http://www.cathoogle.com/results?cx=partner-pub-6379407697620666%3Alil1v7i1hv0&cof=FORID%3A9&safe=active&q=i+love+piwik&sa=Cathoogle+Search&siteurl=www.cathoogle.com%2F#867',
                  array('name' => 'Google Custom Search', 'keywords' => 'i love piwik')),

            // Google advanced search
            array('http://www.google.ca/search?hl=en&as_q=web+analytics&as_epq=real+time&as_oq=gpl+open+source&as_eq=oracle&num=10&lr=&as_filetype=&ft=i&as_sitesearch=&as_qdr=all&as_rights=&as_occt=any&cr=&as_nlo=&as_nhi=&safe=images',
                  array('name' => 'Google', 'keywords' => 'web analytics gpl or open or source "real time" -oracle')),

            array('http://www.google.ca/search?as_q=web+analytics&as_epq=real+time&as_oq=gpl+open+source&as_eq=oracle&num=10&lr=&as_filetype=&ft=i&as_sitesearch=&as_qdr=all&as_rights=&as_occt=any&cr=&as_nlo=&as_nhi=&safe=images',
                  array('name' => 'Google', 'keywords' => 'web analytics gpl or open or source "real time" -oracle')),

            array('http://www.google.ca/url?sa=t&source=web&cd=1&ved=0CBQQFjAA&url=http%3A%2F%2Fwww.robocoder.ca%2F&rct=j&q=web%20analytics%20gpl%20OR%20open%20OR%20source%20%22real%20time%22%20-sco&ei=zv6KTILkGsG88gaxoqz9Cw&usg=AFQjCNEv2Mp3ruU8YCMI40Pqo9ijjXvsUA',
                  array('name' => 'Google', 'keywords' => 'web analytics gpl or open or source "real time" -sco')),

            // Google Images (advanced search)
            array('http://www.google.com/imgres?imgurl=http://www.softwaredevelopment.ca/software/wxtest-red.png&imgrefurl=http://www.softwaredevelopment.ca/wxtestrunner.shtml&usg=__feDWUbLINOfWzPieVKX1iN9uj3A=&h=432&w=615&sz=18&hl=en&start=0&zoom=1&tbnid=V8LgKlxE4zAJnM:&tbnh=143&tbnw=204&ei=w9apTdWzKoLEgQff27X9CA&prev=/images%3Fq%3Dbook%2Bsite:softwaredevelopment.ca%26um%3D1%26hl%3Den%26safe%3Doff%26client%3Dubuntu%26channel%3Dfs%26biw%3D1280%26bih%3D828%26as_st%3Dy%26tbm%3Disch&um=1&itbs=1&iact=hc&vpx=136&vpy=141&dur=19894&hovh=188&hovw=268&tx=124&ty=103&oei=w9apTdWzKoLEgQff27X9CA&page=1&ndsp=3&ved=1t:429,r:0,s:0',
                  array('name' => 'Google Images', 'keywords' => 'book site:softwaredevelopment.ca')),

            // Google Shopping
            array('http://www.google.com/search?q=cameras&tbm=shop&hl=en&aq=f',
                  array('name' => 'Google Shopping', 'keywords' => 'cameras')),

            // Google cache
            array('http://webcache.googleusercontent.com/search?q=cache:CD2SncROLs4J:piwik.org/blog/2010/04/piwik-0-6-security-advisory/+piwik+security&cd=1&hl=en&ct=clnk',
                  array('name' => 'Google', 'keywords' => 'piwik security')),

            // Bing (subdomains)
            array('http://ca.bing.com/search?q=piwik+web+analytics&go=&form=QBLH&filt=all&qs=n&sk=',
                  array('name' => 'Bing', 'keywords' => 'piwik web analytics')),
            array('http://ca.bing.com/images/search?q=anthon+pang&go=&form=QBIR&qs=n&sk=&sc=3-7',
                  array('name' => 'Bing Images', 'keywords' => 'anthon pang')),

            // Bing cache
            array('http://cc.bingj.com/cache.aspx?q=web+analytics&d=5020318678516316&mkt=en-CA&setlang=en-CA&w=6ea8ea88,ff6c44df',
                  array('name' => 'Bing', 'keywords' => 'web analytics')),

            // Bing Mobile
            array('http://m.bing.com/search/search.aspx?Q=piwik&d=&dl=&pq=&a=results&MID=8015',
                  array('name' => 'Bing', 'keywords' => 'piwik')),

            // Bing image search has a special URL
            array('http://www.bing.com/images/search?q=piwik&go=&form=QBIL',
                  array('name' => 'Bing Images', 'keywords' => 'piwik')),

            // Yahoo! Directory
            array('http://search.yahoo.com/search/dir?ei=UTF-8&p=analytics&h=c',
                  array('name' => 'Yahoo! Directory', 'keywords' => 'analytics')),


            // Bing mobile image search has a special URL
//			array('http://m.bing.com/search/search.aspx?A=imageresults&Q=piwik&D=Image&MID=8015&SI=0&PN=0&SCO=0',
//				array('name' => 'Bing Images', 'keywords' => 'piwik')),
//
//			// Yahoo (Bing-powered) cache
//			array('http://74.6.239.84/search/srpcache?ei=UTF-8&p=web+analytics&fr=yfp-t-715&u=http://cc.bingj.com/cache.aspx?q=web+analytics&d=5020318680482405&mkt=en-CA&setlang=en-CA&w=a68d7af0,873cfeb0&icp=1&.intl=ca&sig=x6MgjtrDYvsxi8Zk2ZX.tw--',
//				array('name' => 'Yahoo', 'keywords' => 'web analytics')),
//
//			array('http://74.6.239.185/search/srpcache?ei=UTF-8&p=piwik&fr=yfp-t-964&fp_ip=ca&u=http://cc.bingj.com/cache.aspx?q=piwik&d=4770519086662477&mkt=en-US&setlang=en-US&w=f4bc05d8,8c8af2e3&icp=1&.intl=us&sig=PXmPDNqapxSQ.scsuhIpZA--',
//				array('name' => 'Yahoo', 'keywords' => 'piwik')),


            // InfoSpace
            array('http://www.infospace.com/search/web?fcoid=417&fcop=topnav&fpid=27&q=piwik&ql=',
                  array('name' => 'InfoSpace', 'keywords' => 'piwik')),

            array('http://www.metacrawler.com/info.metac.test.b8/search/web?fcoid=417&fcop=topnav&fpid=27&q=real+time+web+analytics',
                  array('name' => 'InfoSpace', 'keywords' => 'real time web analytics')),

            // Powered by InfoSpace metasearch
            array('http://search.nation.com/pemonitorhosted/ws/results/Web/mobile analytics/1/417/TopNavigation/Source/iq=true/zoom=off/_iceUrlFlag=7?_IceUrl=true',
                  array('name' => 'InfoSpace', 'keywords' => 'mobile analytics')),

            array('http://wsdsold.infospace.com/pemonitorhosted/ws/results/Web/piwik/1/417/TopNavigation/Source/iq=true/zoom=off/_iceUrlFlag=7?_IceUrl=true',
                  array('name' => 'InfoSpace', 'keywords' => 'piwik')),

            // 123people
            array('http://www.123people.de/s/piwik',
                  array('name' => '123people', 'keywords' => 'piwik')),

            // msxml.excite.com (using regex)
            array('http://msxml.excite.com/excite/ws/results/Images/test/1/408/TopNavigation/Relevance/iq=true/zoom=off/_iceUrlFlag=7?_IceUrl=true&padv=qall%3dpiwik%26qphrase%3d%26qany%3d%26qnot%3d',
                  array('name' => 'Excite', 'keywords' => 'test')),

            array('http://search.mywebsearch.com/mywebsearch/GGmain.jhtml?searchFor=piwik&tpr=sbt&st=site&ptnrS=ZZ&ss=sub&gcht=',
                  array('name' => 'MyWebSearch', 'keywords' => 'piwik')),

            // Yahoo!
            array('http://us.yhs4.search.yahoo.com/yhs/search;_ylt=A0oG7qCW9ZhNdFQAuTQPxQt.?q=piwik',
                  array('name' => 'Yahoo!', 'keywords' => 'piwik')),

            array('http://us.nc.yhs.search.yahoo.com/if?p=piwik&partnerid=yhs-if-timewarner&fr=yhs-if-timewarner&ei=UTF-8&YST_b=7&vm=p',
                  array('name' => 'Yahoo!', 'keywords' => 'piwik')),

            // Babylon
            array('http://search.babylon.com/?q=piwik',
                  array('name' => 'Babylon', 'keywords' => 'piwik')),

            array('http://search.babylon.com/web/piwik',
                  array('name' => 'Babylon', 'keywords' => 'piwik')),

            // ask has country not at beginning
            array('http://images.de.ask.com/fr?q=piwik&qt=0',
                  array('name' => 'Ask', 'keywords' => 'piwik')),

            // test that master record is used to backfill subsequent rows
            array('http://www.baidu.com/?wd=test1',
                  array('name' => 'Baidu', 'keywords' => 'test1')),
            array('http://tieba.baidu.com/?kw=test2',
                  array('name' => 'Baidu', 'keywords' => 'test2')),
            array('http://web.gougou.com/?search=test3',
                  array('name' => 'Baidu', 'keywords' => 'test3')),

            // Google SSL hidden keyword not defined
            array('http://www.google.com/url?sa=t&rct=j&q=&esrc=s&source=web&cd=1&ved=0CC&url=http%3A%2F%2Fpiwik.org%2F&ei=&usg=',
                  array('name' => 'Google', 'keywords' => false)),

            // Yet another change http://googlewebmastercentral.blogspot.ca/2012/03/upcoming-changes-in-googles-http.html
            array('https://www.google.com/',
                  array('name' => 'Google', 'keywords' => false)),

            array('https://www.google.co.uk/',
                  array('name' => 'Google', 'keywords' => false)),

            // without trailing slash
            array('https://www.google.co.uk',
                  array('name' => 'Google', 'keywords' => false)),

            array('http://search.naver.com/search.naver?where=nexearch&query=FAU+&x=0&y=0&sm=top_hty&fbm=1&ie=utf8',
                  array('name' => 'Naver', 'keywords' => 'fau')),

            // DDG
            array('http://duckduckgo.com/post.html',
                  array('name' => 'DuckDuckGo', 'keywords' => false)),

            // Google images no keyword
            array('http://www.google.com/imgres?hl=en&client=ubuntu&hs=xDb&sa=X&channel=fs&biw=1920&bih=1084&tbm=isch&prmd=imvns&tbnid=5i7iz7u4LPSSrM:&imgrefurl=http://dev.piwik.org/trac/wiki/HowToSetupDevelopmentEnvironmentWindows&docid=tWN9OesMyOTqsM&imgurl=http://dev.piwik.org/trac/raw-attachment/wiki/HowToSetupDevelopmentEnvironmentWindows/eclipse-preview.jpg&w=1000&h=627&ei=pURoT67BEdT74QTUzYiSCQ&zoom=1&iact=hc&vpx=1379&vpy=548&dur=513&hovh=178&hovw=284&tx=134&ty=105&sig=108396332168858896950&page=1&tbnh=142&tbnw=227&start=0&ndsp=37&ved=1t:429,r:5,s:0',
                  array('name' => 'Google Images', 'keywords' => false)),

            // Google images no keyword next try
            array('http://www.google.fr/imgres?hl=en&biw=1680&bih=925&gbv=2&tbm=isch&tbnid=kBma1eg8aVOKoM:&imgrefurl=http://www.squido.com/research-keywords&docid=YSY3GQh3O8dkjM&imgurl=http://i3.squidocdn.com/resize/squidoo_images/590/draft_lens10233921module148408128photo_1298307262Research_keywords_6.jpg&w=590&h=412&ei=_OVZT4_3EInQ8gOWuqXbDg&zoom=1&iact=hc&vpx=164&vpy=205&dur=33&hovh=188&hovw=269&tx=137&ty=89&sig=113944581904793140725&page=1&tbnh=109&tbnw=156&start=0&ndsp=42&ved=1t:429,r:0,s:0www.google.fr/imgres?hl=en&biw=1680&bih=925&gbv=2&tbm=isch&tbnid=kBma1eg8aVOKoM:&imgrefurl=http://www.squido.com/research-keywords&docid=YSY3GQh3O8dkjM&imgurl=http://i3.squidocdn.com/resize/squidoo_images/590/draft_lens10233921module148408128photo_1298307262Research_keywords_6.jpg&w=590&h=412&ei=_OVZT4_3EInQ8gOWuqXbDg&zoom=1&iact=hc&vpx=164&vpy=205&dur=33&hovh=188&hovw=269&tx=137&ty=89&sig=113944581904793140725&page=1&tbnh=109&tbnw=156&start=0&ndsp=42&ved=1t:429,r:0,s:0',
                  array('name' => 'Google Images', 'keywords' => false)),
        );
    }
    
    /**
     * @dataProvider getSearchEngineUrls
     * @group Core
     * @group Common
     * @group extractSearchEngineInformationFromUrl
     */
    public function testExtractSearchEngineInformationFromUrl($referrerUrl, $expectedReturnedValue)
    {
        include "DataFiles/SearchEngines.php";
        include "DataFiles/Countries.php";
        $returnedValue = Piwik_Common::extractSearchEngineInformationFromUrl($referrerUrl);
        $this->assertEquals($expectedReturnedValue, $returnedValue);
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
}
