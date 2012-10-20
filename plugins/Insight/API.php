<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Insight
 */

class Piwik_Insight_API
{
	
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
	
	/** Get translation strings */
	public function getTranslations($idSite)
	{
		$this->authenticate($idSite);
		
		$translations = array();
		
		return array_map('Piwik_Translate', $translations);
	}
	
	/**
	 * Get following pages of a url.
	 * This is done on the logs - not the archives!
	 */
	public function getFollowingPages($url, $idSite, $period, $date, $segment = false)
	{
		$this->authenticate($idSite);
		
		$resultDataTable = new Piwik_DataTable;
		
		try
		{
			// TODO find a good value for $limitBeforeGrouping - add config option?
			$transitionsReport = Piwik_Transitions_API::getInstance()->getTransitionsForAction(
					$url, $type = 'url', $idSite, $period, $date, $segment, $limitBeforeGrouping = 100, 
					$part = 'followingActions', $returnNormalizedUrls = true);
		}
		catch(Exception $e)
		{
			return $resultDataTable;
		}
		
		$reports = array('followingPages', 'outlinks', 'downloads');
		foreach ($reports as $reportName)
		{
			if (!isset($transitionsReport[$reportName]))
			{
				continue;
			}
			foreach ($transitionsReport[$reportName]->getRows() as $row)
			{
				// don't touch the row at all for performance reasons
				$resultDataTable->addRow($row);
			}
		}
		
		return $resultDataTable;
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

}
