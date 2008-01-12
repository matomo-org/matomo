<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

Zend_Loader::loadClass('Piwik_Timer');

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
	
	function test_unserializeObject()
	{
//		$o = new Piwik_DataTable;
//		$o = 'O:15:"Piwik_DataTable":6:{s:7:"�*�rows";a:10:{i:0;O:19:"Piwik_DataTable_Row":1:{s:1:"c";a:3:{i:0;a:12:{s:5:"label";s:6:"/index";s:9:"nb_visits";s:2:"17";s:7:"nb_hits";s:2:"17";s:23:"entry_nb_unique_visitor";s:2:"12";s:15:"entry_nb_visits";s:2:"16";s:16:"entry_nb_actions";s:2:"19";s:22:"entry_sum_visit_length";s:4:"8102";s:18:"entry_bounce_count";s:2:"15";s:22:"exit_nb_unique_visitor";s:2:"12";s:14:"exit_nb_visits";s:2:"16";s:17:"exit_bounce_count";s:2:"15";s:14:"sum_time_spent";s:4:"3292";}i:1;a:0:{}i:3;N;}}i:1;O:36:"Piwik_DataTable_Row_DataTableSummary":1:{s:1:"c";a:3:{i:0;a:12:{s:5:"label";i:1;s:9:"nb_visits";i:7;s:7:"nb_hits";i:7;s:23:"entry_nb_unique_visitor";i:6;s:15:"entry_nb_visits";i:6;s:16:"entry_nb_actions";i:6;s:22:"entry_sum_visit_length";i:60;s:18:"entry_bounce_count";i:6;s:22:"exit_nb_unique_visitor";i:6;s:14:"exit_nb_visits";i:6;s:17:"exit_bounce_count";i:6;s:14:"sum_time_spent";i:2284;}i:1;a:1:{s:18:"databaseSubtableId";i:38;}i:3;i:193;}}i:2;O:36:"Piwik_DataTable_Row_DataTableSummary":1:{s:1:"c";a:3:{i:0;a:11:{s:5:"label";i:9;s:9:"nb_visits";i:6;s:7:"nb_hits";i:6;s:23:"entry_nb_unique_visitor";i:6;s:15:"entry_nb_visits";i:6;s:16:"entry_nb_actions";i:6;s:22:"entry_sum_visit_length";i:60;s:18:"entry_bounce_count";i:6;s:22:"exit_nb_unique_visitor";i:6;s:14:"exit_nb_visits";i:6;s:17:"exit_bounce_count";i:6;}i:1;a:1:{s:18:"databaseSubtableId";i:18;}i:3;i:173;}}i:3;O:36:"Piwik_DataTable_Row_DataTableSummary":1:{s:1:"c";a:3:{i:0;a:11:{s:5:"label";i:3;s:9:"nb_visits";i:6;s:7:"nb_hits";i:6;s:23:"entry_nb_unique_visitor";i:6;s:15:"entry_nb_visits";i:6;s:16:"entry_nb_actions";i:6;s:22:"entry_sum_visit_length";i:60;s:18:"entry_bounce_count";i:6;s:22:"exit_nb_unique_visitor";i:6;s:14:"exit_nb_visits";i:6;s:17:"exit_bounce_count";i:6;}i:1;a:1:{s:18:"databaseSubtableId";i:9;}i:3;i:164;}}i:4;O:36:"Piwik_DataTable_Row_DataTableSummary":1:{s:1:"c";a:3:{i:0;a:12:{s:5:"label";s:1:"f";s:9:"nb_visits";i:6;s:7:"nb_hits";i:6;s:23:"entry_nb_unique_visitor";i:4;s:15:"entry_nb_visits";i:4;s:16:"entry_nb_actions";i:4;s:22:"entry_sum_visit_length";i:40;s:18:"entry_bounce_count";i:4;s:22:"exit_nb_unique_visitor";i:5;s:14:"exit_nb_visits";i:5;s:17:"exit_bounce_count";i:4;s:14:"sum_time_spent";i:2623;}i:1;a:1:{s:18:"databaseSubtableId";i:31;}i:3;i:186;}}i:5;O:19:"Piwik_DataTable_Row":1:{s:1:"c";a:3:{i:0;a:11:{s:5:"label";s:2:"/9";s:9:"nb_visits";s:1:"5";s:7:"nb_hits";s:1:"5";s:23:"entry_nb_unique_visitor";s:1:"5";s:15:"entry_nb_visits";s:1:"5";s:16:"entry_nb_actions";s:1:"5";s:22:"entry_sum_visit_length";s:2:"50";s:18:"entry_bounce_count";s:1:"5";s:22:"exit_nb_unique_visitor";s:1:"5";s:14:"exit_nb_visits";s:1:"5";s:17:"exit_bounce_count";s:1:"5";}i:1;a:0:{}i:3;N;}}i:6;O:36:"Piwik_DataTable_Row_DataTableSummary":1:{s:1:"c";a:3:{i:0;a:12:{s:5:"label";s:1:"e";s:9:"nb_visits";i:5;s:7:"nb_hits";i:5;s:23:"entry_nb_unique_visitor";i:5;s:15:"entry_nb_visits";i:5;s:16:"entry_nb_actions";i:11;s:22:"entry_sum_visit_length";i:15972;s:18:"entry_bounce_count";i:3;s:22:"exit_nb_unique_visitor";i:3;s:14:"exit_nb_visits";i:3;s:17:"exit_bounce_count";i:3;s:14:"sum_time_spent";i:4711;}i:1;a:1:{s:18:"databaseSubtableId";i:22;}i:3;i:177;}}i:7;O:36:"Piwik_DataTable_Row_DataTableSummary":1:{s:1:"c";a:3:{i:0;a:12:{s:5:"label";s:1:"g";s:9:"nb_visits";i:4;s:7:"nb_hits";i:4;s:23:"entry_nb_unique_visitor";i:4;s:15:"entry_nb_visits";i:4;s:16:"entry_nb_actions";i:5;s:22:"entry_sum_visit_length";i:2267;s:18:"entry_bounce_count";i:3;s:22:"exit_nb_unique_visitor";i:3;s:14:"exit_nb_visits";i:3;s:17:"exit_bounce_count";i:3;s:14:"sum_time_spent";i:2237;}i:1;a:1:{s:18:"databaseSubtableId";i:28;}i:3;i:183;}}i:8;O:19:"Piwik_DataTable_Row":1:{s:1:"c";a:3:{i:0;a:11:{s:5:"label";s:2:"/e";s:9:"nb_visits";s:1:"4";s:7:"nb_hits";s:1:"4";s:23:"entry_nb_unique_visitor";s:1:"3";s:15:"entry_nb_visits";s:1:"3";s:16:"entry_nb_actions";s:1:"3";s:22:"entry_sum_visit_length";s:2:"30";s:18:"entry_bounce_count";s:1:"3";s:22:"exit_nb_unique_visitor";s:1:"4";s:14:"exit_nb_visits";s:1:"4";s:17:"exit_bounce_count";s:1:"3";}i:1;a:0:{}i:3;N;}}i:9;O:19:"Piwik_DataTable_Row":1:{s:1:"c";a:3:{i:0;a:11:{s:5:"label";s:2:"/f";s:9:"nb_visits";s:1:"4";s:7:"nb_hits";s:1:"4";s:23:"entry_nb_unique_visitor";s:1:"4";s:15:"entry_nb_visits";s:1:"4";s:16:"entry_nb_actions";s:1:"4";s:22:"entry_sum_visit_length";s:2:"40";s:18:"entry_bounce_count";s:1:"4";s:22:"exit_nb_unique_visitor";s:1:"4";s:14:"exit_nb_visits";s:1:"4";s:17:"exit_bounce_count";s:1:"4";}i:1;a:0:{}i:3;N;}}}s:12:"�*�currentId";i:163;s:13:"�*�depthLevel";i:0;s:19:"�*�indexNotUpToDate";b:1;s:16:"�*�queuedFilters";a:0:{}s:29:"�*�rowsCountBeforeLimitFilter";i:34;}';
//		$o = unserialize($o);
	}
}





