<?php

error_reporting(E_ALL|E_NOTICE);
define('PIWIK_INCLUDE_PATH', '..');
define('PIWIK_DATAFILES_INCLUDE_PATH', PIWIK_INCLUDE_PATH . "/modules/DataFiles");

ignore_user_abort(true);
set_time_limit(0);

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core/models'
					. PATH_SEPARATOR . get_include_path() );

require_once "Event/Dispatcher.php";
require_once "Common.php";
require_once "LogStats.php";
require_once "PluginManager.php";
require_once "LogStats/Plugins.php";
$GLOBALS['DEBUGPIWIK'] = true;

$_COOKIE = array();

$_GET =  $_REQUEST = array (
  'url' => 'http:%2hF%2wik_kwd=teysyt',
  'urlref' => 'http://locageq.g.lhost/test.php',
  'action_name' => '',
  'idsite' => '1',
  'res' => '1280x1024',
  'col' => '24',
  'h' => '12',
  'm' => '25',
  's' => '45',
  'fla' => '1',
  'dir' => '0',
  'qt' => '1',
  'realp' => '1',
  'pdf' => '0',
  'wma' => '1',
  'java' => '1',
  'cookie' => '1',
  'title' => '',
);

/**
 * Requirements of the visits generator script
 * 
 * Things possible to change
 * 
 * - url => campaigns
 * 		- newsletter
 * 		- partner
 * 		- campaign CPC
 * - referer
 * 		- search engine
 * 		- misc site
 * 		- same website
 * - url => multiple directories, page names
 * - multiple idsite
 * - multiple settings configurations 
 * - action_name 
 * - HTML title
 * 
 * Objective:
 * Generate thousands of visits / actions per visitor with random data to test the performance
 *  
 */

class Piwik_LogStats_Generator
{
	private $get;
	
	public $host = 'http://localhost';
	
	public function addParam( $name, $aValue)
	{
		if(is_array($aValue))
		{	
			$this->allget[$name] = array_merge($aValue,(array)$this->get[$name]);
		}
		else
		{
			$this->allget[$name][] = $aValue;
		}
	}
	
	public function init()
	{
		$common = array(
			'res' => array('1289x800','1024x768','800x600','564x644','200x100','50x2000',),
			'col' => array(24,32,16),
			'idsite'=> 1,
			'h' => range(0,23),
			'm' => range(0,59),
			's' => range(0,59),
			
		);
		
		foreach($common as $label => $values)
		{
			$this->addParam($label,$values);
		}
		
		$referers = array();
		require_once "misc/generateVisitsData/Referers.php";
		
		$this->addParam('urlref',$referers);
		
		$userAgent = $acceptLanguages = array();
		require_once "misc/generateVisitsData/UserAgent.php";
		require_once "misc/generateVisitsData/AcceptLanguage.php";
		$this->userAgents=$userAgent;
		$this->acceptLanguage=$acceptLanguages;
	}
	
	public function generate( $nbVisits, $nbActionsMaxPerVisit )
	{
		for($i = 0; $i < $nbVisits; $i++)
		{
			$nbActions = rand(1, $nbActionsMaxPerVisit);
			
			$this->generateNewVisit();
			for($j = 1; $j <= $nbActions; $j++)
			{
				$this->generateActionVisit();
				$this->saveVisit();
			}
		}
	}
	
	private function generateActionVisit()
	{		
		$this->setCurrentRequest( 'url' , $this->getRandomUrlFromHost($this->host));
		$this->setCurrentRequest( 'action_name' , $this->getRandomString());
		$this->setCurrentRequest( 'title',$this->getRandomString(40,15));
	}
	
	private function getRandomUrlFromHost( $host )
	{
		$url = $host;
		
		$deep = rand(0,5);
		for($i=0;$i<$deep;$i++)
		{
			$name = $this->getRandomString(12,3,'ALNUM');
			
			$url .= '/'.$name;
		}
		return $url;
	}
	
