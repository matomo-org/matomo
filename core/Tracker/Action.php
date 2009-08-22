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
	const TYPE_ACTION   = 1;
	const TYPE_OUTLINK  = 2;
	const TYPE_DOWNLOAD = 3;
	
	public function setRequest($requestArray);
	public function setIdSite( $idSite );
	public function init();
	public function getActionUrl();
	public function getActionName();
	public function getActionType();
	public function record( $idVisit, $idRefererAction, $timeSpentRefererAction );
	public function getIdAction();
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
 * - If the name is not specified, we use the URL(path+query) to build a default name.
 *    For example for "http://piwik.org/test/my_page/test.html" 
 *    the name would be "test/my_page/test.html"
 * - If the name is empty we set it to default_action_name found in global.ini.php
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
	private $idAction = null;

	private $actionName;
	private $actionType;
	private $url;
	
	protected function getDefaultActionName()
	{
		return Piwik_Tracker_Config::getInstance()->Tracker['default_action_name'];
	}
	
	public function setRequest($requestArray)
	{
		$this->request = $requestArray;
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
	
	protected function setActionName($name)
	{
		$this->actionName = $name;
	}
	protected function setActionType($type)
	{
		$this->actionType = $type;
	}
	protected function setActionUrl($url)
	{
		$this->actionUrl = $url;
	}
	
	public function init()
	{
		$info = $this->extractUrlAndActionNameFromRequest();
		$this->setActionName($info['name']);
		$this->setActionType($info['type']);
		$this->setActionUrl($info['url']);
	}
	
	/**
	 * Returns the idaction of the current action name.
	 * This idaction is used in the visitor logging table to link the visit information 
	 * (entry action, exit action) to the actions.
	 * This idaction is also used in the table that links the visits and their actions.
	 * 
	 * The methods takes care of creating a new record in the action table if the existing 
	 * action name doesn't exist yet.
	 * 
	 * @return int Id action that is associated to this action name in the Actions table lookup
	 */
	function getIdAction()
	{
		if(!is_null($this->idAction))
		{
			return $this->idAction;
		}
		$idAction = Piwik_Tracker::getDatabase()->fetch("/* SHARDING_ID_SITE = ".$this->idSite." */ 	SELECT idaction 
							FROM ".Piwik_Common::prefixTable('log_action')
						."  WHERE name = ? AND type = ?", 
						array($this->getActionName(), $this->getActionType()) 
					);
		
		// the action name has not been found, create it
		if($idAction === false)
		{
			Piwik_Tracker::getDatabase()->query("/* SHARDING_ID_SITE = ".$this->idSite." */
							INSERT INTO ". Piwik_Common::prefixTable('log_action'). "( name, type ) 
							VALUES (?,?)",
						array($this->getActionName(),$this->getActionType())
					);
			$idAction = Piwik_Tracker::getDatabase()->lastInsertId();
		}
		else
		{
			$idAction = $idAction['idaction'];
		}
		$this->idAction = $idAction;
		return $this->idAction;
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
	 	Piwik_Tracker::getDatabase()->query("/* SHARDING_ID_SITE = ".$this->idSite." */ INSERT INTO ".Piwik_Common::prefixTable('log_link_visit_action')
						." (idvisit, idaction, idaction_ref, time_spent_ref_action) VALUES (?,?,?,?)",
					array($idVisit, $this->getIdAction(), $idRefererAction, $timeSpentRefererAction)
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
	 */
	protected function extractUrlAndActionNameFromRequest()
	{
		// download?
		$downloadVariableName = Piwik_Tracker_Config::getInstance()->Tracker['download_url_var_name'];
		$downloadUrl = Piwik_Common::getRequestVar( $downloadVariableName, '', 'string', $this->request);
		if(!empty($downloadUrl))
		{
			$actionType = self::TYPE_DOWNLOAD;
			$url = $downloadUrl;
		}
		
		// outlink?
		if(empty($actionType))
		{
			$outlinkVariableName = Piwik_Tracker_Config::getInstance()->Tracker['outlink_url_var_name'];
			$outlinkUrl = Piwik_Common::getRequestVar( $outlinkVariableName, '', 'string', $this->request);
			if(!empty($outlinkUrl))
			{
				$actionType = self::TYPE_OUTLINK;
				$url = $outlinkUrl;
			}
		}
		
		// defaults to page view 
		if(empty($actionType))
		{
			$actionType = self::TYPE_ACTION;
			$url = Piwik_Common::getRequestVar( 'url', '', 'string', $this->request);
			$actionName = Piwik_Common::getRequestVar( 'action_name', '', 'string', $this->request);
			if( empty($actionName) )
			{
				$cleanedUrl = str_replace(array("\n", "\r", "\t"), "", $url);
				$actionName = Piwik_Common::getPathAndQueryFromUrl($cleanedUrl);
				// in case the $actionName is empty or ending with a slash, 
				// we append the defaultActionName: a/b/ becomes a/b/index 
				if(empty($actionName)
					|| substr($actionName, -1) == '/')
				{
					$actionName .= $this->getDefaultActionName();
				}
			}
			
			// get the delimiter, by default '/'
			$actionCategoryDelimiter = Piwik_Tracker_Config::getInstance()->General['action_category_delimiter'];
			
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
		if(empty($actionName))
		{
			$actionName = $url;
		}

		return array(
			'name' => $actionName,
			'type' => $actionType,
			'url'  => $url,
		);
	}
}
