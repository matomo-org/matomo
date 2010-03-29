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
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
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
	protected $convertedGoals = array();
	protected $idsite = null;

	function setCookie($cookie)
	{
		$this->cookie = $cookie;
	}

	static public function getGoalDefinitions( $idSite )
	{
		$websiteAttributes = Piwik_Common::getCacheWebsiteAttributes( $idSite );
		if(isset($websiteAttributes['goals']))
		{
			return $websiteAttributes['goals'];
		}
		return array();
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

	private function isGoalPluginEnabled()
	{
		return Piwik_PluginsManager::getInstance()->isPluginActivated('Goals');
	}

	function detectGoalsMatchingUrl($idSite, $action)
	{
		if(!$this->isGoalPluginEnabled())
		{
			return false;
		}
		$sanitizedUrl = $action->getActionUrl();
		$url = htmlspecialchars_decode($sanitizedUrl);
		$actionType = $action->getActionType();
		$goals = $this->getGoalDefinitions($idSite);
		foreach($goals as $goal)
		{
			$attribute = $goal['match_attribute'];
			// if the attribute to match is not the type of the current action
			if(		($actionType == Piwik_Tracker_Action::TYPE_ACTION_URL && $attribute != 'url')
				||	($actionType == Piwik_Tracker_Action::TYPE_DOWNLOAD && $attribute != 'file')
				||	($actionType == Piwik_Tracker_Action::TYPE_OUTLINK && $attribute != 'external_website')
				||	($attribute == 'manually')
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
				$goal['url'] = $sanitizedUrl;
				$this->convertedGoals[] = $goal;
			}
		}
//		var_dump($this->convertedGoals);exit;
		return count($this->convertedGoals) > 0;
	}

	function detectGoalId($idSite, $idGoal, $request)
	{
		if(!$this->isGoalPluginEnabled())
		{
			return false;
		}
		$goals = $this->getGoalDefinitions($idSite);
		if(!isset($goals[$idGoal]))
		{
			return false;
		}
		$goal = $goals[$idGoal];
		$goal['url'] = Piwik_Common::getRequestVar( 'url', '', 'string', $request);
		$goal['revenue'] = Piwik_Common::getRequestVar('revenue', $goal['revenue'], 'float', $request);
		$this->convertedGoals[] = $goal;
		return true;
	}

	function recordGoals($visitorInformation, $action)
	{
		$location_country = isset($visitorInformation['location_country']) ? $visitorInformation['location_country'] : Piwik_Common::getCountry( Piwik_Common::getBrowserLanguage(), $enableLanguageToCountryGuess = Piwik_Tracker_Config::getInstance()->Tracker['enable_language_to_country_guess'], $visitorInformation['location_ip'] );
		$location_continent = isset($visitorInformation['location_continent']) ? $visitorInformation['location_continent'] : Piwik_Common::getContinent($location_country);

		$goal = array(
			'idvisit' 			=> $visitorInformation['idvisit'],
			'idsite' 			=> $visitorInformation['idsite'],
			'visitor_idcookie' 	=> $visitorInformation['visitor_idcookie'],
			'server_time' 		=> Piwik_Tracker::getDatetimeFromTimestamp($visitorInformation['visit_last_action_time']),
			'location_country'  => $location_country,
			'location_continent'=> $location_continent,
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

		foreach($this->convertedGoals as $convertedGoal)
		{
			printDebug("- Goal ".$convertedGoal['idgoal'] ." matched. Recording...");
			$newGoal = $goal;
			$newGoal['idgoal'] = $convertedGoal['idgoal'];
			$newGoal['url'] = $convertedGoal['url'];
			$newGoal['revenue'] = $convertedGoal['revenue'];
			if(!is_null($action))
			{
				$newGoal['idaction_url'] = $action->getIdActionUrl();
				$newGoal['idlink_va'] = $action->getIdLinkVisitAction();
			}
			printDebug($newGoal);

			$fields = implode(", ", array_keys($newGoal));
			$bindFields = substr(str_repeat( "?,",count($newGoal)),0,-1);

			try {
				Piwik_Tracker::getDatabase()->query(
					"INSERT INTO " . Piwik_Common::prefixTable('log_conversion') . "	($fields)
					VALUES ($bindFields) ", array_values($newGoal)
				);
			} catch( Exception $e) {
				if(Piwik_Tracker::getDatabase()->isErrNo($e, '1062'))
				{
					// integrity violation when same visit converts to the same goal twice
					printDebug("--&gt; Goal already recorded for this (idvisit, idgoal)");
				}
				else
				{
					throw $e;
				}
			}
		}
	}
}
