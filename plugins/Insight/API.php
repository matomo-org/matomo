<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: API.php 4791 2011-05-23 19:05:52Z JulienM $
 *
 * @category Piwik_Plugins
 * @package Piwik_Insight
 */

class Piwik_Insight_API
{
	
	private $mainDomain = array();
	private $aliasDomains = array();
	
	private static $instance = null;
	
	/** @return Piwik_Insight_API */
	public static function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	/** Proxy API call to get screen resolutions */
	public function getResolutions($idSite, $period, $date)
	{
		$this->authenticate($idSite);
		return Piwik_UserSettings_API::getInstance()->getResolution($idSite, $period, $date);
	}
	
	/** Get translation strings */
	public function getTranslations($idSite)
	{
		$this->authenticate($idSite);
		
		$translations = array(
			// messages for screen resolution overlay
			/*'ScreenHeight' => 'Insight_ScreenHeight',
			'ExactHeight' => 'Insight_ExactHeight',
			'AtLeastHeight' => 'Insight_AtLeastHeight',
			'ResolutionOverlay' => 'Insight_ResolutionOverlay'*/
		);
		
		return array_map('Piwik_Translate', $translations);
	}
	
	/**
	 * Get following pages of a url.
	 * This is done on the logs - not the archives!
	 */
	public function getFollowingPages($idSite, $period, $date)
	{
		$this->authenticate($idSite);
		
		// prepare url of current page
		$url = Piwik_Common::getRequestVar('url', false);
		if (!$url)
		{
			return array();
		}
		$url = $this->normalizeUrl($idSite, $url, true, true);
		
		// put together all possible url aliases
		$this->loadDomains($idSite);
		$normalizedDomains = $this->aliasDomains[$idSite];
		$normalizedDomains[] = $this->mainDomain[$idSite];
		
		// get all possible idactions
		$type = Piwik_Tracker_Action_Interface::TYPE_ACTION_URL;
		$where = array();
		$bind = array();
		foreach ($normalizedDomains as $domain)
		{
			$where[] = '( hash = CRC32(?) AND name = ? AND type = ? )';
			$bind[] = $domain.$url;
			$bind[] = $domain.$url;
			$bind[] = $type;
		}
		
		$sql = '
			SELECT idaction
			FROM '.Piwik_Common::prefixTable('log_action').'
			WHERE '.implode(' OR ', $where).'
		';
		
		$result = Piwik_FetchAll($sql, $bind);
		if (count($result) == 0)
		{
			return array();
		}
		
		$idaction = array();
		foreach ($result as $row)
		{
			$idaction[] = intval($row['idaction']);
		}
		
		// prepare the date range
		$site = new Piwik_Site($idSite);
		$period = Piwik_Period::advancedFactory($period, $date);
		$dateStartLocalTimezone = $period->getDateStart();
		$dateEndLocalTimezone = $period->getDateEnd();
		$dateStartUTC = $dateStartLocalTimezone->setTimezone($site->getTimezone());
		$dateEndUTC = $dateEndLocalTimezone->setTimezone($site->getTimezone());
		$dateBegin = $dateStartUTC->getDateStartUTC();
		$dateEnd = $dateEndUTC->getDateEndUTC();
		
		// now, use the idactions to query the logs
		$sql = '
			SELECT CONCAT( "http://", action.name ) AS url, COUNT(link.idlink_va) AS clicks
			FROM '.Piwik_Common::prefixTable('log_link_visit_action').' AS link
			LEFT JOIN '.Piwik_Common::prefixTable('log_action').' AS action
				ON link.idaction_url = action.idaction
			WHERE link.idaction_url_ref IN ('.implode(', ', $idaction).')
				AND server_time BETWEEN "'.$dateBegin.'" AND "'.$dateEnd.'"
			GROUP BY link.idaction_url
		';
		
		$pages = Piwik_FetchAll($sql);
		
		// add click rates (percentages)
		$clicks = 0;
		foreach ($pages as &$page)
		{
			$clicks += $page['clicks'];
		}
		
		foreach ($pages as &$page)
		{
			$page['clickRate'] = round($page['clicks'] / $clicks * 100, 5);
		}
		
		return $pages;
	}
	
	/** Do cookie authentication. This way, the token can remain secret. */
	private function authenticate($idSite)
	{
		Piwik_PostEvent('FrontController.initAuthenticationObject', $notification = null,
				$allowCookieAuthentication = true);
				
		$auth = Zend_Registry::get('auth');
		$success = Zend_Registry::get('access')->reloadAccess($auth);
		
		if (!$success) {
			throw new Exception('Authentication failed');
		}
		
		Piwik::checkUserHasViewAccess($idSite);
	}
	
	/** Load normalized domain names */
	private function loadDomains($idSite)
	{
		if (!isset($this->mainDomain[$idSite]))
		{
			$urls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idSite);
			
			$this->mainDomain[$idSite] = $this->normalizeUrl($idSite, $urls[0], false);
			if (substr($this->mainDomain[$idSite], -1) != '/')
			{
				$this->mainDomain[$idSite] .= '/';
			}
			
			$this->aliasDomains[$idSite] = array();
			for ($i = 1; $i < count($urls); $i++)
			{
				$url = $this->normalizeUrl($idSite, $urls[$i], false);
				if (substr($url, -1) != '/')
				{
					$url .= '/';
				}
				$this->aliasDomains[$idSite][] = $url;
			}
		}
	}
	
	/** Normalize URL for comparison */
	private function normalizeUrl($idSite, $url, $replaceAliases=true, $removeDomain=false)
	{
		// remove protocol and www
		$url = preg_replace(';^http(?:s)?://(?:www\.)?;i', '', $url);
		
		// replace domain aliases with main domain
		if ($replaceAliases)
		{
			$this->loadDomains($idSite);
			
			foreach ($this->aliasDomains[$idSite] as $alias)
			{
				if (substr($url, 0, strlen($alias)) == $alias)
				{
					$url = substr($url, strlen($alias));
					if ($removeDomain)
					{
						return $url;
					}
					$url = $this->mainDomain[$idSite] . $url;
					break;
				}
			}
			
			if ($removeDomain)
			{
				$mainDomain = $this->mainDomain[$idSite];
				if (substr($url, 0, strlen($mainDomain)) == $mainDomain)
				{
					$url = substr($url, strlen($mainDomain));
				}
			}
		}
		
		return $url;
	}

}
