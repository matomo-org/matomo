<?php

class Piwik_LogStats_Action
{
	
	 /*
	  * About the Action concept:
	  * 
	  * - An action is defined by a name.
	  * - The name can be specified in the JS Code in the variable 'action_name'
	  * - Handling UTF8 in the action name
	  * PLUGIN_IDEA - An action is associated to URLs and link to the URL from the interface
	  * PLUGIN_IDEA - An action hit by a visitor is associated to the HTML title of the page that triggered the action
	  * 
	  * + If the name is not specified, we use the URL(path+query) to build a default name.
	  *   For example for "http://piwik.org/test/my_page/test.html" 
	  *   the name would be "test/my_page/test.html"
	  * 
	  * We make sure it is clean and displayable.
	  * If the name is empty we set it to a default name.
	  * 
	  * TODO UTF8 handling to test
	  * 
	  * Specifications
	  *  
	  * - External file tracking
	  * 
	  *    * MANUAL Download tracking 
	  *      download = http://piwik.org/hellokity.zip
	  * 	(name = dir1/file alias name)
	  *
	  *    * AUTOMATIC Download tracking for a known list of file extensions. 
	  *    Make a hit to the piwik.php with the parameter: 
	  *      download = http://piwik.org/hellokity.zip
	  *  
	  *   When 'name' is not specified, 
	  * 	if AUTOMATIC and if anchor not empty => name = link title anchor
	  * 	else name = path+query of the URL
	  *   Ex: myfiles/beta.zip
	  *
	  * - External link tracking
	  * 
	  *    * MANUAL External link tracking
	  * 	 outlink = http://amazon.org/test
	  * 	(name = the big partners / amazon)
	  * 
	  *    * AUTOMATIC External link tracking
	  *      When a link is not detected as being part of the same website 
	  *     AND when the url extension is not detected as being a file download
	  * 	 outlink = http://amazon.org/test
	  * 
	  *  When 'name' is not specified, 
	  * 	if AUTOMATIC and if anchor not empty => name = link title anchor
	  * 	else name = URL
	  *   Ex: http://amazon.org/test
	  */
	private $actionName;
	private $url;
	private $defaultActionName;
	private $nameDownloadOutlink;
	
	const TYPE_ACTION   = 1;
	const TYPE_DOWNLOAD = 3;
	const TYPE_OUTLINK  = 2;
	
	function __construct( $db )
	{
		$this->actionName = Piwik_Common::getRequestVar( 'action_name', '', 'string');
		
		$downloadVariableName = Piwik_LogStats_Config::getInstance()->LogStats['download_url_var_name'];
		$this->downloadUrl = Piwik_Common::getRequestVar( $downloadVariableName, '', 'string');
		
		$outlinkVariableName = Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'];
		$this->outlinkUrl = Piwik_Common::getRequestVar( $outlinkVariableName, '', 'string');
		
		$nameVariableName = Piwik_LogStats_Config::getInstance()->LogStats['download_outlink_name_var'];
		$this->nameDownloadOutlink = Piwik_Common::getRequestVar( $nameVariableName, '', 'string');
		
		$this->url = Piwik_Common::getRequestVar( 'url', '', 'string');
		$this->db = $db;
		$this->defaultActionName = Piwik_LogStats_Config::getInstance()->LogStats['default_action_name'];
	}
	
	private function generateInfo()
	{
		if(!empty($this->downloadUrl))
		{
			$this->actionType = self::TYPE_DOWNLOAD;
			$url = $this->downloadUrl;
			$actionName = $this->nameDownloadOutlink;
		}
		elseif(!empty($this->outlinkUrl))
		{
			$this->actionType = self::TYPE_OUTLINK;
			$url = $this->outlinkUrl;
			$actionName = $this->nameDownloadOutlink;
			if( empty($actionName) )
			{
				$actionName = $url;
			}
		}
		else
		{
			$this->actionType = self::TYPE_ACTION;
			$url = $this->url;
			$actionName = $this->actionName;
		}		
		
		// the ActionName wasn't specified
		if( empty($actionName) )
		{
			$actionName = Piwik_Common::getPathAndQueryFromUrl($url);
		}
		
		// clean the name
		$actionName = str_replace(array("\n", "\r"), '', $actionName);
		
		if(empty($actionName))
		{
			$actionName = $this->defaultActionName;
		}
		
		$this->finalActionName = $actionName;
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
	 * @return int Id action
	 */
	function getActionId()
	{
		$this->loadActionId();
		return $this->idAction;
	}
	
	/**
	 * @see getActionId()
	 */
	private function loadActionId()
	{		
		$this->generateInfo();
		
		$name = $this->finalActionName;
		$type = $this->actionType;
		
		$idAction = $this->db->fetch("	SELECT idaction 
							FROM ".$this->db->prefixTable('log_action')
						."  WHERE name = ? AND type = ?", array($name, $type) );
		
		// the action name has not been found, create it
		if($idAction === false)
		{
			$this->db->query("INSERT INTO ". $this->db->prefixTable('log_action'). "( name, type ) 
								VALUES (?,?)",array($name,$type) );
			$idAction = $this->db->lastInsertId();
		}
		else
		{
			$idAction = $idAction['idaction'];
		}
		
		$this->idAction = $idAction;
	}
	
	/**
	 * Records in the DB the association between the visit and this action.
	 */
	 public function record( $idVisit, $idRefererAction, $timeSpentRefererAction)
	 {
	 	$this->db->query("INSERT INTO ".$this->db->prefixTable('log_link_visit_action')
						." (idvisit, idaction, idaction_ref, time_spent_ref_action) VALUES (?,?,?,?)",
					array($idVisit, $this->idAction, $idRefererAction, $timeSpentRefererAction)
					);
	 }
}

?>
