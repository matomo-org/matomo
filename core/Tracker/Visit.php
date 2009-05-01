<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Visit.php 575 2008-07-26 23:08:32Z matt $
 * 
 * @package Piwik_Tracker
 */


interface Piwik_Tracker_Visit_Interface {
	function setRequest($requestArray);
	function handle();
}

/**
 * Class used to handle a Visit.
 * A visit is either NEW or KNOWN.
 * - If a visit is NEW then we process the visitor information (settings, referers, etc.) and save
 * a new line in the log_visit table.
 * - If a visit is KNOWN then we update the visit row in the log_visit table, updating the number of pages
 * views, time spent, etc.
 * 
 * Whether a visit is NEW or KNOWN we also save the action in the DB. 
 * One request to the piwik.php script is associated to one action.
 * 
 * @package Piwik_Tracker
 */

class Piwik_Tracker_Visit implements Piwik_Tracker_Visit_Interface
{
	/**
	 * @var Piwik_Cookie
	 */
	protected $cookie = null;
	protected $visitorInfo = array();
	protected $userSettingsInformation = null;
	protected $idsite;
	protected $visitorKnown;
	protected $request;
	
	// @see detect*() referer methods
	protected $typeRefererAnalyzed;
	protected $nameRefererAnalyzed;
	protected $keywordRefererAnalyzed;
	protected $refererHost;
	protected $refererUrl;
	protected $refererUrlParse;
	
	function __construct()
	{
		$idsite = Piwik_Common::getRequestVar('idsite', 0, 'int', $this->request);
		if($idsite <= 0)
		{
			throw new Exception("The 'idsite' in the request is invalid.");
		}
		$this->idsite = $idsite;
	}
	function setRequest($requestArray)
	{
		$this->request = $requestArray;
	}
	
