<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

require_once 'Common.php';
class Test_Piwik_Common extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
		$_REQUEST = $_GET = $_POST = array();
	}
	
	public function tearDown()
	{
	}

	function test_isUrl()
	{
		$valid = array(
			'http://piwik.org',
			'http://www.piwik.org',
			'https://piwik.org',
			'https://piwik.org/dir/dir2/?oeajkgea7aega=&ge=a',
			'ftp://www.pi-wik.org',
			'news://www.pi-wik.org',
		);
		
		foreach($valid as $url)
		{
			$this->assertTrue(Piwik_Common::isLookLikeUrl($url), "$url not validated");
		}
	}
	function test_isUrl_notvalid()
	{
		$notValid = array(
			'it doesnt look like url',
			'/index?page=test',
			'test.html',
			
		);
		
		foreach($notValid as $url)
		{
			$this->assertTrue(!Piwik_Common::isLookLikeUrl($url), "$url validated");
		}
	}
	
	// sanitize an array OK
	function test_sanitizeInputValues_array1()
	{
		$a1 = array('test1' => 't1', 't45', "teatae''", 4568, array('test'), 1.52);
		$this->assertEqual( $a1, Piwik_Common::sanitizeInputValues($a1));
	}
	
	// sanitize an array OK
	function test_sanitizeInputValues_array2()
	{
		$a1 = array('test1' => 't1', 't45', "teatae''", 4568, array('test'), 1.52,
				array('test1' => 't1', 't45', "teatae''", 4568, array('test'), 1.52),
				array('test1' => 't1', 't45', "teatae''", 4568, array('test'), 1.52),
				array( array(array(array('test1' => 't1', 't45', "teatae''", 4568, array('test'), 1.52)))
				));
		$this->assertEqual( $a1, Piwik_Common::sanitizeInputValues($a1));
	}
	
	// sanitize an array with bad value level1
	function test_sanitizeInputValues_arrayBadValueL1()
	{
		$a1 = array('test1' => 't1', 't45', 'tea1"ta"e', 568, 1 => array('t<e"st'), 1.52);
		$a1OK = array('test1' => 't1', 't45', 'tea1&quot;ta&quot;e', 568, 1 => array('t&lt;e&quot;st'), 1.52);
		
		$this->assertEqual( $a1OK, Piwik_Common::sanitizeInputValues($a1));
		
	}
	
	// sanitize an array with bad value level2
	function test_sanitizeInputValues_arrayBadValueL2()
	{
		$a1 = array('tea1"ta"e' => array('t<e"st' => array('tgeag454554"t')), 1.52);
		$a1OK = array('tea1&quot;ta&quot;e' => array('t&lt;e&quot;st' => array('tgeag454554&quot;t')), 1.52);
		
		$this->assertEqual( $a1OK, Piwik_Common::sanitizeInputValues($a1));
	}
	
	// sanitize a string unicode => no change
	function test_sanitizeInputValues_arrayBadValueutf8()
	{
		$a1 =   " Поиск в Интернете  Поgqegиск страниц на рgeqg8978усском";
		$a1OK = " Поиск в Интернете  Поgqegиск страниц на рgeqg8978усском";

		$this->assertEqual( $a1OK, Piwik_Common::sanitizeInputValues($a1));
	}
	
	// sanitize a bad string
	function test_sanitizeInputValues_badString()
	{
		$string = '& " < > 123abc\'';
		$stringOK = '&amp; &quot; &lt; &gt; 123abc\'';
		$this->assertEqual($stringOK, Piwik_Common::sanitizeInputValues($string));

	}
	// sanitize an integer
	function test_sanitizeInputValues_badInteger()
	{
		$string = '121564564';
		$this->assertEqual($string, Piwik_Common::sanitizeInputValues($string));
		$string = '121564564.0121';
		$this->assertEqual($string, Piwik_Common::sanitizeInputValues($string));
		$string = 121564564.0121;
		$this->assertEqual($string, Piwik_Common::sanitizeInputValues($string));
		$string = 12121;
		$this->assertEqual($string, Piwik_Common::sanitizeInputValues($string));
		
	}
	
	// sanitize HTML 
	function test_sanitizeInputValues_HTML()
	{
		$html = "<test toto='mama' piwik=\"cool\">Piwik!!!!!</test>";
		$htmlOK = "&lt;test toto='mama' piwik=&quot;cool&quot;&gt;Piwik!!!!!&lt;/test&gt;";
		$this->assertEqual($htmlOK, Piwik_Common::sanitizeInputValues($html));
	}
	
	// sanitize a SQL query
	function test_sanitizeInputValues_SQLQuery()
	{
		$sql = "SELECT piwik FROM piwik_tests where test= 'super\"value' AND cool=toto #comment here";
		$sqlOK = "SELECT piwik FROM piwik_tests where test= 'super&quot;value' AND cool=toto #comment here";
		$this->assertEqual($sqlOK, Piwik_Common::sanitizeInputValues($sql));
	}
	
	// sanitize php variables
	function test_sanitizeInputValues_php()
	{
		$a = true;
		$b = true;
		$this->assertEqual($b, Piwik_Common::sanitizeInputValues($a));
		$a = false;
		$b = false;
		$this->assertEqual($b, Piwik_Common::sanitizeInputValues($a));
		$a = null;
		$b = null;
		$this->assertEqual($b, Piwik_Common::sanitizeInputValues($a));
		$a = "";
		$b = "";
		$this->assertEqual($b, Piwik_Common::sanitizeInputValues($a));
	}
	
	
	// sanitize with magic quotes runtime on => shouldnt affect the result
	function test_sanitizeInputValues_magicquotesON()
	{
		$this->assertTrue(set_magic_quotes_runtime(1));
		$this->assertTrue(get_magic_quotes_runtime(), 1);
		
		$this->test_sanitizeInputValues_array1();
		$this->test_sanitizeInputValues_array2();
		$this->test_sanitizeInputValues_badString();
		$this->test_sanitizeInputValues_HTML();
	}
	
	// sanitize with magic quotes off
	function test_sanitizeInputValues_magicquotesOFF()
	{
		
		$this->assertTrue(set_magic_quotes_runtime(0));
		$this->assertEqual(get_magic_quotes_runtime(), 0);
		$this->test_sanitizeInputValues_array1();
		$this->test_sanitizeInputValues_array2();
		$this->test_sanitizeInputValues_badString();
		$this->test_sanitizeInputValues_HTML();
		
		
	}
	
    /**
     * emptyvarname => exception
     */
    function test_getRequestVar_emptyVarName()
    {
    	$_REQUEST['']=1;
    	try {
    		$test = Piwik_Common::getRequestVar('');
        	$this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		return;
        }
    }
	
    /**
     * nodefault Notype Novalue => exception
     */
    function test_getRequestVar_nodefaultNotypeNovalue()
    {
    	try {
    		$test = Piwik_Common::getRequestVar('test');
        	$this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		return;
        }
    }
	
    /**
     *nodefault Notype WithValue => value
     */
    function test_getRequestVar_nodefaultNotypeWithValue()
    {
    	$_REQUEST['test'] = 1413.431413;
    	$this->assertEqual( Piwik_Common::getRequestVar('test'), $_REQUEST['test']);
    	
    }
	
    /**
     * nodefault Withtype WithValue => exception cos type not matching
     */
    function test_getRequestVar_nodefaultWithtypeWithValue()
    {
    	$_REQUEST['test'] = 1413.431413;
    	
    	try {
    		$this->assertEqual( Piwik_Common::getRequestVar('test', null, 'string'), 
    						(string)$_REQUEST['test']);
        	$this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		return;
        }
    	
    }
	
	
    /**
     * nodefault Withtype WithValue => exception cos type not matching
     */
    function test_getRequestVar_nodefaultWithtypeWithValue2()
    {    	
    	try {
    		$this->assertEqual( Piwik_Common::getRequestVar('test', null, 'string'), 
    						null);
        	$this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		return;
        }
    }
	
    /**
     * withdefault Withtype WithValue => value casted as type
     */
    function test_getRequestVar_withdefaultWithtypeWithValue()
    {
    	
    	$_REQUEST['test'] = 1413.431413;
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 2, 'int'), 
    						2);
    }
	
    /**
     * withdefault Notype NoValue => default value
     */
    function test_getRequestVar_withdefaultNotypeNoValue()
    {
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 'default'), 
    						'default');
    }
	
    /**
     * withdefault Withtype NoValue =>default value casted as type
     */
    function test_getRequestVar_withdefaultWithtypeNoValue()
    {
    	
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 'default', 'string'), 
    						'default');
    }
	
    /**
     * integer as a default value / types
     * several tests
     */
    function test_getRequestVar_integerdefault()
    {
    	$_REQUEST['test'] = 1413.431413;
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'int'), 45);
    	$_REQUEST['test'] = '';
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'int'), 45);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'integer'), 45);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'numeric'), 45);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'float'), 45);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45.25, 'float'), 45.25);
    }
	
    /**
     * string as a default value / types
     * several tests
     */
    function test_getRequestVar_stringdefault()
    {
    	$_REQUEST['test'] = "1413.431413";
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'int'), 45);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'string'), "1413.431413");
    	$_REQUEST['test'] = '';
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'string'), '45');
    	$this->assertEqual( Piwik_Common::getRequestVar('test', "geaga", 'string'), "geaga");
    	$this->assertEqual( Piwik_Common::getRequestVar('test', "'}{}}{}{}'", 'string'), "'}{}}{}{}'");
    	
    }
	
    /**
     * array as a default value / types
     * several tests
     *
     */
    function test_getRequestVar_arraydefault()
    {
    	$test = array("test", 1345524, array("gaga"));
    	$_REQUEST['test'] = $test;
    	
    	$this->assertEqual( Piwik_Common::getRequestVar('test', array(), 'array'), $test);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'string'), "45");
    	$this->assertEqual( Piwik_Common::getRequestVar('test', array(1), 'array'), $test);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 4, 'int'), 4);
    	
    	$_REQUEST['test'] = '';
    	$this->assertEqual( Piwik_Common::getRequestVar('test', array(1), 'array'), array(1));
    	$this->assertEqual( Piwik_Common::getRequestVar('test', array(), 'array'), array());
    }
	
    /**
     * we give a number in a string and request for a number 
     * 	=> it should give the string casted as a number
     *
     */
    function test_getRequestVar_stringedNumericCastedNumeric()
    {
    	$test = "45645646";
    	$_REQUEST['test'] = $test;
    	
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 1, 'int'), 45645646);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 45, 'integer'), 45645646);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', 0, 'numeric'), 45645646);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', "45454", 'string'), $test);
    	$this->assertEqual( Piwik_Common::getRequestVar('test', array(), 'array'), array());
    	
    }
    
    
    
    /**
     * no query string => false
     */
    function test_getParameterFromQueryString_noQuerystring()
    {
    	$urlQuery = "";
    	$urlQuery = htmlentities($urlQuery);
    	$parameter = "test''";
    	$result = Piwik_Common::getParameterFromQueryString( $urlQuery, $parameter);
    	$expectedResult = false;
    	$this->assertEqual($result, $expectedResult);
    }
    
    /**
     * param not found => false
     */
    function test_getParameterFromQueryString_parameternotfound()
    {
    	
    	$urlQuery = "toto=mama&mama=titi";
    	$urlQuery = htmlentities($urlQuery);
    	$parameter = "tot";
    	$result = Piwik_Common::getParameterFromQueryString( $urlQuery, $parameter);
    	$expectedResult = false;
    	$this->assertEqual($result, $expectedResult);
    }
    
    /**
     * empty parameter value => returns empty string
     */
    function test_getParameterFromQueryString_emptyParamValue()
    {
    	
    	$urlQuery = "toto=mama&mama=&tuytyt=teaoi";
    	$urlQuery = htmlentities($urlQuery);
    	$parameter = "mama";
    	$result = Piwik_Common::getParameterFromQueryString( $urlQuery, $parameter);
    	$expectedResult = '';
    	$this->assertEqual($result, $expectedResult);
    }
    
    /**
     * twice the parameter => returns the last value in the url
     */
    function test_getParameterFromQueryString_twiceTheParameterInQuery()
    {
    	
    	$urlQuery = "toto=mama&mama=&tuytyt=teaoi&toto=mama second value";
    	$urlQuery = htmlentities($urlQuery);
    	$parameter = "toto";
    	$result = Piwik_Common::getParameterFromQueryString( $urlQuery, $parameter);
    	$expectedResult = 'mama second value';
    	$this->assertEqual($result, $expectedResult);
    }
    
    /**
     * normal use case => parameter found
     */
    function test_getParameterFromQueryString_normalCase()
    {
    	
    	$urlQuery = "toto=mama&mama=&tuytyt=teaoi&toto=mama second value";
    	$urlQuery = htmlentities($urlQuery);
    	$parameter = "tuytyt";
    	$result = Piwik_Common::getParameterFromQueryString( $urlQuery, $parameter);
    	$expectedResult = 'teaoi';
    	$this->assertEqual($result, $expectedResult);
    }
    
    /**
     * normal use case with a string with many strange characters
     */
    function test_getParameterFromQueryString_strangeChars()
    {
    	
    	$urlQuery = 'toto=mama&mama=&tuytyt=Поиск в Интернете  Поиск страниц на русском _*()!$!£$^!£$%&toto=mama second value';
    	$urlQuery = htmlentities($urlQuery);
    	$parameter = "tuytyt";
    	$result = Piwik_Common::getParameterFromQueryString( $urlQuery, $parameter);
    	$expectedResult = 'Поиск в Интернете  Поиск страниц на русском _*()!$!£$^!£$%';
    	$expectedResult = htmlentities($expectedResult);
    	$this->assertEqual($result, $expectedResult);
    }
    
    
    public function test_isValidFilenameValidValues()
    {
    
    	$valid = array(
    			 "test", "test.txt","test.......", "en-ZHsimplified",
    		);
    	foreach($valid as $toTest)
    	{
    		$this->assertTrue(Piwik_Common::isValidFilename($toTest), $toTest." not valid!");
    	}
    }
    public function test_isValidFilenameNotValidValues()
    {
    
    	$notvalid = array(
    			"../test", "/etc/htpasswd", '$var', ';test', '[bizarre]', '', ".htaccess", "very long long eogaioge ageja geau ghaeihieg heiagie aiughaeui hfilename",
    		);
    	foreach($notvalid as $toTest)
    	{
    		$this->assertFalse(Piwik_Common::isValidFilename($toTest), $toTest." valid but shouldn't!");
    	}
    }
    
}

