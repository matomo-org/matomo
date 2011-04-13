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
 * @package Piwik
 * @subpackage Piwik_Tracker
 */
class Piwik_Tracker_GoalManager 
{
	/**
	 * @var Piwik_Tracker_Action
	 */
	protected $action = null;
	protected $convertedGoals = array();

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
		throw new Exception('Goal not found');
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

	/**
	 * @param int $idSite
	 * @param Piwik_Tracker_Action $action
	 */
	function detectGoalsMatchingUrl($idSite, $action)
	{
		if(!$this->isGoalPluginEnabled())
		{
			return false;
		}
		$sanitizedUrl = $action->getActionUrl();
		$decodedUrl = htmlspecialchars_decode($sanitizedUrl);
		
		$actionType = $action->getActionType();
		$goals = $this->getGoalDefinitions($idSite);
		foreach($goals as $goal)
		{
			$attribute = $goal['match_attribute'];
			// if the attribute to match is not the type of the current action
			if(		($actionType == Piwik_Tracker_Action::TYPE_ACTION_URL && $attribute != 'url' && $attribute != 'title')
				||	($actionType == Piwik_Tracker_Action::TYPE_DOWNLOAD && $attribute != 'file')
				||	($actionType == Piwik_Tracker_Action::TYPE_OUTLINK && $attribute != 'external_website')
				||	($attribute == 'manually')
				)
			{
				continue;
			}
			
			$url = $decodedUrl;
			// Matching on Page Title
			if($attribute == 'title')
			{
				$url = $action->getActionName();
			}
			$pattern_type = $goal['pattern_type'];

			switch($pattern_type)
			{
				case 'regex':
					$pattern = $goal['pattern'];
					if(strpos($pattern, '/') !== false 
						&& strpos($pattern, '\\/') === false)
					{
						$pattern = str_replace('/', '\\/', $pattern);
					}
					$pattern = '/' . $pattern . '/'; 
					if(!$goal['case_sensitive'])
					{
						$pattern .= 'i';
					}
					$match = (@preg_match($pattern, $url) == 1);
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
					throw new Exception(Piwik_TranslateException('General_ExceptionInvalidGoalPattern', array($pattern_type)));
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
		
		$url = Piwik_Common::getRequestVar( 'url', '', 'string', $request);
		$goal['url'] = Piwik_Tracker_Action::excludeQueryParametersFromUrl($url, $idSite);
		$goal['revenue'] = Piwik_Common::getRequestVar('revenue', $goal['revenue'], 'float', $request);
		$this->convertedGoals[] = $goal;
		return true;
	}

	function recordGoals($idSite, $visitorInformation, $visitCustomVariables, $action, $referrerTimestamp, $referrerUrl, $referrerCampaignName, $referrerCampaignKeyword)
	{
		$location_country = isset($visitorInformation['location_country']) 
							? $visitorInformation['location_country'] 
							: Piwik_Common::getCountry( 
										Piwik_Common::getBrowserLanguage(), 
										$enableLanguageToCountryGuess = Piwik_Tracker_Config::getInstance()->Tracker['enable_language_to_country_guess'], $visitorInformation['location_ip'] 
							);
							
		$location_continent = isset($visitorInformation['location_continent']) 
								? $visitorInformation['location_continent'] 
								: Piwik_Common::getContinent($location_country);

		$goal = array(
			'idvisit' 			=> $visitorInformation['idvisit'],
			'idsite' 			=> $idSite,
			'idvisitor' 		=> $visitorInformation['idvisitor'],
			'server_time' 		=> Piwik_Tracker::getDatetimeFromTimestamp($visitorInformation['visit_last_action_time']),
			'location_country'  => $location_country,
			'location_continent'=> $location_continent,
			'visitor_returning' => $visitorInformation['visitor_returning'],
			'visitor_days_since_first' => $visitorInformation['visitor_days_since_first'],
			'visitor_count_visits' => $visitorInformation['visitor_count_visits'],
		
		);

		// Attributing the correct Referrer to this conversion. 
		// Priority order is as follows:
		// 1) Campaign name/kwd parsed in the JS
		// 2) Referrer URL stored in the _ref cookie
		// 3) If no info from the cookie, attribute to the current visit referrer
		
		// 3) Default values: current referrer
        $type = $visitorInformation['referer_type'];
        $name = $visitorInformation['referer_name'];
        $keyword = $visitorInformation['referer_keyword'];
        $time = $visitorInformation['visit_first_action_time'];
        
        // 1) Campaigns from 1st party cookie
		if(!empty($referrerCampaignName))
		{
			$type = Piwik_Common::REFERER_TYPE_CAMPAIGN;
			$name = $referrerCampaignName;
			$keyword = $referrerCampaignKeyword;
			$time = $referrerTimestamp;
		}
		// 2) Referrer URL parsing
		elseif(!empty($referrerUrl))
		{
			$referrer = new Piwik_Tracker_Visit_Referer();  
            $referrer = $referrer->getRefererInformation($referrerUrl, $currentUrl = '', $idSite);
            
            // if the parsed referer is interesting enough, ie. website or search engine 
            if(in_array($referrer['referer_type'], array(Piwik_Common::REFERER_TYPE_SEARCH_ENGINE, Piwik_Common::REFERER_TYPE_WEBSITE)))
            {
            	$type = $referrer['referer_type'];
            	$name = $referrer['referer_name'];
            	$keyword = $referrer['referer_keyword'];
				$time = $referrerTimestamp;
            }
		}
		$goal += array(
			'referer_type' 				=> $type,
			'referer_name' 				=> $name,
			'referer_keyword' 			=> $keyword,
			// this field is currently unused
			'referer_visit_server_date' => date("Y-m-d", $time),
		);

		$goal += $visitCustomVariables;
		
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

			// If multiple Goal conversions per visit, set a cache buster 
			$newGoal['buster'] = $convertedGoal['allow_multiple'] == 0 
										? '0' 
										: $visitorInformation['visit_last_action_time'];
			$newGoalDebug = $newGoal;
			$newGoalDebug['idvisitor'] = bin2hex($newGoalDebug['idvisitor']);
			printDebug($newGoalDebug);

			$fields = implode(", ", array_keys($newGoal));
			$bindFields = substr(str_repeat( "?,",count($newGoal)),0,-1);
			
			$sql = "INSERT IGNORE INTO " . Piwik_Common::prefixTable('log_conversion') . "	
					($fields) VALUES ($bindFields) ";
			$bind = array_values($newGoal);
			Piwik_Tracker::getDatabase()->query($sql, $bind);
		}
	}
}
