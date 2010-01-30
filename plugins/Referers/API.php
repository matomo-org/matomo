<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Referers
 */

// no direct access
defined('PIWIK_INCLUDE_PATH') or die;

/**
 * @see plugins/Referers/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Referers/functions.php';

/**
 *
 * @package Piwik_Referers
 */
class Piwik_Referers_API 
{
	static private $instance = null;
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}

	/**
	 * @return Piwik_DataTable
	 */
	static protected function getDataTable($name, $idSite, $period, $date, $expanded, $idSubtable = null)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );

		if($expanded)
		{
			$dataTable = $archive->getDataTableExpanded($name, $idSubtable);
		}
		else
		{
			$dataTable = $archive->getDataTable($name, $idSubtable);
		}
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS, 'desc', $naturalSort = false, $expanded));
		$dataTable->queueFilter('ReplaceColumnNames', array($expanded));
		$dataTable->queueFilter('ReplaceSummaryRowLabel');
		return $dataTable;
	}
	
	static public function getRefererType($idSite, $period, $date, $typeReferer = false)
	{
		$dataTable = self::getDataTable('Referers_type', $idSite, $period, $date, $expanded = false);
		if($typeReferer !== false)
		{
			$dataTable->filter('Pattern', array('label', $typeReferer));
		}
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getRefererTypeLabel'));
		return $dataTable;
	}
	
	static public function getKeywords($idSite, $period, $date, $expanded = false)
	{
		$dataTable = self::getDataTable('Referers_searchEngineByKeyword', $idSite, $period, $date, $expanded);
		return $dataTable;
	}

	static public function getSearchEnginesFromKeywordId($idSite, $period, $date, $idSubtable)
	{
		$dataTable = self::getDataTable('Referers_searchEngineByKeyword',$idSite, $period, $date, $expanded = false, $idSubtable);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array( 'label', 'url', 'Piwik_getSearchEngineUrlFromName') );
		$dataTable->queueFilter('MetadataCallbackAddMetadata', array( 'url', 'logo', 'Piwik_getSearchEngineLogoFromUrl') );
		
		// get the keyword and create the URL to the search result page
		$keywords = self::getKeywords($idSite, $period, $date);
		$keyword = $keywords->getRowFromIdSubDataTable($idSubtable)->getColumn('label');
		$dataTable->queueFilter('MetadataCallbackReplace', array( 'url', 'Piwik_getSearchEngineUrlFromUrlAndKeyword', array($keyword)) );
		return $dataTable;
	}

	static public function getSearchEngines($idSite, $period, $date, $expanded = false)
	{
		$dataTable = self::getDataTable('Referers_keywordBySearchEngine',$idSite, $period, $date, $expanded);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array( 'label', 'url', 'Piwik_getSearchEngineUrlFromName') );
		$dataTable->queueFilter('MetadataCallbackAddMetadata', array( 'url', 'logo', 'Piwik_getSearchEngineLogoFromUrl') );
		return $dataTable;
	}

	static public function getKeywordsFromSearchEngineId($idSite, $period, $date, $idSubtable)
	{
		$dataTable = self::getDataTable('Referers_keywordBySearchEngine',$idSite, $period, $date, $expanded = false, $idSubtable);
		
		// get the search engine and create the URL to the search result page
		$searchEngines = self::getSearchEngines($idSite, $period, $date);
		$searchEngines->applyQueuedFilters();
		$searchEngineUrl = $searchEngines->getRowFromIdSubDataTable($idSubtable)->getMetadata('url');
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array( 'label', 'url', 'Piwik_getSearchEngineUrlFromKeywordAndUrl', array($searchEngineUrl)));
		return $dataTable;
	}

	static public function getCampaigns($idSite, $period, $date, $expanded = false)
	{
		$dataTable = self::getDataTable('Referers_keywordByCampaign',$idSite, $period, $date, $expanded);
		return $dataTable;
	}

	static public function getKeywordsFromCampaignId($idSite, $period, $date, $idSubtable)
	{
		$dataTable = self::getDataTable('Referers_keywordByCampaign',$idSite, $period, $date, $expanded = false, $idSubtable);
		return $dataTable;
	}

	static public function getWebsites($idSite, $period, $date, $expanded = false)
	{
		$dataTable = self::getDataTable('Referers_urlByWebsite',$idSite, $period, $date, $expanded);
		return $dataTable;
	}
	
	static public function getUrlsFromWebsiteId($idSite, $period, $date, $idSubtable)
	{
		$dataTable = self::getDataTable('Referers_urlByWebsite',$idSite, $period, $date, $expanded = false, $idSubtable);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array( 'label', 'url', create_function('$label', 'return $label;')) );
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getPathFromUrl'));
		return $dataTable;
	}

	static public function getNumberOfDistinctSearchEngines($idSite, $period, $date)
	{
		return self::getNumeric('Referers_distinctSearchEngines', $idSite, $period, $date);
	}

	static public function getNumberOfDistinctKeywords($idSite, $period, $date)
	{
		return self::getNumeric('Referers_distinctKeywords', $idSite, $period, $date);
	}

	static public function getNumberOfDistinctCampaigns($idSite, $period, $date)
	{
		return self::getNumeric('Referers_distinctCampaigns', $idSite, $period, $date);
	}

	static public function getNumberOfDistinctWebsites($idSite, $period, $date)
	{
		return self::getNumeric('Referers_distinctWebsites', $idSite, $period, $date);
	}

	static public function getNumberOfDistinctWebsitesUrls($idSite, $period, $date)
	{
		return self::getNumeric('Referers_distinctWebsitesUrls', $idSite, $period, $date);
	}

	static private function getNumeric($name, $idSite, $period, $date)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		return $archive->getDataTableFromNumeric($name);
	}
}