	/**
	 *	Main algorith to handle the visit. 
	 *
	 *  Once we have the visitor information, we have to define if the visit is a new or a known visit.
	 * 
	 * 1) When the last action was done more than 30min ago, 
	 * 	  or if the visitor is new, then this is a new visit.
	 *	
	 * 2) If the last action is less than 30min ago, then the same visit is going on. 
	 *	Because the visit goes on, we can get the time spent during the last action.
	 *
	 * NB:
	 *  - In the case of a new visit, then the time spent 
	 *	during the last action of the previous visit is unknown. 
	 * 
	 *	- In the case of a new visit but with a known visitor, 
	 *	we can set the 'returning visitor' flag.
	 *
	 * In all the cases we set a cookie to the visitor with the new information.
	 */
	public function handle()
	{
		if($this->isExcluded())
		{
			return;
		}
		
		$action = $this->newAction();
		$action->setIdSite($this->idsite);
		$action->setRequest($this->request);
		$action->init();
		if($this->detectActionIsOutlinkOnAliasHost($action))
		{
			printDebug("The outlink's URL host is one  of the known host for this website. We don't record this click.");
			return;
		}
		$actionId = $action->getIdAction();

		if(isset($GLOBALS['PIWIK_TRACKER_DEBUG']) && $GLOBALS['PIWIK_TRACKER_DEBUG'])
		{
			switch($action->getActionType()) {
				case Piwik_Tracker_Action::TYPE_ACTION:
					$type = "normal page view";
					break;
				case Piwik_Tracker_Action::TYPE_DOWNLOAD:
					$type = "download";
					break;
				case Piwik_Tracker_Action::TYPE_OUTLINK:
					$type = "outlink";
					break;
			}
			printDebug("Detected action <u>$type</u>, 
						Action name: ". $action->getActionName() . ", 
						Action URL = ". $action->getActionUrl() );
		}
				
		// goal matched?
		$goalManager = new Piwik_Tracker_GoalManager( $action );
		$someGoalsConverted = false;
		if($goalManager->detectGoals($this->idsite))
		{
			$someGoalsConverted = true;
		}
		
		// the visitor and session
		$this->recognizeTheVisitor();
		
		$isLastActionInTheSameVisit = $this->isLastActionInTheSameVisit();
		
		// Known visit when:
		// - the visitor has the Piwik cookie with the idcookie ID used by Piwik to match the visitor 
		// OR
		// - the visitor doesn't have the Piwik cookie but could be match using heuristics @see recognizeTheVisitor()
		// AND 
		// - the last page view for this visitor was less than 30 minutes ago @see isLastActionInTheSameVisit()
		if( $this->isVisitorKnown() 
			&& $isLastActionInTheSameVisit)
		{
			$idActionReferer = $this->visitorInfo['visit_exit_idaction'];
			try {
				$this->handleKnownVisit($actionId, $someGoalsConverted);
				$action->record( 	$this->visitorInfo['idvisit'], 
									$idActionReferer, 
									$this->visitorInfo['time_spent_ref_action']
							);
			} catch(Piwik_Tracker_Visit_VisitorNotFoundInDatabase $e) {
				printDebug($e->getMessage());
				$this->visitorKnown = false;
			}
		}
		
		// New visit when:
		// - the visitor has the Piwik cookie but the last action was performed more than 30 min ago @see isLastActionInTheSameVisit()
		// - the visitor doesn't have the Piwik cookie, and couldn't be matched in @see recognizeTheVisitor()
		// - the visitor does have the Piwik cookie but the idcookie and idvisit found in the cookie didn't match to any existing visit in the DB
		if(!$this->isVisitorKnown()
			|| !$isLastActionInTheSameVisit)
		{
			$this->handleNewVisit($actionId, $someGoalsConverted);
			$action->record( $this->visitorInfo['idvisit'], 0, 0 );
		}
		
		// update the cookie with the new visit information
		$this->updateCookie();

		// record the goals if applicable
		if($someGoalsConverted) 
		{
			$goalManager->setCookie($this->cookie);
			$goalManager->recordGoals($this->visitorInfo);
		}
		unset($goalManager);
		unset($action);
	}

	
	/**
	 * In the case of a known visit, we have to do the following actions:
	 * 
	 * 1) Insert the new action
	 * 
	 * 2) Update the visit information
	 */
	protected function handleKnownVisit($actionId, $someGoalsConverted)
	{
		$serverTime 	= $this->getCurrentTimestamp();
		$datetimeServer = Piwik_Tracker::getDatetimeFromTimestamp($serverTime);
		printDebug("Visit known. Current date is ".$datetimeServer);
				
		$sqlUpdateGoalConverted = '';
		if($someGoalsConverted)
		{
			$sqlUpdateGoalConverted = " visit_goal_converted = 1,";
		}
		
		$statement = Piwik_Tracker::getDatabase()->query("/* SHARDING_ID_SITE = ". $this->idsite ." */
							UPDATE ". Piwik_Common::prefixTable('log_visit')." 
							SET visit_last_action_time = ?,
								visit_exit_idaction = ?,
								visit_total_actions = visit_total_actions + 1,
								$sqlUpdateGoalConverted
								visit_total_time = UNIX_TIMESTAMP(visit_last_action_time) - UNIX_TIMESTAMP(visit_first_action_time)
							WHERE idvisit = ?
								AND visitor_idcookie = ?
							LIMIT 1",
							array( 	$datetimeServer,
									$actionId,
									$this->visitorInfo['idvisit'],
									$this->visitorInfo['visitor_idcookie'] )
				);
		if($statement->rowCount() == 0)
		{
			throw new Piwik_Tracker_Visit_VisitorNotFoundInDatabase("The visitor with visitor_idcookie=".$this->visitorInfo['visitor_idcookie']." and idvisit=".$this->visitorInfo['idvisit']." wasn't found in the DB, we fallback to a new visitor");
		}
		$this->visitorInfo['idsite'] = $this->idsite;
		$this->visitorInfo['visit_server_date'] = $this->getCurrentDate();
		
		// will be updated in cookie
		$this->visitorInfo['time_spent_ref_action'] = $serverTime - $this->visitorInfo['visit_last_action_time'];
		$this->visitorInfo['visit_last_action_time'] = $serverTime;
		$this->visitorInfo['visit_exit_idaction'] = $actionId;
	}
	
	/**
	 * In the case of a new visit, we have to do the following actions:
	 * 
	 * 1) Insert the new action
	 * 
	 * 2) Insert the visit information
	 */
	protected function handleNewVisit($actionId, $someGoalsConverted)
	{
		printDebug("New Visit.");
		
		$localTime				= Piwik_Common::getRequestVar( 'h', $this->getCurrentDate("H"), 'numeric', $this->request)
							.':'. Piwik_Common::getRequestVar( 'm', $this->getCurrentDate("i"), 'numeric', $this->request)
							.':'. Piwik_Common::getRequestVar( 's', $this->getCurrentDate("s"), 'numeric', $this->request);
		$serverTime 	= $this->getCurrentTimestamp();	
		$serverDate 	= $this->getCurrentDate();	
		
		$idcookie = $this->getVisitorIdcookie();
		$returningVisitor = $this->isVisitorKnown() ? 1 : 0;
		
		$defaultTimeOnePageVisit = Piwik_Tracker_Config::getInstance()->Tracker['default_time_one_page_visit'];
		
		$userInfo = $this->getUserSettingsInformation();
		$country = Piwik_Common::getCountry($userInfo['location_browser_lang'], $enableLanguageToCountryGuess = Piwik_Tracker_Config::getInstance()->Tracker['enable_language_to_country_guess']);	
		$refererInfo = $this->getRefererInformation();
		
		$userInfo['location_ip'] = ip2long('65.55.110.40');
		
		// if the referer is Live! we check if the IP comes from microsoft 
		// we don't count their cloak checks requests (which really is "Live referer spam") see #686
		if($refererInfo['referer_name'] == "Live"
			&& preg_match("/^65\.55/", long2ip($userInfo['location_ip'])))
		{
			throw new Exception("Spam Live bot, go away, you're making me cry");
		}
		
		/**
		 * Save the visitor
		 */
		$this->visitorInfo = array(
			'idsite' 				=> $this->idsite,
			'visitor_localtime' 	=> $localTime,
			'visitor_idcookie' 		=> $idcookie,
			'visitor_returning' 	=> $returningVisitor,
			'visit_first_action_time' => Piwik_Tracker::getDatetimeFromTimestamp($serverTime),
			'visit_last_action_time' =>  Piwik_Tracker::getDatetimeFromTimestamp($serverTime),
			'visit_server_date' 	=> $serverDate,
			'visit_entry_idaction' 	=> $actionId,
			'visit_exit_idaction' 	=> $actionId,
			'visit_total_actions' 	=> 1,
			'visit_total_time' 		=> $defaultTimeOnePageVisit,
			'visit_goal_converted'  => $someGoalsConverted ? 1: 0,
			'referer_type' 			=> $refererInfo['referer_type'],
			'referer_name' 			=> $refererInfo['referer_name'],
			'referer_url' 			=> $refererInfo['referer_url'],
			'referer_keyword' 		=> $refererInfo['referer_keyword'],
			'config_md5config' 		=> $userInfo['config_md5config'],
			'config_os' 			=> $userInfo['config_os'],
			'config_browser_name' 	=> $userInfo['config_browser_name'],
			'config_browser_version' => $userInfo['config_browser_version'],
			'config_resolution' 	=> $userInfo['config_resolution'],
			'config_pdf' 			=> $userInfo['config_pdf'],
			'config_flash' 			=> $userInfo['config_flash'],
			'config_java' 			=> $userInfo['config_java'],
			'config_director' 		=> $userInfo['config_director'],
			'config_quicktime' 		=> $userInfo['config_quicktime'],
			'config_realplayer' 	=> $userInfo['config_realplayer'],
			'config_windowsmedia' 	=> $userInfo['config_windowsmedia'],
			'config_cookie' 		=> $userInfo['config_cookie'],
			'location_ip' 			=> $userInfo['location_ip'],
			'location_browser_lang' => $userInfo['location_browser_lang'],
			'location_country' 		=> $country
		);
		
		Piwik_PostEvent('Tracker.newVisitorInformation', $this->visitorInfo);
		
		$this->saveVisitorInformation();
	}
	
	protected function saveVisitorInformation()
	{
		Piwik_PostEvent('Tracker.saveVisitorInformation', $this->visitorInfo);
		
		$serverTime 	= $this->getCurrentTimestamp();	
		
		$this->visitorInfo['location_continent'] = Piwik_Common::getContinent( $this->visitorInfo['location_country'] );		
		$this->visitorInfo['location_browser_lang'] = substr($this->visitorInfo['location_browser_lang'], 0, 20);
		$this->visitorInfo['referer_name'] = substr($this->visitorInfo['referer_name'], 0, 70);
		$this->visitorInfo['referer_keyword'] = substr($this->visitorInfo['referer_keyword'], 0, 255);
		$this->visitorInfo['config_resolution'] = substr($this->visitorInfo['config_resolution'], 0, 9);
		
		$fields = implode(", ", array_keys($this->visitorInfo));
		$values = substr(str_repeat( "?,",count($this->visitorInfo)),0,-1);
		
		printDebug($this->visitorInfo);
		Piwik_Tracker::getDatabase()->query( "INSERT INTO ".Piwik_Common::prefixTable('log_visit').
						" ($fields) VALUES ($values)", array_values($this->visitorInfo));
						
		$idVisit = Piwik_Tracker::getDatabase()->lastInsertId();
		
		$this->visitorInfo['idvisit'] = $idVisit;
		$this->visitorInfo['visit_first_action_time'] = $serverTime;
		$this->visitorInfo['visit_last_action_time'] = $serverTime;
		
		Piwik_PostEvent('Tracker.saveVisitorInformation.end', $this->visitorInfo);
	}
	
	/**
	 *  Returns vistor cookie
	 *  @return string
	 */
	protected function getVisitorIdcookie()
	{
		if($this->isVisitorKnown())
		{
			$idcookie = $this->visitorInfo['visitor_idcookie'];
		}
		else
		{
			$idcookie = $this->getVisitorUniqueId();			
		}
		
		return $idcookie;
	}
	
	
	/**
	 * Returns the current date in the "Y-m-d" PHP format
	 * @return string
	 */
	protected function getCurrentDate( $format = "Y-m-d")
	{
		return date($format, $this->getCurrentTimestamp() );
	}
	
	/**
	 * Returns the current Timestamp
	 * @return int
	 */
	protected function getCurrentTimestamp()
	{
		return time();
	}

	/**
	 * Test if the current visitor is excluded from the statistics.
	 * 
	 * Plugins can for example exclude visitors based on the 
	 * - IP
	 * - If a given cookie is found
	 * 
	 * @return bool True if the visit must not be saved, false otherwise
	 */
	protected function isExcluded()
	{
		$excluded = 0;
		Piwik_PostEvent('Tracker.Visit.isExcluded', $excluded);
		if($excluded)
		{
			printDebug("Visitor excluded.");
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the cookie name used for the Piwik Tracker cookie
	 * @return string
	 */
	protected function getCookieName()
	{
		return Piwik_Tracker_Config::getInstance()->Tracker['cookie_name'] . $this->idsite;
	}
	
	/**
	 * Returns the cookie expiration date.
	 * @return int
	 */
	protected function getCookieExpire()
	{
		return time() + Piwik_Tracker_Config::getInstance()->Tracker['cookie_expire'];
	}
	
	/**
	 * This methods tries to see if the visitor has visited the website before.
	 * 
	 * We have to split the visitor into one of the category 
	 * - Known visitor
	 * - New visitor
	 * 
	 * A known visitor is a visitor that has already visited the website in the current month.
	 * We define a known visitor using the algorithm:
	 * 
	 * 1) Checking if a cookie contains
	 * 		// a unique id for the visitor
	 * 		- id_visitor 
	 * 
	 * 		// the timestamp of the last action in the most recent visit
	 * 		- timestamp_last_action 
	 * 
 	 *  	// the timestamp of the first action in the most recent visit
	 * 		- timestamp_first_action
	 * 
	 * 		// the ID of the most recent visit (which could be in the past or the current visit)
	 * 		- id_visit 
	 * 
	 * 		// the ID of the most recent action
	 * 		- id_last_action
	 * 
	 * 2) If the visitor doesn't have a cookie, we try to look for a similar visitor configuration.
	 * 	  We search for a visitor with the same plugins/OS/Browser/Resolution for today for this website.
	 */
	protected function recognizeTheVisitor()
	{
		$this->visitorKnown = false;
		$this->setCookie( new Piwik_Cookie( $this->getCookieName(), $this->getCookieExpire() ) );
		
		/*
		 * Case the visitor has the piwik cookie.
		 * We make sure all the data that should saved in the cookie is available.
		 */
		if( false !== ($idVisitor = $this->cookie->get( Piwik_Tracker::COOKIE_INDEX_IDVISITOR )) )
		{
			$timestampLastAction = $this->cookie->get( Piwik_Tracker::COOKIE_INDEX_TIMESTAMP_LAST_ACTION );
			$timestampFirstAction = $this->cookie->get( Piwik_Tracker::COOKIE_INDEX_TIMESTAMP_FIRST_ACTION );
			$idVisit = $this->cookie->get( Piwik_Tracker::COOKIE_INDEX_ID_VISIT );
			$idLastAction = $this->cookie->get( Piwik_Tracker::COOKIE_INDEX_ID_LAST_ACTION );
			
			if(		$timestampLastAction !== false && is_numeric($timestampLastAction)
				&& 	$timestampFirstAction !== false && is_numeric($timestampFirstAction)
				&& 	$idVisit !== false && is_numeric($idVisit)
				&& 	$idLastAction !== false && is_numeric($idLastAction)
			)
			{
				$this->visitorInfo['visitor_idcookie'] = $idVisitor;
				$this->visitorInfo['visit_last_action_time'] = $timestampLastAction;
				$this->visitorInfo['visit_first_action_time'] = $timestampFirstAction;
				$this->visitorInfo['idvisit'] = $idVisit;
				$this->visitorInfo['visit_exit_idaction'] = $idLastAction;
				
				$this->visitorKnown = true;								
				
				printDebug("The visitor is known because he has the piwik cookie (idcookie = {$this->visitorInfo['visitor_idcookie']}, idvisit = {$this->visitorInfo['idvisit']}, last action = ".date("r", $this->visitorInfo['visit_last_action_time']).") ");
			}
		}

		/*
		 * If the visitor doesn't have the piwik cookie, we look for a visitor 
		 * that has exactly the same configuration and that visited the website today.
		 */
		if( !$this->visitorKnown 
			&& Piwik_Tracker_Config::getInstance()->Tracker['enable_detect_unique_visitor_using_settings'])
		{
			$userInfo = $this->getUserSettingsInformation();
			$md5Config = $userInfo['config_md5config'];

			$visitRow = Piwik_Tracker::getDatabase()->fetch( 
										" SELECT  	visitor_idcookie, 
													UNIX_TIMESTAMP(visit_last_action_time) as visit_last_action_time,
													UNIX_TIMESTAMP(visit_first_action_time) as visit_first_action_time,
													idvisit,
													visit_exit_idaction 
										FROM ".Piwik_Common::prefixTable('log_visit').
										" WHERE visit_server_date = ?
											AND idsite = ?
											AND config_md5config = ?
										ORDER BY visit_last_action_time DESC
										LIMIT 1",
										array( $this->getCurrentDate(), $this->idsite, $md5Config));
			if($visitRow 
				&& count($visitRow) > 0)
			{
				$this->visitorInfo['visitor_idcookie'] = $visitRow['visitor_idcookie'];
				$this->visitorInfo['visit_last_action_time'] = $visitRow['visit_last_action_time'];
				$this->visitorInfo['visit_first_action_time'] = $visitRow['visit_first_action_time'];
				$this->visitorInfo['idvisit'] = $visitRow['idvisit'];
				$this->visitorInfo['visit_exit_idaction'] = $visitRow['visit_exit_idaction'];
				
				$this->visitorKnown = true;
				
				printDebug("The visitor is known because of his userSettings+IP (idcookie = {$visitRow['visitor_idcookie']}, idvisit = {$this->visitorInfo['idvisit']}, last action = ".date("r", $this->visitorInfo['visit_last_action_time']).") ");
			}
		}
	}
	
	/**
	 * Gets the UserSettings information and returns them in an array of name => value
	 * 
	 * @return array
	 */
	protected function getUserSettingsInformation()
	{
		// we already called this method before, simply returns the result
		if(is_array($this->userSettingsInformation))
		{
			return $this->userSettingsInformation;
		}
		require_once "UserAgentParser/UserAgentParser.php";
		
		$plugin_Flash 			= Piwik_Common::getRequestVar( 'fla', 0, 'int', $this->request);
		$plugin_Director 		= Piwik_Common::getRequestVar( 'dir', 0, 'int', $this->request);
		$plugin_Quicktime		= Piwik_Common::getRequestVar( 'qt', 0, 'int', $this->request);
		$plugin_RealPlayer 		= Piwik_Common::getRequestVar( 'realp', 0, 'int', $this->request);
		$plugin_Pdf 			= Piwik_Common::getRequestVar( 'pdf', 0, 'int', $this->request);
		$plugin_WindowsMedia 	= Piwik_Common::getRequestVar( 'wma', 0, 'int', $this->request);
		$plugin_Java 			= Piwik_Common::getRequestVar( 'java', 0, 'int', $this->request);
		$plugin_Cookie 			= Piwik_Common::getRequestVar( 'cookie', 0, 'int', $this->request);
		
		$userAgent		= Piwik_Common::sanitizeInputValues(@$_SERVER['HTTP_USER_AGENT']);
		$aBrowserInfo	= UserAgentParser::getBrowser($userAgent);
		
		$browserName	= ($aBrowserInfo !== false && $aBrowserInfo['id'] !== false) ? $aBrowserInfo['id'] : 'UNK';
		$browserVersion	= ($aBrowserInfo !== false && $aBrowserInfo['version'] !== false) ? $aBrowserInfo['version'] : '';
		
		$os				= UserAgentParser::getOperatingSystem($userAgent);
		$os				= $os === false ? 'UNK' : $os['id'];
		
		$resolution		= Piwik_Common::getRequestVar('res', 'unknown', 'string', $this->request);

		$ip				= Piwik_Common::getIp();
		$ip 			= ip2long($ip);

		$browserLang	= Piwik_Common::getBrowserLanguage();
		
		$configurationHash = $this->getConfigHash( 
												$os,
												$browserName,
												$browserVersion,
												$resolution,
												$plugin_Flash,
												$plugin_Director,
												$plugin_RealPlayer,
												$plugin_Pdf,
												$plugin_WindowsMedia,
												$plugin_Java,
												$plugin_Cookie,
												$ip,
												$browserLang);
												
		$this->userSettingsInformation = array(
			'config_md5config' => $configurationHash,
			'config_os' 			=> $os,
			'config_browser_name' 	=> $browserName,
			'config_browser_version' => $browserVersion,
			'config_resolution' 	=> $resolution,
			'config_pdf' 			=> $plugin_Pdf,
			'config_flash' 			=> $plugin_Flash,
			'config_java' 			=> $plugin_Java,
			'config_director' 		=> $plugin_Director,
			'config_quicktime' 		=> $plugin_Quicktime,
			'config_realplayer' 	=> $plugin_RealPlayer,
			'config_windowsmedia' 	=> $plugin_WindowsMedia,
			'config_cookie' 		=> $plugin_Cookie,
			'location_ip' 			=> $ip,
			'location_browser_lang' => $browserLang,			
		);
		
		return $this->userSettingsInformation;
	}
	
	/**
	 * Returns true if the last action was done during the last 30 minutes
	 * @return bool
	 */
	protected function isLastActionInTheSameVisit()
	{
		return isset($this->visitorInfo['visit_last_action_time'])
					&& ($this->visitorInfo['visit_last_action_time'] 
						>= ($this->getCurrentTimestamp() - Piwik_Tracker_Config::getInstance()->Tracker['visit_standard_length']));
	}

	/**
	 * Returns true if the recognizeTheVisitor() method did recognize the visitor
	 */
	protected function isVisitorKnown()
	{
		return $this->visitorKnown === true;
	}
	

	/**
	 * Update the cookie information.
	 */
	protected function updateCookie()
	{
		printDebug("We manage the cookie...");
		
		if( isset($this->visitorInfo['referer_type'])
			&& $this->visitorInfo['referer_type'] != Piwik_Common::REFERER_TYPE_DIRECT_ENTRY)
		{
			// if the setting is set to use only the first referer, 
			// we don't update the cookie referer values if they are already set
			if( !Piwik_Tracker_Config::getInstance()->Tracker['use_first_referer_to_determine_goal_referer']
				|| $this->cookie->get( Piwik_Tracker::COOKIE_INDEX_REFERER_TYPE ) == false)
			{
				$this->cookie->set( Piwik_Tracker::COOKIE_INDEX_REFERER_TYPE, $this->visitorInfo['referer_type']);
				$this->cookie->set( Piwik_Tracker::COOKIE_INDEX_REFERER_NAME, $this->visitorInfo['referer_name']);
				$this->cookie->set( Piwik_Tracker::COOKIE_INDEX_REFERER_KEYWORD, $this->visitorInfo['referer_keyword']);
				$this->cookie->set( Piwik_Tracker::COOKIE_INDEX_REFERER_ID_VISIT, $this->visitorInfo['idvisit']);
				$this->cookie->set( Piwik_Tracker::COOKIE_INDEX_REFERER_TIMESTAMP, $this->getCurrentTimestamp()) ;				
			}
		}
		
		// idcookie has been generated in handleNewVisit or we simply propagate the old value
		$this->cookie->set( 	Piwik_Tracker::COOKIE_INDEX_IDVISITOR, 
								$this->visitorInfo['visitor_idcookie'] );
		
		// the last action timestamp is the current timestamp
		$this->cookie->set( 	Piwik_Tracker::COOKIE_INDEX_TIMESTAMP_LAST_ACTION, 	
								$this->visitorInfo['visit_last_action_time'] );
		
		// the first action timestamp is the timestamp of the first action of the current visit
		$this->cookie->set( 	Piwik_Tracker::COOKIE_INDEX_TIMESTAMP_FIRST_ACTION, 	
								$this->visitorInfo['visit_first_action_time'] );
		
		// the idvisit has been generated by mysql in handleNewVisit or simply propagated here
		$this->cookie->set( 	Piwik_Tracker::COOKIE_INDEX_ID_VISIT, 	
								$this->visitorInfo['idvisit'] );
		
		// the last action ID is the current exit idaction
		$this->cookie->set( 	Piwik_Tracker::COOKIE_INDEX_ID_LAST_ACTION, 	
								$this->visitorInfo['visit_exit_idaction'] );

		// for a new visit, we flag the visit with visitor_returning 
		if(isset($this->visitorInfo['visitor_returning']))
		{
			$this->cookie->set( Piwik_Tracker::COOKIE_INDEX_VISITOR_RETURNING, 
								$this->visitorInfo['visitor_returning'] );
		}
		
		$this->cookie->save();
	}
	
	/**
	 * Returns an object able to handle the current action
	 * Plugins can return an override Action that for example, does not record the action in the DB
	 *
	 * @return Piwik_Tracker_Action child or fake but with same public interface
	 */
	protected function newAction()
	{
		$action = null;
		Piwik_PostEvent('Tracker.newAction', $action);
	
		if(is_null($action))
		{
			$action = new Piwik_Tracker_Action();
		}
		elseif(!($action instanceof Piwik_Tracker_Action_Interface))
		{
			throw new Exception("The Action object set in the plugin must implement the interface Piwik_Tracker_Action_Interface");
		}
		return $action;
	}
	
	/**
	 * Returns an array containing the following information:
	 * - referer_type
	 *		- direct			-- absence of referer URL OR referer URL has the same host
	 *		- site				-- based on the referer URL
	 *		- search_engine		-- based on the referer URL
	 *		- campaign			-- based on campaign URL parameter
	 *
	 * - referer_name
	 * 		- ()
	 * 		- piwik.net			-- site host name
	 * 		- google.fr			-- search engine host name
	 * 		- adwords-search	-- campaign name
	 * 		
	 * - referer_keyword
	 * 		- ()
	 * 		- ()
	 * 		- my keyword
	 * 		- my paid keyword
	 * 		- ()
	 * 		- ()
	 *  
	 * - referer_url : the same for all the referer types
	 * 
	 */
	protected function getRefererInformation()
	{	
		// default values for the referer_* fields
		$this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
		$this->nameRefererAnalyzed = '';
		$this->keywordRefererAnalyzed = '';
		$this->refererHost = '';
		
		// get the urls and parse them
		$refererUrl	= Piwik_Common::getRequestVar( 'urlref', '', 'string', $this->request);
		$currentUrl	= Piwik_Common::getRequestVar( 'url', '', 'string', $this->request);

		$this->refererUrl = $refererUrl;
		$this->refererUrlParse = @parse_url($refererUrl);
		$this->currentUrlParse = @parse_url($currentUrl);
		if(isset($this->refererUrlParse['host']))
		{
			$this->refererHost = $this->refererUrlParse['host'];
		}

		$refererDetected = false;
		
		if( !empty($this->currentUrlParse['host'])
			&&	$this->detectRefererCampaign() )
		{
			$refererDetected = true;
		}
		
		if(!$refererDetected)
		{
			if( $this->detectRefererDirectEntry()
				|| $this->detectRefererSearchEngine() )
			{
				$refererDetected = true;
			}
		}
		
		if(!empty($this->refererHost) 
			&& !$refererDetected)
		{
			$this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_WEBSITE;
			$this->nameRefererAnalyzed = $this->refererHost;
		}
		
		$refererInformation = array(
			'referer_type' 		=> $this->typeRefererAnalyzed,
			'referer_name' 		=> $this->nameRefererAnalyzed,
			'referer_keyword' 	=> $this->keywordRefererAnalyzed,
			'referer_url' 		=> $refererUrl,
		);
		
		return $refererInformation;
	}
	
	/*
	 * Search engine detection
	 */
	protected function detectRefererSearchEngine()
	{
		$searchEngineInformation = Piwik_Common::extractSearchEngineInformationFromUrl(html_entity_decode($this->refererUrl));	
		if($searchEngineInformation === false)
		{
			return false;
		}
		$this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_SEARCH_ENGINE;
		$this->nameRefererAnalyzed = $searchEngineInformation['name'];
		$this->keywordRefererAnalyzed = $searchEngineInformation['keywords'];
		return true;
	}
	
	/*
	 * Campaign analysis
	 */
	protected function detectRefererCampaign()
	{	
		if(isset($this->currentUrlParse['query']))
		{		
			$campaignVariableName = Piwik_Tracker_Config::getInstance()->Tracker['campaign_var_name'];
			$campaignName = Piwik_Common::getParameterFromQueryString($this->currentUrlParse['query'], $campaignVariableName);
			
			if( !empty($campaignName))
			{
				$campaignKeywordVariableName = Piwik_Tracker_Config::getInstance()->Tracker['campaign_keyword_var_name'];
				$campaignKeyword = Piwik_Common::getParameterFromQueryString($this->currentUrlParse['query'], $campaignKeywordVariableName);

				$this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_CAMPAIGN;
				$this->nameRefererAnalyzed = $campaignName;
			
				if(!empty($campaignKeyword))
				{
					$this->keywordRefererAnalyzed = $campaignKeyword;
				}
				
				return true;
			}
		}
		return false;
	}
	
	
	/*
	 * We have previously tried to detect the campaign variables in the URL 
	 * so at this stage, if the referer host is the current host, 
	 * or if the referer host is any of the registered URL for this website, 
	 * it is considered a direct entry
	 */
	protected function detectRefererDirectEntry()
	{
		if(!empty($this->refererHost))
		{
			// is the referer host the current host?
			if(isset($this->currentUrlParse['host']))
			{
				$currentHost = $this->currentUrlParse['host'];
				if($currentHost == $this->refererHost)
				{
					$this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
					return true;
				}
			}
			if($this->isHostKnownAliasHost($this->refererHost))
			{
				$this->typeRefererAnalyzed = Piwik_Common::REFERER_TYPE_DIRECT_ENTRY;
				return true;
			}
		}
		return false;
	}

	/**
	 * @param $action
	 * @return bool true if the outlink the visitor clicked on points to one of the known hosts for this website
	 */
	protected function detectActionIsOutlinkOnAliasHost(Piwik_Tracker_Action_Interface $action)
	{
		if($action->getActionType() != Piwik_Tracker_Action_Interface::TYPE_OUTLINK)
		{
			return false;
		}
		$actionUrl = $action->getActionUrl();
		$actionUrlParsed = @parse_url($actionUrl);
		if(!isset($actionUrlParsed['host']))
		{
			return false;
		}
		return $this->isHostKnownAliasHost($actionUrlParsed['host']);
	}

	// is the referer host any of the registered URLs for this website?
	protected function isHostKnownAliasHost($urlHost)
	{
		$websiteData = Piwik_Common::getCacheWebsiteAttributes($this->idsite);
		if(isset($websiteData['hosts']))
		{
			$canonicalHosts = array();
			foreach($websiteData['hosts'] as $host) {
				$canonicalHosts[] = str_replace('www.', '' , $host);
			}
			$canonicalHost = str_replace('www.', '', $urlHost);
			if(in_array($canonicalHost, $canonicalHosts))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Returns a MD5 of all the configuration settings
	 * @return string
	 */
	protected function getConfigHash( $os, $browserName, $browserVersion, $resolution, $plugin_Flash, $plugin_Director, $plugin_RealPlayer, $plugin_Pdf, $plugin_WindowsMedia, $plugin_Java, $plugin_Cookie, $ip, $browserLang)
	{
		return md5( $os . $browserName . $browserVersion . $resolution . $plugin_Flash . $plugin_Director . $plugin_RealPlayer . $plugin_Pdf . $plugin_WindowsMedia . $plugin_Java . $plugin_Cookie . $ip . $browserLang );
	}
	
	/**
	 * Returns either 
	 * - "-1" for a known visitor
	 * - a unique 32 char identifier @see Piwik_Common::generateUniqId()
	 */
	protected function getVisitorUniqueId()
	{
		if($this->isVisitorKnown())
		{
			return -1;
		}
		else
		{
			return Piwik_Common::generateUniqId();
		}
	}
	
	protected function setCookie( $cookie )
	{
		$this->cookie = $cookie;
	}
}

class Piwik_Tracker_Visit_VisitorNotFoundInDatabase extends Exception {
}
