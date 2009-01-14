<?php

class Piwik_Tracker_GoalManager 
{
	/**
	 * @var Piwik_Cookie 
	 */
	protected $cookie = null;
	/**
	 * @var Piwik_Tracker_Action
	 */
	protected $action = null;
	protected $matchedGoals = array();
	protected $idsite = null;
	
	function __construct($action)
	{
		$this->action = $action;
		
		
	}

	function setCookie($cookie)
	{
		$this->cookie = $cookie;
	}

	static public function getGoalDefinitions( $idSite )
	{
		$websiteAttributes = Piwik_Common::getCacheWebsiteAttributes( $idSite );
		$goals = $websiteAttributes['goals'];
		return $goals;
	}
	
	static public function getGoalDefinition( $idSite, $idGoal )
	{
		$goals = self::getGoalDefinitions( $idSite );
		foreach($goals as $goal)
		{
			if($goal['idgoal'] == $idGoal)
			{
				return $goal;
			}
		}
		throw new Exception("The goal id = $idGoal couldn't be found.");
	}
	
	static public function getGoalIds( $idSite )
	{
		$goals = self::getGoalDefinitions( $idSite );
		$goalIds = array();
		foreach($goals as $goal)
		{
			$goalIds[] = $goal['idgoal'];
		}
		return $goalIds;
	}
	
	//TODO does this code work for manually triggered goals, with custom revenue? 
	function detectGoals($idSite)
	{
		if(!Piwik_PluginsManager::getInstance()->isPluginActivated('Goals'))
		{
			return false;
		}
		$url = $this->action->getUrl();
		$actionType = $this->action->getActionType();
		$goals = $this->getGoalDefinitions($idSite);
		foreach($goals as $goal)
		{
			$attribute = $goal['match_attribute'];
			// if the attribute to match is not the type of the current action
			if(		($actionType == Piwik_Tracker_Action::TYPE_ACTION && $attribute != 'url')
				||	($actionType == Piwik_Tracker_Action::TYPE_DOWNLOAD && $attribute != 'file')
				||	($actionType == Piwik_Tracker_Action::TYPE_OUTLINK && $attribute != 'external_website')
				)
			{
				continue;
			}
			
			$pattern_type = $goal['pattern_type'];
			
			switch($pattern_type)
			{
				case 'regex':
					$pattern = '/' . $goal['pattern'] . '/';
					if(!$goal['case_sensitive'])
					{
						$pattern .= 'i';
					}
					$match = (preg_match($pattern, $url) == 1);
					break;
				case 'contains':
					if($goal['case_sensitive'])
					{
						$matched = strpos($url, $goal['pattern']);
					}
					else
					{
						$matched = stripos($url, $goal['pattern']);
					}
					$match = ($matched !== false);
					break;
				case 'exact':
					if($goal['case_sensitive'])
					{
						$matched = strcmp($goal['pattern'], $url);
					}
					else
					{
						$matched = strcasecmp($goal['pattern'], $url);
					}
					$match = ($matched == 0);
					break;
				default:
					throw new Exception("Pattern type $pattern_type not valid.");
					break;
			}
			if($match)
			{
				$this->matchedGoals[] = $goal;
			}
		}
//		var_dump($this->matchedGoals);exit;
		return count($this->matchedGoals) > 0;
	}
	
	function recordGoals($visitorInformation)
	{
		$location_country = isset($visitorInformation['location_country']) ? $visitorInformation['location_country'] : Piwik_Common::getCountry(Piwik_Common::getBrowserLanguage());
		$location_continent = isset($visitorInformation['location_continent']) ? $visitorInformation['location_continent'] : Piwik_Common::getContinent($location_country);
		
		$goal = array(
			'idvisit' 			=> $visitorInformation['idvisit'],
			'idsite' 			=> $visitorInformation['idsite'],
			'visitor_idcookie' 	=> $visitorInformation['visitor_idcookie'],
			'server_time' 		=> Piwik_Tracker::getDatetimeFromTimestamp($visitorInformation['visit_last_action_time']),
			'visit_server_date' => $visitorInformation['visit_server_date'],
			'idaction' 			=> $this->action->getIdAction(),
			'idlink_va' 		=> $this->action->getIdLinkVisitAction(),
			'location_country'  => $location_country,
			'location_continent'=> $location_continent,
			'url' 				=> $this->action->getUrl(),
			'visitor_returning' => $this->cookie->get( Piwik_Tracker::COOKIE_INDEX_VISITOR_RETURNING ),
		);

		$referer_idvisit = $this->cookie->get(  Piwik_Tracker::COOKIE_INDEX_REFERER_ID_VISIT );
		if($referer_idvisit !== false)
		{
			$goal += array(
				'referer_idvisit' 			=> $referer_idvisit,
				'referer_visit_server_date' => date("Y-m-d", $this->cookie->get( Piwik_Tracker::COOKIE_INDEX_REFERER_TIMESTAMP )),
				'referer_type' 				=> htmlspecialchars_decode($this->cookie->get( Piwik_Tracker::COOKIE_INDEX_REFERER_TYPE )),
				'referer_name' 				=> htmlspecialchars_decode($this->cookie->get(  Piwik_Tracker::COOKIE_INDEX_REFERER_NAME )),
				'referer_keyword' 			=> htmlspecialchars_decode($this->cookie->get(  Piwik_Tracker::COOKIE_INDEX_REFERER_KEYWORD )),
			);
		}

		foreach($this->matchedGoals as $matchedGoal)
		{
			printDebug("- Goal ".$matchedGoal['idgoal'] ." matched. Recording...");
			$newGoal = $goal;
			$newGoal['idgoal'] = $matchedGoal['idgoal'];
			$newGoal['revenue'] = $matchedGoal['revenue'];
			printDebug($newGoal);
			
			$fields = implode(", ", array_keys($newGoal));
			$bindFields = substr(str_repeat( "?,",count($newGoal)),0,-1);
			
			try {
				Piwik_Tracker::getDatabase()->query(
					"INSERT INTO " . Piwik_Common::prefixTable('log_conversion') . "	($fields) 
					VALUES ($bindFields) ", array_values($newGoal) 
				);
			} catch( Exception $e) {
				if(strpos($e->getMessage(), '1062') !== false)
				{
					// integrity violation when same visit converts to the same goal twice
					printDebug("--> Goal already recorded for this (idvisit, idgoal)");
				}
				else
				{
					throw $e;
				}
			}
			//$idlog_goal = Piwik_Tracker::getDatabase()->lastInsertId();
		}
	}
}
