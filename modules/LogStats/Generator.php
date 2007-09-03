<?php

/**
 * Class used to generate fake visits. 
 * Useful to test performances, general functional testing, etc.
 * 
 * Requirements of the visits generator script. It is to edit * 
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
 * Generate thousands of visits / actions per visitor 
 *  
 * @package Piwik_LogStats
 */

class Piwik_LogStats_Generator
{
	protected $currentget	=	array();
	protected $allget		=	array();
	protected $maximumUrlDepth = 1;
	protected $timestampToUse;
	
	public $profiling 	= true;
	public $reinitProfilingAtEveryRequest = true;
	
	//TODO also make this variable dynamic so that a visitor can make hit on several hosts and 
	// only the good ones are kept
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
		
		Piwik_LogStats_Db::enableProfiling();
		
		$this->timestampToUse = time();
	}
	
	/**
	 * Sets the depth level of the generated URLs
	 * value = 1 => path OR path/page1
	 * value = 2 => path OR path/pageRand OR path/dir1/pageRand
	 * 
	 * @param int Depth
	 */
	public function setMaximumUrlDepth($value)
	{
		$this->maximumUrlDepth = (int)$value;
	}
	
	/**
	 * Set the timestamp to use as the starting time for the visitors times
	 * To be set with every day value
	 * 
	 * @param int Unix timestamp
	 */
	public function setTimestampToUse($timestamp)
	{
		$this->timestampToUse = $timestamp;
	}
	
	/**
	 * Returns the timestamp to be used as the visitor timestamp
	 * 
	 * @return int
	 */
	public function getTimestampToUse()
	{
		return $this->timestampToUse;
	}
	
	/**
	 * Add a parameter to the GET global array
	 * We set an array value to the GET global array when we want to random select
	 * a value for a given name. 
	 * 
	 * @param string Name of the parameter _GET[$name]
	 * @param array|mixed Value of the parameter
	 */
	protected function addParam( $name, $aValue)
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
	
	/**
	 * TRUNCATE all logs related tables to start a fresh logging database
	 */
	public function emptyAllLogTables()
	{
		$db = Zend_Registry::get('db');
		$db->query('TRUNCATE TABLE '.Piwik::prefixTable('log_action'));
		$db->query('TRUNCATE TABLE '.Piwik::prefixTable('log_visit'));
		$db->query('TRUNCATE TABLE '.Piwik::prefixTable('log_link_visit_action'));
	}
	
	/**
	 * Call this method to disable the SQL query profiler
	 */
	public function disableProfiler()
	{
		$this->profiling = false;
		Piwik_LogStats_Db::disableProfiling();
	}
	
	/**
	 * This marks the end of the Generator script 
	 * and calls the Profiler output if the profiler is enabled
	 */
	public function end()
	{
		if($this->profiling)
		{
			Piwik::printLogStatsSQLProfiling();
		}
	}
	
	/**
	 * Init the Generator script:
	 * - init the SQL profiler
	 * - init the random generator
	 * - setup the different possible values for parameters such as 'resolution',
	 * 		'color', 'hour', 'minute', etc.
	 * - load and setup values for the other parameters
	 */
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
		
		// we get the name of the Download/outlink variables
		$downloadOrOutlink = array(
						Piwik_LogStats_Config::getInstance()->LogStats['download_url_var_name'],
						Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'],
		);
		// we have a 20% chance to add a download or outlink variable to the URL 
		$this->addParam('piwik_downloadOrOutlink', $downloadOrOutlink);
		$this->addParam('piwik_downloadOrOutlink', array_fill(0,8,''));
		
		// we get the variables name for the campaign parameters
		$campaigns = array(
						Piwik_LogStats_Config::getInstance()->LogStats['campaign_var_name'],
						Piwik_LogStats_Config::getInstance()->LogStats['newsletter_var_name'],
						Piwik_LogStats_Config::getInstance()->LogStats['partner_var_name'],
		);
		// we generate a campaign in the URL in 3/18 % of the generated URls
		$this->addParam('piwik_vars_campaign', $campaigns);
		$this->addParam('piwik_vars_campaign', array_fill(0,15,''));
		
		// we load some real referers to be used by the generator
		$referers = array();
		require_once "misc/generateVisitsData/Referers.php";

		$this->addParam('urlref',$referers);

		// and we add 2000 empty referers so that some visitors don't come using a referer (direct entry)
		$this->addParam('urlref',array_fill(0,2000,''));
		
		// load some user agent and accept language
		$userAgent = $acceptLanguages = array();
		require_once "misc/generateVisitsData/UserAgent.php";
		require_once "misc/generateVisitsData/AcceptLanguage.php";
		$this->userAgents=$userAgent;
		$this->acceptLanguage=$acceptLanguages;
	}
	
	/**
	 * Launches the process and generates an exact number of nbVisits
	 * For each visit, we setup the timestamp to the common timestamp
	 * Then we generate between 1 and nbActionsMaxPerVisit actions for this visit
	 * 
	 * @return int The number of total actions generated
	 */
	public function generate( $nbVisits, $nbActionsMaxPerVisit )
	{
		$nbActionsTotal = 0;
		for($i = 0; $i < $nbVisits; $i++)
		{
			$nbActions = mt_rand(1, $nbActionsMaxPerVisit);
			
			Piwik_LogStats_Generator_Visit::setTimestampToUse($this->getTimestampToUse());
						
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
	
	/**
	 * Generate a new visit. Load a random value for 
	 * all the parameters that are read by the piwik logging engine.
	 * 
	 * We even set the _SERVER values
	 */
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
	
	/**
	 * Generates a new action for the current visitor.
	 * We random generate some campaigns, action names, 
	 * download or outlink clicks, etc.
	 * 
	 */
	private function generateActionVisit()
	{		
		// we don't keep the previous action values 
		// reinit them to empty string
		$this->setCurrentRequest( Piwik_LogStats_Config::getInstance()->LogStats['download_outlink_name_var'],'');
		$this->setCurrentRequest( Piwik_LogStats_Config::getInstance()->LogStats['download_url_var_name'],'');
		$this->setCurrentRequest( Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'],'');
		$this->setCurrentRequest( 'action_name', '');

		// generate new url referer ; case the visitor stays more than 30min
		// (when the visit is known this value will simply be ignored)
		$this->setCurrentRequest( 'urlref' , $this->getRandom('urlref'));
		
		// generates the current URL 
		$url = $this->getRandomUrlFromHost($this->host);
		
		// we generate a campaign (partner or newsletter or campaign)
		$urlVars = $this->getRandom('piwik_vars_campaign');
		
		// if we actually generated a campaign
		if(!empty($urlVars))
		{
			// campaign name
			$urlValue = $this->getRandomString(5,3,'lower');
			
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
				
				// in 50% we give a special name to the download/outlink 
				if(mt_rand(0,1)==0)
				{
					$this->setCurrentRequest( Piwik_LogStats_Config::getInstance()->LogStats['download_outlink_name_var'] 
											, $this->getRandomString(6,3,'ALL'));
				}
			}
			
			// if we didn't set any campaign NOR any download click
			// then we sometimes set a special action name to the current action
			elseif(rand(0,2)==1)
			{
				$this->setCurrentRequest( 'action_name' , $this->getRandomString(1,1));
			}
		}
		
		$this->setCurrentRequest( 'url' ,$url);
		
		// setup the title of the page
		$this->setCurrentRequest( 'title',$this->getRandomString(15,5));
	}
	
	/**
	 * Returns a random URL using the $host as the URL host.
	 * Depth level depends on @see setMaximumUrlDepth()
	 */
	private function getRandomUrlFromHost( $host )
	{
		$url = $host;
		
		$deep = mt_rand(0,$this->maximumUrlDepth);
		for($i=0;$i<$deep;$i++)
		{
			$name = $this->getRandomString(1,1,'alnum');
			
			$url .= '/'.$name;
		}
		return $url;
	}
	
	/**
	 * Generates a random string from minLength to maxLenght 
	 * using a specified set of characters
	 * 
	 * From php.net and then badly hacked by myself
	 * 
	 * @param int Maximum length
	 * @param int Minimum length
	 * @param string Characters set to use, ALL or lower or upper or numeric or ALPHA or ALNUM
	 * 
	 * @return string The generated random string
	 */
	private function getRandomString($maxLength = 15, $minLength = 5, $type = 'ALL')
	{
		$len = mt_rand($minLength, $maxLength);
		
	    // Register the lower case alphabet array
	    $alpha = array('a', 'd', 'e', 'f', 'g');
	
	    // Register the upper case alphabet array                    
	    $ALPHA = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
	                     'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	       
	    // Register the numeric array              
	    $num = array('1', '2', '3',  '8', '9', '0');
	    
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

	/**
	 * Set the _GET and _REQUEST superglobal to the current generated array of values
	 */
	private function setFakeRequest()
	{
		$_REQUEST = $_GET = $this->currentget;
	}
	
	/**
	 * Set a value in the current request
	 * 
	 * @param string Name of the parameter to set
	 * @param string Value of the parameter
	 */
	private function setCurrentRequest($name,$value)
	{
		$this->currentget[$name] = $value;
	}
	
	/**
	 * Returns a value for the given parameter $name
	 * 
	 * @throws Exception if the parameter asked for has never been set
	 * 
	 * @return mixed Random value for the parameter named $name
	 */
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

	/**
	 * Returns either 0 or 1
	 * @return int
	 */	
	private function getRandom01()
	{
		return mt_rand(0,1);
	}
	
	/**
	 * Saves the visit 
	 * - set the fake request 
	 * - load the LogStats class and call the method to launch the recording
	 */
	private function saveVisit()
	{
		$this->setFakeRequest();
		$process = new Piwik_LogStats_Generator_Main;
		$process->main('Piwik_LogStats_Generator_Visit');
	}
	
}

/**
 * Fake Piwik_LogStats that simply overwrite the sendHeader method 
 * so that no headers are sent
 */
class Piwik_LogStats_Generator_Main extends Piwik_LogStats
{
	protected function sendHeader($header)
	{
	//	header($header);
	}
}

/**
 * Fake Piwik_LogStats_Visit class that overwrite all the Time related method to be able
 * to setup a given timestamp for the generated visitor and actions.
 */
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
		return date($format, $this->getCurrentTimestamp() );
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

