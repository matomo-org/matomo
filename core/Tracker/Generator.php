<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Generator.php 492 2008-05-23 01:08:12Z matt $
 * 
 * @package Piwik_Tracker
 */

/**
 * Class used to generate fake visits. 
 * Useful to test performances, general functional testing, etc.
 * 
 * Objective:
 * Generate thousands of visits / actions per visitor using 
 *  a single request to misc/generateVisits.php
 * 
 * Requirements of the visits generator script. Fields that can be edited:
 * - url => campaigns
 * 		- newsletter
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
 *  
 * @package Piwik_Tracker
 * @subpackage Piwik_Tracker_Generator
 * 
 * 											"Le Generator, il est trop Fort!"
 * 											- Random fan
 */

class Piwik_Tracker_Generator
{
	/**
	 * GET parameters array of values to be used for the current visit
	 *
	 * @var array ('res' => '1024x768', 'urlref' => 'http://google.com/search?q=piwik', ...)
	 */
	protected $currentget	=	array();
	
	/**
	 * Array of all the potential values for the visit parameters
	 * Values of 'resolution', 'urlref', etc. will be randomly read from this array
	 *
	 * @var array ( 
	 * 			'res' => array('1024x768','800x600'), 
	 * 			'urlref' => array('google.com','intel.com','amazon.com'),
	 * 			....)
	 */
	protected $allget		=	array();
	
	/**
	 * See @see setMaximumUrlDepth
	 *
	 * @var int
	 */
	protected $maximumUrlDepth = 1;
	
	/**
	 * Unix timestamp to use for the generated visitor 
	 *
	 * @var int Unix timestamp
	 */
	protected $timestampToUse;
	
	/**
	 * See @see disableProfiler()
	 * The profiler is enabled by default
	 *
	 * @var bool
	 */
	protected $profiling 	= true;
	
	/**
	 * If set to true, this will TRUNCATE the profiling tables at every new generated visit 
	 * @see initProfiler()
	 * 
	 * @var bool
	 */
	public $reinitProfilingAtEveryRequest = true;
	
	/**
	 * Hostname used to prefix all the generated URLs
	 * we could make this variable dynamic so that a visitor can make hit on several hosts and
	 * only the good ones should be kept (feature not yet implemented in piwik)
	 * 
	 * @var string
	 */
	public $host = 'http://localhost';
	
	/**
	 * IdSite to generate visits for (@see setIdSite())
	 *
	 * @var int
	 */
	public $idSite = 1;
	
