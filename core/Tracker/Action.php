<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Interface of the Action object.
 * New Action classes can be defined in plugins and used instead of the default one.
 * 
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
interface Piwik_Tracker_Action_Interface {
	const TYPE_ACTION_URL   = 1;
	const TYPE_OUTLINK  = 2;
	const TYPE_DOWNLOAD = 3;
	const TYPE_ACTION_NAME = 4;
	const TYPE_ECOMMERCE_ITEM_SKU = 5;
	const TYPE_ECOMMERCE_ITEM_NAME = 6;
	const TYPE_ECOMMERCE_ITEM_CATEGORY = 7;
	
	public function setRequest($requestArray);
	public function setIdSite( $idSite );
	public function init();
	public function getActionUrl();
	public function getActionName();
	public function getActionType();
	public function record( $idVisit, $visitorIdCookie, $idRefererActionUrl, $idRefererActionName, $timeSpentRefererAction );
	public function getIdActionUrl();
	public function getIdActionName();
	public function getIdLinkVisitAction();
}

/**
 * Handles an action (page view, download or outlink) by the visitor.
 * Parses the action name and URL from the request array, then records the action in the log table.
 * 
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_Action implements Piwik_Tracker_Action_Interface
{
	private $request;
	private $idSite;
	private $timestamp;
	private $idLinkVisitAction;
	private $idActionName = false;
	private $idActionUrl = false;

	private $actionName;
	private $actionType;
	private $actionUrl;
	
	static private $queryParametersToExclude = array('phpsessid', 'jsessionid', 'sessionid', 'aspsessionid', 'fb_xd_fragment', 'fb_comment_id');

	/**
	 * Map URL prefixes to integers.
	 * @see self::normalizeUrl(), self::reconstructNormalizedUrl()
	 */
	static private $urlPrefixMap = array(
		'http://www.' => 1,
		'http://' => 0,
		'https://www.' => 3,
		'https://' => 2
	);

	/**
	 * Extract the prefix from a URL.
	 * Return the prefix ID and the rest.
	 * 
	 * @param string $url
	 * @return array
	 */
	static public function normalizeUrl($url)
	{
		foreach (self::$urlPrefixMap as $prefix => $id)
		{
			if (strtolower(substr($url, 0, strlen($prefix))) == $prefix)
			{
				return array(
					'url' => substr($url, strlen($prefix)),
					'prefixId' => $id
				);
			}
		}
		return array('url' => $url, 'prefixId' => null);
	}

	/**
	 * Build the full URL from the prefix ID and the rest.
	 * 
	 * @param string $url
	 * @param integer $prefixId
	 * @return string
	 */
	static public function reconstructNormalizedUrl($url, $prefixId)
	{
		$map = array_flip(self::$urlPrefixMap);
		if ($prefixId !== null && isset($map[$prefixId]))
		{
			return $map[$prefixId].$url;
		}
		return $url;
	}
	

	/**
	 * Set request parameters
	 *
	 * @param array  $requestArray
	 */
	public function setRequest($requestArray)
	{
		$this->request = $requestArray;
	}

	/**
	 * Returns the current set request parameters
	 *
	 * @return array
	 */
	public function getRequest()
	{
	    return $this->request;
	}
	
	/**
	 * Returns URL of the page currently being tracked, or the file being downloaded, or the outlink being clicked
	 *
	 * @return string
	 */
	public function getActionUrl()
	{
		return $this->actionUrl;
	}
	public function getActionName()
	{
		return $this->actionName;
	}
	public function getActionType()
	{
		return $this->actionType;
	}
	public function getActionNameType()
	{
		$actionNameType = null;

		// we can add here action types for names of other actions than page views (like downloads, outlinks)
		switch( $this->getActionType() )
		{
			case Piwik_Tracker_Action_Interface::TYPE_ACTION_URL:
				$actionNameType = Piwik_Tracker_Action_Interface::TYPE_ACTION_NAME;
				break;
		}

		return $actionNameType;
	}

	public function getIdActionUrl()
	{
		return $this->idActionUrl;
	}
	public function getIdActionName()
	{
		return $this->idActionName;
	}
	
	protected function setActionName($name)
	{
		$name = self::cleanupString($name);
		$this->actionName = $name;
	}
	
	protected function setActionType($type)
	{
		$this->actionType = $type;
	}
	
	protected function setActionUrl($url)
	{
		$url = self::excludeQueryParametersFromUrl($url, $this->idSite);
		$this->actionUrl = $url;
	}
	
	/**
	 * Converts Matrix URL format 
	 * from http://example.org/thing;paramA=1;paramB=6542
	 * to   http://example.org/thing?paramA=1&paramB=6542
	 * 
	 * @param string $url
	 */
	static public function convertMatrixUrl($originalUrl)
	{
		$posFirstSemiColon = strpos($originalUrl,";");
		if($posFirstSemiColon === false)
		{
			return $originalUrl;
		}
		$posQuestionMark = strpos($originalUrl,"?");
	    $replace = ($posQuestionMark === false);
	    if ($posQuestionMark > $posFirstSemiColon) 
	    {
	    	$originalUrl = substr_replace($originalUrl,";",$posQuestionMark,1);
	    	$replace = true;
	    }
	    if($replace) 
	    { 
	    	$originalUrl = substr_replace($originalUrl,"?",strpos($originalUrl,";"),1);
	    	$originalUrl = str_replace(";","&",$originalUrl);
	    }
	    return $originalUrl;
	}
	
	static public function cleanupUrl($url)
	{
		$url = Piwik_Common::unsanitizeInputValue($url);
		$url = self::cleanupString($url);
		$url = self::convertMatrixUrl($url);
		return $url;
	}

	static public function excludeQueryParametersFromUrl($originalUrl, $idSite)
	{
		$website = Piwik_Common::getCacheWebsiteAttributes( $idSite );
		$originalUrl = self::cleanupUrl($originalUrl);
		$parsedUrl = @parse_url($originalUrl);
		if(empty($parsedUrl['query']))
		{
			return $originalUrl;
		}
		$campaignTrackingParameters = Piwik_Common::getCampaignParameters();
		
		$campaignTrackingParameters = array_merge(
				$campaignTrackingParameters[0], // campaign name parameters
				$campaignTrackingParameters[1] // campaign keyword parameters
		);	
				
		$excludedParameters = isset($website['excluded_parameters']) 
									? $website['excluded_parameters'] 
									: array();
									
		$parametersToExclude = array_merge( $excludedParameters, 
											self::$queryParametersToExclude,
											$campaignTrackingParameters);
											
		$parametersToExclude = array_map('strtolower', $parametersToExclude);
		$queryParameters = Piwik_Common::getArrayFromQueryString($parsedUrl['query']);
		
		$validQuery = '';
		$separator = '&';
		foreach($queryParameters as $name => $value)
		{
			// decode encoded square brackets
            $name = str_replace(array('%5B','%5D'),array('[',']'),$name);

			if(!in_array(strtolower($name), $parametersToExclude))
			{
				if (is_array($value))
				{
					foreach ($value as $param)
					{
						if($param === false)
						{
							$validQuery .= $name.'[]'.$separator;
						}
						else
						{
							$validQuery .= $name.'[]='.$param.$separator;
						}
					}
				}
				else if($value === false)
				{
					$validQuery .= $name.$separator;
				}
				else
				{
					$validQuery .= $name.'='.$value.$separator;
				}
			}
		}
		$parsedUrl['query'] = substr($validQuery,0,-strlen($separator));
		$url = Piwik_Common::getParseUrlReverse($parsedUrl);
		printDebug('Excluding parameters "'.implode(',',$excludedParameters).'" from URL');
		if($originalUrl != $url)
		{
			printDebug(' Before was "'.$originalUrl.'"');
			printDebug(' After is "'.$url.'"');
		}
		return $url;
	}
	
	public function init()
	{
		$info = $this->extractUrlAndActionNameFromRequest();
		$this->setActionName($info['name']);
		$this->setActionType($info['type']);
		$this->setActionUrl($info['url']);
	}
	
	static public function getSqlSelectActionId()
	{
		$sql = "SELECT idaction, type, name
							FROM ".Piwik_Common::prefixTable('log_action')
						."  WHERE "
						."		( hash = CRC32(?) AND name = ? AND type = ? ) ";
		return $sql;
	}
	
	/**
	 * This function will find the idaction from the lookup table piwik_log_action,
	 * given an Action name and type.
	 * 
	 * This is used to record Page URLs, Page Titles, Ecommerce items SKUs, item names, item categories
	 * 
	 * If the action name does not exist in the lookup table, it will INSERT it
	 * @param array $actionNamesAndTypes Array of one or many (name,type)
	 * @return array Returns the input array, with the idaction appended ie. Array of one or many (name,type,idaction)
	 */
	static public function loadActionId( $actionNamesAndTypes )
	{
		// First, we try and select the actions that are already recorded
		$sql = self::getSqlSelectActionId();
		$bind = array();
		$normalizedUrls = array();
		$i = 0;
		foreach($actionNamesAndTypes as $index => &$actionNameType)
		{
			list($name,$type) = $actionNameType;
			if(empty($name))
			{
				$actionNameType[] = false;
				continue;
			}
			if($i > 0)
			{
				$sql .= " OR ( hash = CRC32(?) AND name = ? AND type = ? ) ";
			}
			if ($type == Piwik_Tracker_Action::TYPE_ACTION_URL)
			{
				// normalize urls by stripping protocol and www
				$normalizedUrls[$index] = self::normalizeUrl($name);
				$name = $normalizedUrls[$index]['url'];
			}
			$bind[] = $name;
			$bind[] = $name;
			$bind[] = $type;
			$i++;
		}
		// Case URL & Title are empty
		if(empty($bind))
		{
			return $actionNamesAndTypes;
		}
		$actionIds = Piwik_Tracker::getDatabase()->fetchAll($sql, $bind);
		
		// For the Actions found in the lookup table, add the idaction in the array, 
		// If not found in lookup table, queue for INSERT
		$actionsToInsert = array();
		foreach($actionNamesAndTypes as $index => &$actionNameType)
		{
			list($name,$type) = $actionNameType;
			if(empty($name)) { continue; }
			if(isset($normalizedUrls[$index]))
			{
				$name = $normalizedUrls[$index]['url'];
			}
			$found = false;
			foreach($actionIds as $row)
			{
				if($name == $row['name']
					&& $type == $row['type'])
				{
					$found = true;
					$actionNameType[] = $row['idaction'];
					continue;
				}
			}
			if(!$found)
			{
				$actionsToInsert[] = $index;
			}
		}
		
		$sql = "INSERT INTO ". Piwik_Common::prefixTable('log_action'). 
				"( name, hash, type, url_prefix ) VALUES (?,CRC32(?),?,?)";
		// Then, we insert all new actions in the lookup table
		foreach($actionsToInsert as $actionToInsert)
		{
			list($name,$type) = $actionNamesAndTypes[$actionToInsert];
			
			$urlPrefix = null;
			if(isset($normalizedUrls[$actionToInsert]))
			{
				$name = $normalizedUrls[$actionToInsert]['url'];
				$urlPrefix = $normalizedUrls[$actionToInsert]['prefixId'];
			}
			
			Piwik_Tracker::getDatabase()->query($sql, array($name, $name, $type, $urlPrefix));
			$actionId = Piwik_Tracker::getDatabase()->lastInsertId();
			printDebug("Recorded a new action (".self::getActionTypeName($type).") in the lookup table: ". $name . " (idaction = ".$actionId.")");
			
			$actionNamesAndTypes[$actionToInsert][] = $actionId;
		}
		return $actionNamesAndTypes;
	}
	
	static public function getActionTypeName($type)
	{
		switch($type)
		{
			case self::TYPE_ACTION_URL: return 'Page URL'; break;
			case self::TYPE_OUTLINK: return 'Outlink URL'; break;
			case self::TYPE_DOWNLOAD: return 'Download URL'; break;
			case self::TYPE_ACTION_NAME: return 'Page Title'; break;
			case self::TYPE_ECOMMERCE_ITEM_SKU: return 'Ecommerce Item SKU'; break;
			case self::TYPE_ECOMMERCE_ITEM_NAME: return 'Ecommerce Item Name'; break;
			case self::TYPE_ECOMMERCE_ITEM_CATEGORY: return 'Ecommerce Item Category'; break;
			default: throw new Exception("Unexpected action type ".$type); break;
		}
	}
	
	/**
	 * Loads the idaction of the current action name and the current action url.
	 * These idactions are used in the visitor logging table to link the visit information
	 * (entry action, exit action) to the actions.
	 * These idactions are also used in the table that links the visits and their actions.
	 * 
	 * The methods takes care of creating a new record(s) in the action table if the existing
	 * action name and action url doesn't exist yet.
	 */
	function loadIdActionNameAndUrl()
	{
		if( $this->idActionUrl !== false 
			&& $this->idActionName !== false )
		{
			return;
		}
		$actions = array();
		$action = array($this->getActionName(), $this->getActionNameType());
		if(!is_null($action[1]))
		{
			$actions[] = $action;
		}
		$action = array($this->getActionUrl(), $this->getActionType());
		if(!is_null($action[1]))
		{
			$actions[] = $action;
		}
		$loadedActionIds = self::loadActionId($actions);
		
		foreach($loadedActionIds as $loadedActionId)
		{
			list($name, $type, $actionId) = $loadedActionId;
			if($type == $this->getActionType())
			{
				$this->idActionUrl = $actionId;
			}
			elseif($type == $this->getActionNameType())
			{
				$this->idActionName = $actionId;
			}
		}
	}
	
	/**
	 * @param int $idSite
	 */
	function setIdSite($idSite)
	{
		$this->idSite = $idSite;
	}
	
	function setTimestamp($timestamp)
	{
		$this->timestamp = $timestamp;
	}


	/**
	 * Records in the DB the association between the visit and this action.
	 *
	 * @param int $idVisit is the ID of the current visit in the DB table log_visit
	 * @param $visitorIdCookie
	 * @param int $idRefererActionUrl is the ID of the last action done by the current visit.
	 * @param $idRefererActionName
	 * @param int $timeSpentRefererAction is the number of seconds since the last action was done.
	 *                 It is directly related to idRefererActionUrl.
	 */
	 public function record( $idVisit, $visitorIdCookie, $idRefererActionUrl, $idRefererActionName, $timeSpentRefererAction)
	 {
		$this->loadIdActionNameAndUrl();
		
		$idActionName = in_array($this->getActionType(), array(Piwik_Tracker_Action::TYPE_ACTION_NAME, Piwik_Tracker_Action::TYPE_ACTION_URL))
							? (int)$this->getIdActionName()
							: null;
		$insert = array(
			'idvisit' => $idVisit, 
			'idsite' => $this->idSite, 
			'idvisitor' => $visitorIdCookie, 
			'server_time' => Piwik_Tracker::getDatetimeFromTimestamp($this->timestamp), 
			'idaction_url' => (int)$this->getIdActionUrl(), 
			'idaction_name' => $idActionName, 
			'idaction_url_ref' => $idRefererActionUrl, 
			'idaction_name_ref' => $idRefererActionName, 
			'time_spent_ref_action' => $timeSpentRefererAction
		);
		$customVariables = Piwik_Tracker_Visit::getCustomVariables($scope = 'page', $this->request);
		$insert = array_merge($insert, $customVariables);

		// Mysqli apparently does not like NULL inserts?
		$insertWithoutNulls = array();
		foreach($insert as $column => $value)
		{
			if(!is_null($value))
			{
				$insertWithoutNulls[$column] = $value;
			}
		}
		
		$fields = implode(", ", array_keys($insertWithoutNulls));
		$bind = array_values($insertWithoutNulls);
		$values = Piwik_Common::getSqlStringFieldsArray($insertWithoutNulls);

		$sql = "INSERT INTO ".Piwik_Common::prefixTable('log_link_visit_action'). " ($fields) VALUES ($values)";
		Piwik_Tracker::getDatabase()->query( $sql, $bind ); 
		
		$this->idLinkVisitAction = Piwik_Tracker::getDatabase()->lastInsertId(); 
		
		$info = array( 
			'idSite' => $this->idSite, 
			'idLinkVisitAction' => $this->idLinkVisitAction, 
			'idVisit' => $idVisit, 
			'idRefererActionUrl' => $idRefererActionUrl, 
			'idRefererActionName' => $idRefererActionName, 
			'timeSpentRefererAction' => $timeSpentRefererAction, 
		); 
		printDebug($insertWithoutNulls);

		/* 
		* send the Action object ($this)  and the list of ids ($info) as arguments to the event 
		*/ 
		Piwik_PostEvent('Tracker.Action.record', $this, $info);
	 }
	 
	/**
	 * Returns the ID of the newly created record in the log_link_visit_action table
	 *
	 * @return int | false
	 */
	public function getIdLinkVisitAction()
	{
		return $this->idLinkVisitAction;
	}
	
	 /**
	 * Generates the name of the action from the URL or the specified name.
	 * Sets the name as $this->actionName
	  *
	 * @return array
	 */
	protected function extractUrlAndActionNameFromRequest()
	{
		$actionName = null;
		
		// download?
		$downloadUrl = Piwik_Common::getRequestVar( 'download', '', 'string', $this->request);
		if(!empty($downloadUrl))
		{
			$actionType = self::TYPE_DOWNLOAD;
			$url = $downloadUrl;
		}
		
		// outlink?
		if(empty($actionType))
		{
			$outlinkUrl = Piwik_Common::getRequestVar( 'link', '', 'string', $this->request);
			if(!empty($outlinkUrl))
			{
				$actionType = self::TYPE_OUTLINK;
				$url = $outlinkUrl;
			}
		}

		// handle encoding
		$actionName = Piwik_Common::getRequestVar( 'action_name', '', 'string', $this->request);

		// defaults to page view 
		if(empty($actionType))
		{
			$actionType = self::TYPE_ACTION_URL;
			$url = Piwik_Common::getRequestVar( 'url', '', 'string', $this->request);

			// get the delimiter, by default '/'; BC, we read the old action_category_delimiter first (see #1067) 
			$actionCategoryDelimiter = isset(Piwik_Config::getInstance()->General['action_category_delimiter'])
										? Piwik_Config::getInstance()->General['action_category_delimiter']
										: Piwik_Config::getInstance()->General['action_url_category_delimiter'];
			
			// create an array of the categories delimited by the delimiter
			$split = explode($actionCategoryDelimiter, $actionName);
			
			// trim every category
			$split = array_map('trim', $split);
			
			// remove empty categories
			$split = array_filter($split, 'strlen');
			
			// rebuild the name from the array of cleaned categories
			$actionName = implode($actionCategoryDelimiter, $split);
		}
		$url = self::cleanupString($url);
		
		if(!Piwik_Common::isLookLikeUrl($url))
		{
			$url = '';
		}
		$actionName = self::cleanupString($actionName);

		return array(
			'name' => empty($actionName) ? '' : $actionName,
			'type' => $actionType,
			'url'  => $url,
		);
	}

	/**
	 * Clean up string contents (filter, truncate, ...)
	 *
	 * @param string $string Dirty string
	 * @return string
	 */
	protected static function cleanupString($string)
	{
		$string = trim($string);
		$string = str_replace(array("\n", "\r", "\0"), '', $string);
		
		$limit = Piwik_Config::getInstance()->Tracker['page_maximum_length'];
		return substr($string, 0, $limit);
	}
}
