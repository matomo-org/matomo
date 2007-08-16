<?php

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
	private $currentget=array();
	private $allget=array();
	public $profiling;
	public $reinitProfilingAtEveryRequest = true;
	
	public $host = 'http://localhost';
	
	public function __construct()
	{
		$_COOKIE = $_GET = $_REQUEST = $_POST = array();
		
		// init GET and REQUEST to the empty array
		$this->setFakeRequest();
		
		require_once "Piwik.php";
		Piwik::createConfigObject('../config/config.ini.php');
		// setup database	
		Piwik::createDatabaseObject();
		
		$this->profiling = true;
		Piwik_LogStats_Db::enableProfiling();
		
	}
	public function addParam( $name, $aValue)
	{
		if(is_array($aValue))
		{	
			$this->allget[$name] = array_merge(	$aValue,
												(array)@$this->allget[$name]);
		}
		else
		{
			$this->allget[$name][] = $aValue;
		}
	}
	
	
	public function emptyAllLogTables()
	{
		$db = Zend_Registry::get('db');
		$db->query('TRUNCATE TABLE '.Piwik::prefixTable('log_action'));
		$db->query('TRUNCATE TABLE '.Piwik::prefixTable('log_visit'));
		$db->query('TRUNCATE TABLE '.Piwik::prefixTable('log_link_visit_action'));
	}
	
	
	public function disableProfiler()
	{
		$this->profiling = false;
		Piwik_LogStats_Db::disableProfiling();
	}
	
	
	public function end()
	{
		if($this->profiling)
		{
			function maxSumMsFirst($a,$b)
			{
				return $a['sum_time_ms'] < $b['sum_time_ms'];
			}
			
			$db = Zend_Registry::get('db');
			$all = $db->fetchAll('SELECT *, sum_time_ms / count as avg_time_ms FROM '.Piwik::prefixTable('log_profiling').'' );
			usort($all, 'maxSumMsFirst');
			
			
			$str='<br><br>Query Profiling<br>----------------------<br>';
			foreach($all as $infoQuery)
			{
				$query = $infoQuery['query'];
				$count = $infoQuery['count'];
				$sum_time_ms = $infoQuery['sum_time_ms'];
				$avg_time_ms = round($infoQuery['avg_time_ms'],1);
				$query = str_replace("\t", "", $query);
				
				$str .= "$query <br>
			$count times, <b>$sum_time_ms ms total</b><br>
			$avg_time_ms ms average<br>
			<br>";
			}
			
			
			print($str);
		}
	}
	
	public function init()
	{
		if($this->profiling)
		{
			if($this->reinitProfilingAtEveryRequest)
			{
				$db = Zend_Registry::get('db');
				$all = $db->query('TRUNCATE TABLE '.Piwik::prefixTable('log_profiling').'' );
			}
		}
		
		// seed with microseconds
		function make_seed()
		{
		  list($usec, $sec) = explode(' ', microtime());
		  return (float) $sec + ((float) $usec * 100000);
		}
		mt_srand(make_seed());
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
		
		$downloadOrOutlink = array(
						Piwik_LogStats_Config::getInstance()->LogStats['download_url_var_name'],
						Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'],
		);
		$this->addParam('piwik_downloadOrOutlink', $downloadOrOutlink);
		$this->addParam('piwik_downloadOrOutlink', array_fill(0,8,''));
		
		$campaigns = array(
						Piwik_LogStats_Config::getInstance()->LogStats['campaign_var_name'],
						Piwik_LogStats_Config::getInstance()->LogStats['newsletter_var_name'],
						Piwik_LogStats_Config::getInstance()->LogStats['partner_var_name'],
		);
		$this->addParam('piwik_vars_campaign', $campaigns);
		$this->addParam('piwik_vars_campaign', array_fill(0,5,''));
		
		$referers = array();
		require_once "misc/generateVisitsData/Referers.php";
		
		$this->addParam('urlref',$referers);
		$this->addParam('urlref',array_fill(0,2000,''));
		
		$userAgent = $acceptLanguages = array();
		require_once "misc/generateVisitsData/UserAgent.php";
		require_once "misc/generateVisitsData/AcceptLanguage.php";
		$this->userAgents=$userAgent;
		$this->acceptLanguage=$acceptLanguages;
	}
	
	public function generate( $nbVisits, $nbActionsMaxPerVisit )
	{
		$nbActionsTotal = 0;
		for($i = 0; $i < $nbVisits; $i++)
		{
//			print("$i ");
			$nbActions = mt_rand(1, $nbActionsMaxPerVisit);
			
			$this->generateNewVisit();
			for($j = 1; $j <= $nbActions; $j++)
			{
				$this->generateActionVisit();
				$this->saveVisit();
			}
			
			$nbActionsTotal += $nbActions;
		}
//		print("<br> Generated $nbVisits visits.");
//		print("<br> Generated $nbActionsTotal actions.");
		
		return $nbActionsTotal;
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

		$_SERVER['HTTP_CLIENT_IP'] = mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255);
		$_SERVER['HTTP_USER_AGENT'] = $this->userAgents[mt_rand(0,count($this->userAgents)-1)];
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $this->acceptLanguage[mt_rand(0,count($this->acceptLanguage)-1)];
	}
	
	
	private function generateActionVisit()
	{		
		// we don't keep the previous action values // reinit them to empty string
		$this->setCurrentRequest( Piwik_LogStats_Config::getInstance()->LogStats['download_outlink_name_var'],'');
		$this->setCurrentRequest( Piwik_LogStats_Config::getInstance()->LogStats['download_url_var_name'],'');
		$this->setCurrentRequest( Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'],'');
		$this->setCurrentRequest( 'action_name', '');

		// generate new url referer ; case the visitor stays more than 30min
		// we set it as a new visit and the referer will then be used
		$this->setCurrentRequest( 'urlref' , $this->getRandom('urlref'));
		
		$url = $this->getRandomUrlFromHost($this->host);
		
		// we generate a campaign (partner or newsletter or campaign)
		$urlVars = $this->getRandom('piwik_vars_campaign');
		// campaign name
		$urlValue = $this->getRandomString(5,3,'lower');
		
		// if we actually generated a campaign
		if(!empty($urlVars))
		{
			// add the parameter to the url
			$url .= '?'. $urlVars . '=' . $urlValue;
			
			// for a campaign of the CPC kind, we sometimes generate a keyword 
			if($urlVars == Piwik_LogStats_Config::getInstance()->LogStats['campaign_var_name']
				&& mt_rand(0,1)==0)
			{
				$url .= '&'. Piwik_LogStats_Config::getInstance()->LogStats['campaign_keyword_var_name'] 
							. '=' . $this->getRandomString(6,3,'ALL');;
			}
		}
		else
		{
			// we generate a download Or Outlink parameter in the GET request so that 
			// the current action is counted as a download action OR a outlink click action
			$GETParamToAdd = $this->getRandom('piwik_downloadOrOutlink');
			if(!empty($GETParamToAdd))
			{
				// download / outlink url
				$urlValue = $this->getRandomUrlFromHost($this->host);
				
				// add the parameter to the url
				$this->setCurrentRequest( $GETParamToAdd , $urlValue);
				
				if(mt_rand(0,1)==0)
				{
					$this->setCurrentRequest( Piwik_LogStats_Config::getInstance()->LogStats['download_outlink_name_var'] 
											, $this->getRandomString(6,3,'ALL'));
				}
			}
			else
			{
				if(rand(0,2)==1)
				{
					$this->setCurrentRequest( 'action_name' , $this->getRandomString(3,3));
				}
			}
		}
		
//		print($url . "<br>"); 
		$this->setCurrentRequest( 'url' ,$url);
		
		$this->setCurrentRequest( 'title',$this->getRandomString(15,5));
	}
	
	private function getRandomUrlFromHost( $host )
	{
		$url = $host;
		
		$deep = mt_rand(0,2);
		for($i=0;$i<$deep;$i++)
		{
			$name = $this->getRandomString(1,2,'alnum');
			
			$url .= '/'.$name;
		}
		return $url;
	}
	
	// from php.net and edited
	private function getRandomString($maxLength = 15, $minLength = 5, $type = 'ALL')
	{
		$len = mt_rand($minLength, $maxLength);
		
	    // Register the lower case alphabet array
	    $alpha = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm');
	
	    // Register the upper case alphabet array                    
	    $ALPHA = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
	                     'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	       
	    // Register the numeric array              
	    $num = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
	    
	    // Register the strange array              
	    $strange = array('/', '?', '!','"','£','$','%','^','&','*','(',')',' ');
	   
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
	        case 'alnum' :
	            $keyVals = array_merge($alpha, $num);
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
	        $r = mt_rand(0,count($keyVals)-1);
	        $key[$i] = $keyVals[$r];
	    }
	   
	    // Glue the key array into a string and return it
	    return join("", $key);
	}

	private function setFakeRequest()
	{
		$_REQUEST = $_GET = $this->currentget;
	}
	
	private function setCurrentRequest($name,$value)
	{
		$this->currentget[$name] = $value;
	}
	
	private function getRandom( $name )
	{		
		if(!isset($this->allget[$name]))
		{
			throw new exception("You are asking for $name which doesnt exist");
		}
		else
		{
			$index = mt_rand(0,count($this->allget[$name])-1);
			$value =$this->allget[$name][$index];
			return $value;
		}
	}
	
	private function getRandom01()
	{
		return mt_rand(0,1);
	}
	
	
	private function saveVisit()
	{
		$this->setFakeRequest();
		$process = new Piwik_LogStats_Generator_Main;
		$process->main('Piwik_LogStats_Generator_Visit');
	}
	
}

class Piwik_LogStats_Generator_Main extends Piwik_LogStats
{
	protected function sendHeader($header)
	{
	//	header($header);
	}
}

class Piwik_LogStats_Generator_Visit extends Piwik_LogStats_Visit
{
	static protected $timestampToUse;
	
	function __construct( $db )
	{
		parent::__construct($db);
	}
	
	static public function setTimestampToUse($time)
	{
		self::$timestampToUse = $time;
	}
	protected function getCurrentDate( $format = "Y-m-d")
	{
		if($format ==  "Y-m-d") return date($format);
		else return date($format, $this->getCurrentTimestamp() );
	}
	
	protected function getCurrentTimestamp()
	{
		self::$timestampToUse = max(@$this->visitorInfo['visit_last_action_time'],self::$timestampToUse);
		self::$timestampToUse += mt_rand(4,1840);
		return self::$timestampToUse;
	}
		
	protected function getDatetimeFromTimestamp($timestamp)
	{
		return date("Y-m-d H:i:s",$timestamp);
	}
	
}
?>
