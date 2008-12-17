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

	//TODO goalid should be incrementing on a website basis
	// load goal definitions from file
	static public function getGoalDefinitions()
	{
		return array(
			0 => array(	'id' => 1,
						'name' => 'Downloads',
						'default_revenue' => '3',
						'pattern' => '/e/' 
			),
			1 => array(	'id' => 5,
						'name' => 'hosted signups',
						'default_revenue' => false,
						'pattern' => '//' 
			),
		);
	}
	
	static public function getGoalDefinition( $idGoal )
	{
		$goals = self::getGoalDefinitions();
		foreach($goals as $goal)
		{
			if($goal['id'] == $idGoal)
			{
				return $goal;
			}
		}
		throw new Exception("The goal id = $idGoal couldn't be found.");
	}
	
	static public function getGoalIds()
	{
		$goals = self::getGoalDefinitions();
		$goalIds = array();
		foreach($goals as $goal)
		{
			$goalIds[] = $goal['id'];
		}
		return $goalIds;
	}
	
	//TODO does this code work for manually triggered goals, with custom revenue? 
	function detectGoals($idSite)
	{
		$url = $this->action->getUrl();
		$goals = $this->getGoalDefinitions($idSite);
		foreach($goals as $goal)
		{
			$match = preg_match($goal['pattern'], $url);
			if($match === 1)
			{
				$this->matchedGoals[] = $goal;
			}
		}
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
			printDebug("- Goal ".$matchedGoal['id'] ." matched. Recording...");
			$newGoal = $goal;
			$newGoal['idgoal'] = $matchedGoal['id'];
			$newGoal['revenue'] = $matchedGoal['default_revenue'];
			printDebug($newGoal);
			
			$fields = implode(", ", array_keys($newGoal));
			$bindFields = substr(str_repeat( "?,",count($newGoal)),0,-1);
			
			try {
				Piwik_Tracker::getDatabase()->query(
					"INSERT INTO " . Piwik_Tracker::getDatabase()->prefixTable('log_conversion') . "	($fields) 
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