	/**
	 * Overwrite the global GET/POST/COOKIE variables and set the fake ones @see setFakeRequest()
	 * Reads the configuration file but disables write to this file
	 * Creates the database object & enable profiling by default (@see disableProfiler())
	 *
	 */
	public function __construct()
	{
		$_COOKIE = $_GET = $_REQUEST = $_POST = array();
		
		// init GET and REQUEST to the empty array
		$this->setFakeRequest();
		
		require_once "core/Piwik.php";
		Piwik::createConfigObject('../config/config.ini.php');
		Zend_Registry::get('config')->doWriteFileWhenUpdated = false;
		
		// setup database	
		Piwik::createDatabaseObject();
		
		Piwik_Tracker_Db::enableProfiling();
		
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
	 * You have to call this method for every day you want to generate data
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
	 * @return int Unix timestamp
	 */
	public function getTimestampToUse()
	{
		return $this->timestampToUse;
	}

	/**
	 * Set the idsite to generate the visits for
	 * To be called before init()
	 * 
	 * @param int idSite
	 */
	public function setIdSite($idSite)
	{
		$this->idSite = $idSite;
	}
	
	/**
	 * Add a value to the GET global array.
	 * The generator script will then randomly read a value from this array.
	 * 
	 * For example, $name = 'res' $aValue = '1024x768' 
	 * 
	 * @param string Name of the parameter _GET[$name]
	 * @param array|mixed Value of the parameter
	 * @return void
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
	 * TRUNCATE all logs related tables to start a fresh logging database.
	 * Be careful, any data deleted this way is deleted forever
	 * 
	 * @return void
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
		Piwik_Tracker_Db::disableProfiling();
	}
	
	/**
	 * This is called at the end of the Generator script.
	 * Calls the Profiler output if the profiler is enabled.
	 * 
	 * @return void
	 */
	public function end()
	{
		Piwik_Tracker::disconnectDatabase();
		if($this->profiling)
		{
			Piwik::printSqlProfilingReportTracker();
		}
	}
	
	/**
	 * Init the Generator script:
	 * - init the SQL profiler
	 * - init the random generator
	 * - setup the different possible values for parameters such as 'resolution',
	 * 		'color', 'hour', 'minute', etc.
	 * - load from DataFiles and setup values for the other parameters such as UserAgent, Referers, AcceptedLanguages, etc.
	 *  @see /misc/generateVisitsData/
	 * 
	 * @return void
	 */
	public function init()
	{
		Piwik::createLogObject();
		
		$this->initProfiler();
		
		/*
		 * Init the random number generator 
		 */ 
		function make_seed()
		{
		  list($usec, $sec) = explode(' ', microtime());
		  return (float) $sec + ((float) $usec * 100000);
		}
		mt_srand(make_seed());
		
		/*
		 * Sets values for: resolutions, colors, idSite, times
		 */
		$common = array(
			'res' => array('1289x800','1024x768','800x600','564x644','200x100','50x2000',),
			'col' => array(24,32,16),
			'idsite'=> $this->idSite,
			'h' => range(0,23),
			'm' => range(0,59),
			's' => range(0,59),
		);
		
		foreach($common as $label => $values)
		{
			$this->addParam($label,$values);
		}
		
		/*
		 * Sets values for: outlinks, downloads, campaigns
		 */
		// we get the name of the Download/outlink variables
		$downloadOrOutlink = array(
						Piwik_Tracker_Config::getInstance()->Tracker['download_url_var_name'],
						Piwik_Tracker_Config::getInstance()->Tracker['outlink_url_var_name'],
		);
		// we have a 20% chance to add a download or outlink variable to the URL 
		$this->addParam('piwik_downloadOrOutlink', $downloadOrOutlink);
		$this->addParam('piwik_downloadOrOutlink', array_fill(0,8,''));
		
		// we get the variables name for the campaign parameters
		$campaigns = array(
						Piwik_Tracker_Config::getInstance()->Tracker['campaign_var_name'],
						Piwik_Tracker_Config::getInstance()->Tracker['newsletter_var_name'],
		);
		// we generate a campaign in the URL in 3/18 % of the generated URls
		$this->addParam('piwik_vars_campaign', $campaigns);
		$this->addParam('piwik_vars_campaign', array_fill(0,15,''));
		
		
		/*
		 * Sets values for: Referers, user agents, accepted languages
		 */
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
	 * If the SQL profiler is enabled and if the reinit at every request is set to true,
	 * then we TRUNCATE the profiling information so that we only profile one visitor at a time
	 * 
	 * @return void
	 */
	protected function initProfiler()
	{
		/*
		 * Inits the profiler
		 */
		if($this->profiling)
		{
			if($this->reinitProfilingAtEveryRequest)
			{
				$all = Zend_Registry::get('db')->query('TRUNCATE TABLE '.Piwik::prefixTable('log_profiling').'' );
			}
		}
	}
	/**
	 * Launches the process and generates an exact number of nbVisitors
	 * For each visit, we setup the timestamp to the common timestamp
	 * Then we generate between 1 and nbActionsMaxPerVisit actions for this visit
	 * The generated actions will have a growing timestamp so it looks like a real visit
	 * 
	 * @param int The number of visits to generate
	 * @param int The maximum number of actions to generate per visit
	 * 
	 * @return int The number of total actions generated
	 */
	public function generate( $nbVisitors, $nbActionsMaxPerVisit )
	{
		$nbActionsTotal = 0;
		for($i = 0; $i < $nbVisitors; $i++)
		{
			$nbActions = mt_rand(1, $nbActionsMaxPerVisit);
			
			Piwik_Tracker_Generator_Visit::setTimestampToUse($this->getTimestampToUse());
						
			$this->generateNewVisit();
			for($j = 1; $j <= $nbActions; $j++)
			{
				$this->generateActionVisit();
				$this->saveVisit();
			}
			
			$nbActionsTotal += $nbActions;
		}
		return $nbActionsTotal;
	}
	
	/**
	 * Generates a new visitor. 
	 * Loads random values for all the necessary parameters (resolution, local time, referers, etc.) from the fake GET array.
	 * Also generates a random IP.
	 * 
	 * We change the superglobal values of HTTP_USER_AGENT, HTTP_CLIENT_IP, HTTP_ACCEPT_LANGUAGE to the generated value.
	 * 
	 * @return void
	 */
	protected function generateNewVisit()
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
	 * We random generate some campaigns, action names, download or outlink clicks, etc.
	 * We generate a new Referer, that would be read in the case the visit last page is older than 30 minutes.
	 * 
	 * This function tries to generate actions that use the features of Piwik (campaigns, downloads, outlinks, action_name set in the JS tag, etc.)
	 * 
	 * @return void
	 * 
	 */
	protected function generateActionVisit()
	{		
		// we don't keep the previous action values 
		// reinit them to empty string
		$this->setCurrentRequest( Piwik_Tracker_Config::getInstance()->Tracker['download_outlink_name_var'],'');
		$this->setCurrentRequest( Piwik_Tracker_Config::getInstance()->Tracker['download_url_var_name'],'');
		$this->setCurrentRequest( Piwik_Tracker_Config::getInstance()->Tracker['outlink_url_var_name'],'');
		$this->setCurrentRequest( 'action_name', '');

		// generate new url referer ; case the visitor stays more than 30min
		// (when the visit is known this value will simply be ignored)
		$this->setCurrentRequest( 'urlref' , $this->getRandom('urlref'));
		
		// generates the current URL 
		$url = $this->getRandomUrlFromHost($this->host);
		
		// we generate a campaign (newsletter or campaign)
		$urlVars = $this->getRandom('piwik_vars_campaign');
		
		// if we actually generated a campaign
		if(!empty($urlVars))
		{
			// campaign name
			$urlValue = $this->getRandomString(5,3,'lower');
			
			// add the parameter to the url
			$url .= '?'. $urlVars . '=' . $urlValue;
			
			// for a campaign of the CPC kind, we sometimes generate a keyword 
			if($urlVars == Piwik_Tracker_Config::getInstance()->Tracker['campaign_var_name']
				&& mt_rand(0,1)==0)
			{
				$url .= '&'. Piwik_Tracker_Config::getInstance()->Tracker['campaign_keyword_var_name'] 
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
				
				$possibleDownloadHosts = array('http://piwik.org/',$this->host);
				$nameDownload = $this->getRandomUrlFromHost($possibleDownloadHosts[mt_rand(0,1)]);
				$extensions = array('.zip','.tar.gz');
				$nameDownload .= $extensions[mt_rand(0,1)];
				$urlValue = $nameDownload;
				
				// add the parameter to the url
				$this->setCurrentRequest( $GETParamToAdd , $urlValue);
				
				// in 50% we give a special name to the download/outlink 
				if(mt_rand(0,1)==0)
				{
					$nameDownload = $this->getRandomString(6,3,'ALL');
					
					$this->setCurrentRequest( Piwik_Tracker_Config::getInstance()->Tracker['download_outlink_name_var'] 
											, $nameDownload);
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
	 * 
	 * @param string Hostname of the URL to generate, eg. http://example.com/
	 * 
	 * @return string The generated URL
	 */
	protected function getRandomUrlFromHost( $host )
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
	 * Generates a random string from minLength to maxLength using a specified set of characters
	 * 
	 * Taken from php.net and then badly hacked by some unknown monkey
	 * 
	 * @param int (optional) Maximum length of the string to generate
	 * @param int (optional) Minimum length of the string to generate
	 * @param string (optional) Characters set to use, 'ALL' or 'lower' or 'upper' or 'numeric' or 'ALPHA' or 'ALNUM'
	 * 
	 * @return string The generated random string
	 */
	protected function getRandomString($maxLength = 15, $minLength = 5, $type = 'ALL')
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
	 * Sets the _GET and _REQUEST superglobal to the current generated array of values.
	 * @see setCurrentRequest()
	 * This method is called once the current action parameters array has been generated from 
	 * the global parameters array
	 * 
	 * @return void
	 */
	protected function setFakeRequest()
	{
		$_REQUEST = $_GET = $this->currentget;
	}
	
	/**
	 * Sets a value in the current action request array.
	 * 
	 * @param string Name of the parameter to set
	 * @param string Value of the parameter
	 */
	protected function setCurrentRequest($name,$value)
	{
		$this->currentget[$name] = $value;
	}
	
	/**
	 * Returns a value for the given parameter $name read randomly from the global parameter array.
	 * @see init()
	 * 
	 * @param string Name of the parameter value to randomly load and return
	 * @return mixed Random value for the parameter named $name
	 * @throws Exception if the parameter asked for has never been set
	 * 
	 */
	protected function getRandom( $name )
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
	 * 
	 * @return int 0 or 1
	 */	
	protected function getRandom01()
	{
		return mt_rand(0,1);
	}
	
	/**
	 * Saves the visit 
	 * - replaces GET and REQUEST by the fake generated request
	 * - load the Tracker class and call the method to launch the recording
	 * 
	 * This will save the visit in the database
	 * 
	 * @return void
	 */
	protected function saveVisit()
	{
		$this->setFakeRequest();
		$process = new Piwik_Tracker_Generator_Tracker;
		$process->main();
	}
	
}
require_once "Generator/Tracker.php";
require_once "Generator/Visit.php";
