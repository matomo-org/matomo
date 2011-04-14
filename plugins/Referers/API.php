<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Referers
 */

/**
 * The Referrers API lets you access reports about Websites, Search engines, Keywords, Campaigns used to access your website.
 * 
 * For example, "getKeywords" returns all search engine keywords (with <a href='http://piwik.org/docs/analytics-api/reference/#toc-metric-definitions' target='_blank'>general analytics metrics</a> for each keyword), "getWebsites" returns referrer websites (along with the full Referrer URL if the parameter &expanded=1 is set). 
 * "getRefererType" returns the Referrer overview report. "getCampaigns" returns the list of all campaigns (and all campaign keywords if the parameter &expanded=1 is set).
 * 
 * The methods "getKeywordsForPageUrl" and "getKeywordsForPageTitle" are used to output the top keywords used to find a page. 
 * Check out the widget <a href='http://demo.piwik.org/index.php?module=Widgetize&action=iframe&moduleToWidgetize=Referers&actionToWidgetize=getKeywordsForPage&idSite=7&period=day&date=2011-02-15&disableLink=1' target='_blank'>"Top keywords used to find this page"</a> that you can easily re-use on your website.
 * @package Piwik_Referers
 */
class Piwik_Referers_API 
{
	static private $instance = null;
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * @return Piwik_DataTable
	 */
	protected function getDataTable($name, $idSite, $period, $date, $segment, $expanded, $idSubtable = null)
	{
	    $dataTable = Piwik_Archive::getDataTableFromArchive($name, $idSite, $period, $date, $segment, $expanded, $idSubtable);
	    $dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS, 'desc', $naturalSort = false, $expanded));
		$dataTable->queueFilter('ReplaceColumnNames');
		return $dataTable;
	}
	
	public function getRefererType($idSite, $period, $date, $segment = false, $typeReferer = false)
	{
		$dataTable = $this->getDataTable('Referers_type', $idSite, $period, $date, $segment, $expanded = false);
		if($typeReferer !== false)
		{
			$dataTable->filter('Pattern', array('label', $typeReferer));
		}
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getRefererTypeLabel'));
		return $dataTable;
	}
	
	public function getKeywords($idSite, $period, $date, $segment = false, $expanded = false)
	{
		$dataTable = $this->getDataTable('Referers_searchEngineByKeyword', $idSite, $period, $date, $segment, $expanded);
		return $dataTable;
	}
	
	public function getKeywordsForPageUrl($idSite, $period, $date, $url)
	{
		// Fetch the Top keywords for this page
		$segment = 'entryPageUrl=='.$url;
		$table = $this->getKeywords($idSite, $period, $date, $segment);
		return $this->getLabelsFromTable($table);

	}
	
	public function getKeywordsForPageTitle($idSite, $period, $date, $title)
	{
		$segment = 'entryPageTitle=='.$title;
		$table = $this->getKeywords($idSite, $period, $date, $segment);
		return $this->getLabelsFromTable($table);
	}
	
	protected function getLabelsFromTable($table)
	{
		$request = $_GET;
		$request['serialize'] = 0;
		
		// Apply generic filters
		$response = new Piwik_API_ResponseBuilder($format = 'original', $request);
		$table = $response->getResponse($table);
		
		// If period=lastX we only keep the first resultset as we want to return a plain list
		if($table instanceof Piwik_DataTable_Array)
		{
			$tables = $table->getArray();
			$table = current($tables);
		}
		// Keep the response simple, only include keywords
		$keywords = $table->getColumn('label');
		return $keywords;
	}

	public function getSearchEnginesFromKeywordId($idSite, $period, $date, $idSubtable, $segment = false)
	{
		$dataTable = $this->getDataTable('Referers_searchEngineByKeyword',$idSite, $period, $date, $segment, $expanded = false, $idSubtable);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array( 'label', 'url', 'Piwik_getSearchEngineUrlFromName') );
		$dataTable->queueFilter('MetadataCallbackAddMetadata', array( 'url', 'logo', 'Piwik_getSearchEngineLogoFromUrl') );
		
		// get the keyword and create the URL to the search result page
		$keywords = $this->getKeywords($idSite, $period, $date, $segment);
		$subTable = $keywords->getRowFromIdSubDataTable($idSubtable);
		if($subTable)
		{
    		$keyword = $subTable->getColumn('label');
    		$dataTable->queueFilter('MetadataCallbackReplace', array( 'url', 'Piwik_getSearchEngineUrlFromUrlAndKeyword', array($keyword)) );
		}
		return $dataTable;
	}

	public function getSearchEngines($idSite, $period, $date, $segment = false, $expanded = false)
	{
		$dataTable = $this->getDataTable('Referers_keywordBySearchEngine',$idSite, $period, $date, $segment, $expanded);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array( 'label', 'url', 'Piwik_getSearchEngineUrlFromName') );
		$dataTable->queueFilter('MetadataCallbackAddMetadata', array( 'url', 'logo', 'Piwik_getSearchEngineLogoFromUrl') );
		return $dataTable;
	}

	public function getKeywordsFromSearchEngineId($idSite, $period, $date, $idSubtable, $segment = false)
	{
		$dataTable = $this->getDataTable('Referers_keywordBySearchEngine',$idSite, $period, $date, $segment, $expanded = false, $idSubtable);
		
		// get the search engine and create the URL to the search result page
		$searchEngines = $this->getSearchEngines($idSite, $period, $date, $segment);
		$searchEngines->applyQueuedFilters();
		$subTable = $searchEngines->getRowFromIdSubDataTable($idSubtable);
		if($subTable)
		{
    		$searchEngineUrl = $subTable->getMetadata('url');
    		$dataTable->queueFilter('ColumnCallbackAddMetadata', array( 'label', 'url', 'Piwik_getSearchEngineUrlFromKeywordAndUrl', array($searchEngineUrl)));
		}
		return $dataTable;
	}

	public function getCampaigns($idSite, $period, $date, $segment = false, $expanded = false)
	{
		$dataTable = $this->getDataTable('Referers_keywordByCampaign',$idSite, $period, $date, $segment, $expanded);
		return $dataTable;
	}

	public function getKeywordsFromCampaignId($idSite, $period, $date, $idSubtable, $segment = false)
	{
		$dataTable = $this->getDataTable('Referers_keywordByCampaign',$idSite, $period, $date, $segment, $expanded = false, $idSubtable);
		return $dataTable;
	}

	public function getWebsites($idSite, $period, $date, $segment = false, $expanded = false)
	{
		$dataTable = $this->getDataTable('Referers_urlByWebsite',$idSite, $period, $date, $segment, $expanded);
		return $dataTable;
	}
	
	public function getUrlsFromWebsiteId($idSite, $period, $date, $idSubtable, $segment = false)
	{
		$dataTable = $this->getDataTable('Referers_urlByWebsite',$idSite, $period, $date, $segment, $expanded = false, $idSubtable);
		// the htmlspecialchars_decode call is for BC for before 1.1 
		// as the Referer URL was previously encoded in the log tables, but is now recorded raw
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array( 'label', 'url', create_function('$label', 'return htmlspecialchars_decode($label);')) );
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getPathFromUrl'));
		return $dataTable;
	}

	public function getNumberOfDistinctSearchEngines($idSite, $period, $date, $segment = false)
	{
		return $this->getNumeric('Referers_distinctSearchEngines', $idSite, $period, $date, $segment);
	}

	public function getNumberOfDistinctKeywords($idSite, $period, $date, $segment = false)
	{
		return $this->getNumeric('Referers_distinctKeywords', $idSite, $period, $date, $segment);
	}

	public function getNumberOfDistinctCampaigns($idSite, $period, $date, $segment = false)
	{
		return $this->getNumeric('Referers_distinctCampaigns', $idSite, $period, $date, $segment);
	}

	public function getNumberOfDistinctWebsites($idSite, $period, $date, $segment = false)
	{
		return $this->getNumeric('Referers_distinctWebsites', $idSite, $period, $date, $segment);
	}

	public function getNumberOfDistinctWebsitesUrls($idSite, $period, $date, $segment = false)
	{
		return $this->getNumeric('Referers_distinctWebsitesUrls', $idSite, $period, $date, $segment);
	}

	private function getNumeric($name, $idSite, $period, $date, $segment)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
		return $archive->getDataTableFromNumeric($name);
	}
}
