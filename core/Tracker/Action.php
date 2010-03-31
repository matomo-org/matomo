<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
	
	public function setRequest($requestArray);
	public function setIdSite( $idSite );
	public function init();
	public function getActionUrl();
	public function getActionName();
	public function getActionType();
	public function record( $idVisit, $idRefererAction, $timeSpentRefererAction );
	public function getIdActionUrl();
	public function getIdActionName();
	public function getIdLinkVisitAction();
}

/**
 * Handles an action by the visitor.
 * A request to the piwik.php script is associated with one Action.
 * This class is used to build the Action Name (which can be built from the URL, 
 * or can be directly specified in the JS code, etc.).
 * It also saves the Action when necessary in the DB. 
 *  
 * About the Action concept:
 * - An action is defined by a name.
 * - The name can be specified in the JS Code in the variable 'action_name'
 *    For example you can decide to use the javascript value document.title as an action_name
 * - Handling UTF8 in the action name
 * PLUGIN_IDEA - An action is associated to URLs and link to the URL from the reports (currently actions do not link to the url of the pages)
 * PLUGIN_IDEA - An action hit by a visitor is associated to the HTML title of the page that triggered the action and this HTML title is displayed in the interface
 * 
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_Action implements Piwik_Tracker_Action_Interface
{
	private $request;
	private $idSite;
	private $idLinkVisitAction;
	private $idActionName = null;
	private $idActionUrl = null;

	private $actionName;
	private $actionType;
	private $actionUrl;
	
	private $queryParametersToExclude = array('phpsessid', 'jsessionid', 'sessionid', 'aspsessionid');
	
	public function setRequest($requestArray)
	{
		$this->request = $requestArray;
	}

	public function getRequest()
	{
	    return $this->request;
	}
	
	/**
	 * Returns URL of the page currently being tracked, or the file being downloaded, or the outlink being clicked
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
		$name = $this->truncate($name);
		$this->actionName = $name;
	}
	
	protected function setActionType($type)
	{
		$this->actionType = $type;
	}
	
	protected function setActionUrl($url)
	{
		$url = $this->excludeParametersFromUrl($url);
		$url = $this->truncate($url);
		$this->actionUrl = $url;
	}
	
	public function excludeParametersFromUrl($originalUrl)
	{
		$website = Piwik_Common::getCacheWebsiteAttributes( $this->idSite );

		$parsedUrl = @parse_url($originalUrl);
		if(empty($parsedUrl['query']))
		{
			return $originalUrl;
		}
		$parametersToExclude = array_merge($website['excluded_parameters'], $this->queryParametersToExclude);
		
		$parametersToExclude = array_map('strtolower', $parametersToExclude);
		$queryParameters = Piwik_Common::getArrayFromQueryString($parsedUrl['query']);
		
		$validQuery = '';
		foreach($queryParameters as $name => $value)
		{
			if(!in_array(strtolower($name), $parametersToExclude))
			{
				$validQuery .= $name.'='.$value.'&';
			}
		}
		$parsedUrl['query'] = substr($validQuery,0,-1);
		$url = Piwik_Common::getParseUrlReverse($parsedUrl);
		printDebug('Excluded parameters "'.implode(',',$website['excluded_parameters']).'" from URL.
					 Before was <br/><code>"'.$originalUrl.'"</code>, <br/>
					 After is <br/><code>"'.$url.'"</code>');
		return $url;
	}
	
	public function init()
	{
		$info = $this->extractUrlAndActionNameFromRequest();
		$this->setActionName($info['name']);
		$this->setActionType($info['type']);
		$this->setActionUrl($info['url']);
	}
	
	protected function truncate( $label )
	{
		$limit = Piwik_Tracker_Config::getInstance()->Tracker['page_maximum_length'];
		return substr($label, 0, $limit);
	}
	
	/**
	 * Loads the idaction of the current action name and the current action url.
	 * These idactions are used in the visitor logging table to link the visit information
	 * (entry action, exit action) to the actions.
	 * These idactions are also used in the table that links the visits and their actions.
	 * 
	 * The methods takes care of creating a new record(s) in the action table if the existing
	 * action name and action url doesn't exist yet.
	 * 
	 */
	function loadIdActionNameAndUrl()
	{
		if( !is_null($this->idActionUrl) && !is_null($this->idActionName) )
		{
			return;
		}
		$idAction = Piwik_Tracker::getDatabase()->fetchAll("/* SHARDING_ID_SITE = ".$this->idSite." */
							SELECT idaction, type 
							FROM ".Piwik_Common::prefixTable('log_action')
						."  WHERE "
						."		( hash = CRC32(?) AND name = ? AND type = ? ) "
						."	OR "
						."		( hash = CRC32(?) AND name = ? AND type = ? ) ",
						array($this->getActionName(), $this->getActionName(), $this->getActionNameType(),
							$this->getActionUrl(), $this->getActionUrl(), $this->getActionType())
					);

		if( $idAction !== false )
		{
			foreach($idAction as $row)
			{
				if( $row['type'] == Piwik_Tracker_Action_Interface::TYPE_ACTION_NAME )
				{
					$this->idActionName = $row['idaction'];
				}
				else
				{
					$this->idActionUrl = $row['idaction'];
				}
			}
		}

		$sql = "/* SHARDING_ID_SITE = ".$this->idSite." */ 
							INSERT INTO ". Piwik_Common::prefixTable('log_action'). 
							"( name, hash, type ) VALUES (?,CRC32(?),?)";

		if( is_null($this->idActionName) 
		    && !is_null($this->getActionNameType()) )
		{
			Piwik_Tracker::getDatabase()->query($sql,
				array($this->getActionName(), $this->getActionName(), $this->getActionNameType()));
			$this->idActionName = Piwik_Tracker::getDatabase()->lastInsertId();
		}

		if( is_null($this->idActionUrl) )
		{
			Piwik_Tracker::getDatabase()->query($sql,
				array($this->getActionUrl(), $this->getActionUrl(), $this->getActionType()));
			$this->idActionUrl = Piwik_Tracker::getDatabase()->lastInsertId();
		}
	}
	
	/**
	 * @param int $idSite
	 */
	function setIdSite($idSite)
	{
		$this->idSite = $idSite;
	}
	
	
	/**
	 * Records in the DB the association between the visit and this action.
	 * 
	 * @param int idVisit is the ID of the current visit in the DB table log_visit
	 * @param int idRefererAction is the ID of the last action done by the current visit. 
	 * @param int timeSpentRefererAction is the number of seconds since the last action was done. 
	 * 				It is directly related to idRefererAction.
	 */
	 public function record( $idVisit, $idRefererAction, $timeSpentRefererAction)
	 {
		$this->loadIdActionNameAndUrl();

		Piwik_Tracker::getDatabase()->query("/* SHARDING_ID_SITE = ".$this->idSite." */ INSERT INTO ".Piwik_Common::prefixTable('log_link_visit_action')
						." (idvisit, idaction_url, idaction_name, idaction_url_ref, time_spent_ref_action) VALUES (?,?,?,?,?)",
					array($idVisit, $this->getIdActionUrl(), $this->getIdActionName(), $idRefererAction, $timeSpentRefererAction)
					);
		
		$this->idLinkVisitAction = Piwik_Tracker::getDatabase()->lastInsertId(); 
		
		$info = array( 
			'idSite' => $this->idSite, 
			'idLinkVisitAction' => $this->idLinkVisitAction, 
			'idVisit' => $idVisit, 
			'idRefererAction' => $idRefererAction, 
			'timeSpentRefererAction' => $timeSpentRefererAction, 
		); 
		printDebug($info);

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

		$actionName = Piwik_Common::getRequestVar( 'action_name', '', 'string', $this->request);

		// defaults to page view 
		if(empty($actionType))
		{
			$actionType = self::TYPE_ACTION_URL;
			$url = Piwik_Common::getRequestVar( 'url', '', 'string', $this->request);

			// get the delimiter, by default '/'; BC, we read the old action_category_delimiter first (see #1067) 
			$actionCategoryDelimiter = isset(Piwik_Tracker_Config::getInstance()->General['action_category_delimiter'])
										? Piwik_Tracker_Config::getInstance()->General['action_category_delimiter']
										: Piwik_Tracker_Config::getInstance()->General['action_url_category_delimiter'];
			
			// create an array of the categories delimited by the delimiter
			$split = explode($actionCategoryDelimiter, $actionName);
			
			// trim every category
			$split = array_map('trim', $split);
			
			// remove empty categories
			$split = array_filter($split, 'strlen');
			
			// rebuild the name from the array of cleaned categories
			$actionName = implode($actionCategoryDelimiter, $split);
		}
		
		$url = trim($url);
		$url = str_replace(array("\n", "\r"), "", $url);

		$actionName = trim($actionName);
		$actionName = str_replace(array("\n", "\r"), "", $actionName);

		return array(
			'name' => empty($actionName) ? '' : $actionName,
			'type' => $actionType,
			'url'  => $url,
		);
	}
}
