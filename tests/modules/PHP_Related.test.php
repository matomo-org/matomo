<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}


require_once 'Timer.php';		
class Test_PHP_Related extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
	}
	
	public function tearDown()
	{
	}
	
	function test_equal()
	{
		//aaaaaaaaaaaahhhhhhhhhhhh
		$this->assertTrue( "off" == true);
	}
	function test_listEach()
	{
		$array = array('key' => 'elem2');
		list($elem1,$elem2) = each($array);
		
		$this->assertEqual($elem1, 'key');
		$this->assertEqual($elem2, 'elem2');
	}
	function test_TMP()
	{
		echo md5('root'.md5('nintendo'));
	}
	
	public function testStringEqualszero()
	{
		$columnToSort = 'nb_hits';
		// it might seem strange. This was the reason of a bug I searched 1 hour for!!
		$this->assertTrue( $columnToSort == 0);
		
	}
	
	public function testMergeArray()
	{
		$a = array('label' => 'test');
		$b = array('test' => 1, 1 => 2100);
		
		$expected = array(
		'label' => 'test',
		'test' => 1, 1 => 2100
		);
		
//		$this->assertEqual( array_merge($a,$b), $expected);
		$this->assertEqual( $a+$b, $expected);
	}
	
	/**
	 * test reading static attribute of a variable class
	 */
	public function test_staticAttr()
	{
		// use this trick to read the static attribute of the class
		// $class::$methodsNotToPublish doesn't work
		$vars = get_class_vars("test_staticAttr");
		
		$this->assertEqual( $vars['a'], 'testa' );
	}
	
	static $countSort=0;
	function test_usortcalledHowManyTimes()
	{
		$a=array();
		//generate fake 1000 elements access
		for($i=0;$i<1000;$i++)
		{
			$a[]=mt_rand();
		}
		$timer = new Piwik_Timer;
		function countSort($a,$b)
		{
			Test_PHP_Related::$countSort++;
			return $a < $b ? -1 : 1;
		}
		//sort using usort
		usort($a, "countSort");
		
		// in the function used count nb of times called
//		print("called ".self::$countSort." times to sort the 1000 elements array");
		
//		echo $timer;
	}
	
	/**
	 * __get is not called when reading a static attribute from a class... snif 
	 */
	public function test_magicMethodStaticAttr()
	{
		$val = test_magicMethodStaticAttr::$test;
		
		$this->assertEqual( $val, "test" );
	}
	
	function test_array2XML()
	{
		
		function array_to_simplexml($array, $name="config" ,&$xml=null )
		{
		    if(is_null($xml))
		    {
		        $xml = new SimpleXMLElement("<{$name}/>");
		    }
		   
		    foreach($array as $key => $value)
		    {
		        if(is_array($value))
		        {
		            $xml->addChild($key);
		            array_to_simplexml($value, $name, $xml->$key);
		        }
		        else
		        {
		            $xml->addChild($key, $value);
		        }
		    }
		    return $xml;
		}
		$test=array("TEST"=>"nurso",
       	 		"none"=>null,
       		 "a"=>"b",
        	array(
          	  "c"=>"d",
          		  array("d"=>"e"))
          );
          
		$xml = array_to_simplexml($test);
		
//		print("<pre>START");print($xml);print("START2");
//		print_r($xml->asXML());
//		print("</pre>");
	}
	/**
	 * misc tests for performance
	 * 
	 * - we try to serialize data when data is a huge multidimensional array
	 * - we then compare the results when data is the huge array 
	 *   but split in hundreds of smaller arrays.
	 * 
	 * clearly the best solution is to split the array in multiple small arrays
	 */
	public function _test_serializeHugeTable()
	{
		$timer = new Piwik_Timer;
		$a=array();
		//generate table
		for($i=0;$i<100;$i++)
		{
			$category=array();
			for($j=0;$j<10;$j++)
			{
				for($k=0;$k<20;$k++)
				{
					$infoPage=array(10,50,1500,15000,1477,15669,15085,45454,87877,222);
					$a[$i][$j][] = $infoPage;
				}
				
			}
			
			
			for($k=0;$k<15;$k++)
			{
				$infoPage=array(154548781,10,50,10,1477,15669,15085);
				$a[$i][] = $infoPage;
			}
		}
		
		//echo "<br>after generation array = ". $timer;
		//echo "<br>count array = ". count($a,1);
		
		$serialized = serialize($a);
		$size = round(strlen($serialized)/1024/1024,3);
		//echo "<br>size serialized string = ". $size."mb";
		//echo "<br>after serialization array = ". $timer;
		
		$serialized = gzcompress($serialized);
		$size = round(strlen($serialized)/1024/1024,3);
		//echo "<br>size compressed string = ". $size."mb";
		//echo "<br>after compression array = ". $timer;
		
		$a = gzuncompress($serialized);
		//echo "<br>after uncompression array = ". $timer;
		$a = unserialize($a);
		//echo "<br>after unserialization array = ". $timer;
		
	}
	public function _test_serializeManySmallTable()
	{
		$timer = new Piwik_Timer;
		$a=array();
		//echo "<br>";
		//generate table
		for($i=0;$i<100;$i++)
		{
			$category=array();
			for($j=0;$j<10;$j++)
			{
				for($k=0;$k<20;$k++)
				{
					$infoPage=array(10,50,1500,15000,1477,15669,15085,45454,87877,222);
					$a[$i][$j][] = $infoPage;
				}
				
			}
			for($k=0;$k<15;$k++)
			{
				$infoPage=array(-1,10,50,10,1477,15669,15085);
				$a[$i][] = $infoPage;
			}
		}
		//echo "<br>after generation array = ". $timer;
		//echo "<br>count array = ". count($a,1);
		
		$allSerialized=array();
		for($i=0;$i<100;$i++)
		{
			for($j=0;$j<10;$j++)
			{
				$allSerialized[] = serialize($a[$i][$j]);
			}
			
			$allSerialized[] = serialize( array_slice($a[$i], 10,15));
		}
		
		//echo "<br>after serialize the subs-arrays = ". $timer;
		//echo "<br>count array = ". count($allSerialized,1);
		
		$size=0;
		foreach($allSerialized as $str)
		{
			$size+=strlen($str);
		}
		$size = round($size/1024/1024,3);
		//echo "<br>size serialized string = ". $size."mb";
		
		$acompressed=array();
		$size = 0;
		foreach($allSerialized as $str)
		{
			$compressed=gzcompress($str);
			$size+=strlen($compressed);
			$acompressed[] = $compressed;
		}
		$size = round($size/1024/1024,3);
		//echo "<br>size compressed string = ". $size."mb";
		//echo "<br>after compression all sub arrays = ". $timer;
	}
	
	function test_functionReturnNothing()
	{
		function givemenothing()
		{
			$a = 4;
		}
		
		$return = givemenothing();
		
		$this->assertFalse(isset($return));
		$this->assertTrue(empty($return));
		$this->assertTrue(!is_int($return));
		$this->assertTrue(!is_string($return));
		$this->assertTrue(!is_bool($return));
	}
	
}


class test_staticAttr
{
	static public $a = 'testa';
	public $b = 'testb';
}

class test_magicMethodStaticAttr
{
	static $test = "test";
	
	function __get($name)
	{
		print("reading static attr ; __get called");
		return 1;
	}
}