	// from php.net and edited
	private function getRandomString($maxLength = 15, $minLength = 5, $type = 'ALL')
	{
		$len = rand($minLength, $maxLength);
		
	    // Register the lower case alphabet array
	    $alpha = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
	                   'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
	
	    // Register the upper case alphabet array                    
	    $ALPHA = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
	                     'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	       
	    // Register the numeric array              
	    $num = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
	    
	    // Register the strange array              
	    $strange = array('/', '?', '!','"','£','$','%','^','&','*','(',')');
	   
	    // Initialize the keyVals array for use in the for loop
	    $keyVals = array();
	   
	    // Initialize the key array to register each char
	    $key = array();   
	   
	    // Loop through the choices and register
	    // The choice to keyVals array
	    switch ($type)
	    {
	        case 'lower' :
	            $keyVals = $alpha;
	            break;
	        case 'upper' :
	            $keyVals = $ALPHA;
	            break;
	        case 'numeric' :
	            $keyVals = $num;
	            break;
	        case 'ALPHA' :
	            $keyVals = array_merge($alpha, $ALPHA);
	            break;
	        case 'ALNUM' :
	            $keyVals = array_merge($alpha, $ALPHA, $num);
	            break;
	        case 'ALL' :
	            $keyVals = array_merge($alpha, $ALPHA, $num, $strange);
	            break;
	    }
	   
	    // Loop as many times as specified
	    // Register each value to the key array
	    for($i = 0; $i <= $len-1; $i++)
	    {
	        $r = rand(0,count($keyVals)-1);
	        $key[$i] = $keyVals[$r];
	    }
	   
	    // Glue the key array into a string and return it
	    return join("", $key);
	}

	private function setFakeRequest()
	{
		$_REQUEST = $_GET = $this->currentget;
	}
	
	private function generateNewVisit()
	{
		$this->setCurrentRequest( 'urlref' , $this->getRandom('urlref'));
		$this->setCurrentRequest( 'idsite', $this->getRandom('idsite'));
		$this->setCurrentRequest( 'res' ,$this->getRandom('res'));
		$this->setCurrentRequest( 'col' ,$this->getRandom('col'));
		$this->setCurrentRequest( 'h' ,$this->getRandom('h'));
		$this->setCurrentRequest( 'm' ,$this->getRandom('m'));
		$this->setCurrentRequest( 's' ,$this->getRandom('s'));
		$this->setCurrentRequest( 'fla' ,$this->getRandom01());
		$this->setCurrentRequest( 'dir' ,$this->getRandom01());
		$this->setCurrentRequest( 'qt' ,$this->getRandom01());
		$this->setCurrentRequest( 'realp' ,$this->getRandom01());
		$this->setCurrentRequest( 'pdf' ,$this->getRandom01());
		$this->setCurrentRequest( 'wma' ,$this->getRandom01());
		$this->setCurrentRequest( 'java' ,$this->getRandom01());
		$this->setCurrentRequest( 'cookie',$this->getRandom01());
		
		$_SERVER['HTTP_CLIENT_IP'] = rand(0,255).".".rand(0,255).".".rand(0,255).".".rand(0,255);
		$_SERVER['HTTP_USER_AGENT'] = $this->userAgents[rand(0,count($this->userAgents)-1)];
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $this->acceptLanguage[rand(0,count($this->acceptLanguage)-1)];
	}
	
	private function setCurrentRequest($name,$value)
	{
		$this->currentget[$name] = $value;
	}
	
	private function getRandom( $name )
	{
		/*switch($name)
		{
			case 'url':
			break;
			case 'urlref':
			break;
			case 'action_name':
			break;
			case 'title':
			break;
			case 'idsite':
			break;
			case 'res':
			break;
			case 'col':
			break;
			default:
			break;
		}*/
		
		if(!isset($this->allget[$name]))
		{
			throw new exception("You are asking for $name which doesnt exist");
		}
		else
		{
			$index = rand(0,count($this->allget[$name])-1);
			$value =$this->allget[$name][$index];
			return $value;
		}
	}
	
	private function getRandom01()
	{
		return rand(0,1);
	}
	
	
	private function saveVisit()
	{
		$this->setFakeRequest();
		$process = new Piwik_LogStats;
		$process->main();
	}
	
}

$generator = new Piwik_LogStats_Generator;
$generator->init();
$generator->generate(1000,10);
?>
