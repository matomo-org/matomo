<?php

require_once "DataFiles/Browsers.php";
require_once "DataFiles/OS.php";
require_once "Actions.php";
		
class Piwik_Referers_API extends Piwik_Apiable
{
	static private $instance = null;
	protected function __construct()
	{
		parent::__construct();
	}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	function getDataTable($name, $idSite, $period, $date, $idSubtable = null)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		
		$dataTable = $archive->getDataTable($name, $idSubtable);
		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
		return $dataTable;
	}
	function getRefererType($idSite, $period, $date)
	{
		$dataTable = $this->getDataTable('Referers_type',$idSite, $period, $date);
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_getRefererTypeLabel'));
		return $dataTable;
	}
	
	function getKeywords($idSite, $period, $date)
	{
		$dataTable = $this->getDataTable('Referers_searchEngineByKeyword',$idSite, $period, $date);
		return $dataTable;
	}
	
	function getSearchEnginesFromKeywordId($idSite, $period, $date, $idSubtable)
	{
		require PIWIK_DATAFILES_INCLUDE_PATH . "/SearchEngines.php";
		$dataTable = $this->getDataTable('Referers_searchEngineByKeyword',$idSite, $period, $date, $idSubtable);
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddDetail', array( 'label', 'url', 'Piwik_getSearchEngineUrlFromName') );
		$dataTable->queueFilter('Piwik_DataTable_Filter_DetailCallbackAddDetail', array( 'url', 'logo', 'Piwik_getSearchEngineLogoFromName') );
		return $dataTable;		
	}
	
	function getSearchEngines($idSite, $period, $date)
	{
		$dataTable = $this->getDataTable('Referers_keywordBySearchEngine',$idSite, $period, $date);
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddDetail', array( 'label', 'url', 'Piwik_getSearchEngineUrlFromName') );
		$dataTable->queueFilter('Piwik_DataTable_Filter_DetailCallbackAddDetail', array( 'url', 'logo', 'Piwik_getSearchEngineLogoFromName') );
		return $dataTable;
	}
	
	function getKeywordsFromSearchEngineId($idSite, $period, $date, $idSubtable)
	{}
	
	function getCampaigns($idSite, $period, $date)
	{}
	function getKeywordsFromCampaignId($idSite, $period, $date, $idSubtable)
	{}
	
	function getWebsites($idSite, $period, $date)
	{}
	function getUrlsFromWebsiteId($idSite, $period, $date, $idSubtable)
	{}
	
	function getPartners($idSite, $period, $date)
	{}
	function getUrlsFromPartnerId($idSite, $period, $date, $idSubtable)
	{}
	
	
	function getNumberOfDistinctSearchEngines($idSite, $period, $date)
	{}
	function getNumberOfDistinctKeywords($idSite, $period, $date)
	{}
	function getNumberOfDistinctCampaigns($idSite, $period, $date)
	{}
	function getNumberOfDistinctWebsites($idSite, $period, $date)
	{}
	function getNumberOfDistinctWebsitesUrls($idSite, $period, $date)
	{}
	function getNumberOfDistinctPartners($idSite, $period, $date)
	{}
	function getNumberOfDistinctPartnersUrls($idSite, $period, $date)
	{}
}

function Piwik_getSearchEngineUrlFromName($name)
{
	if(isset($GLOBALS['Piwik_SearchEngines_NameToUrl'][$name]))
	{
		$url = 'http://'.$GLOBALS['Piwik_SearchEngines_NameToUrl'][$name];
	}
	else
	{
		$url = 'URL unknown!';
	}
	return $url;
}

function Piwik_getSearchEngineLogoFromName($url)
{
	$path = PIWIK_PLUGINS_PATH . '/Referers/images/searchEngines/%s.png';
	
	$beginningUrl = strpos($url,'//')+2;
	$normalPath = sprintf($path, substr($url,$beginningUrl));
	
	// flags not in the package !
	if(!file_exists($normalPath))
	{
		return sprintf($path, 'xx');			
	}
	return $normalPath;
}

function Piwik_getRefererTypeLabel($label)
{	
	$indexTranslation = '';
	switch($label)
	{
		case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
			$indexTranslation = 'Referers_DirectEntry';
		break;
		case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
			$indexTranslation = 'Referers_SearchEngines';
		break;
		case Piwik_Common::REFERER_TYPE_WEBSITE:
			$indexTranslation = 'Referers_Websites';
		break;
		case Piwik_Common::REFERER_TYPE_PARTNER:
			$indexTranslation = 'Referers_Partners';
		break;
		case Piwik_Common::REFERER_TYPE_NEWSLETTER:
			$indexTranslation = 'Referers_Newsletters';
		break;
		case Piwik_Common::REFERER_TYPE_CAMPAIGN:
			$indexTranslation = 'Referers_Campaigns';
		break;
	}
	return Piwik_Translate($indexTranslation);
}
//'Referers_keywordBySearchEngine',
//'Referers_searchEngineByKeyword',
//'Referers_type',

//'Referers_keywordByCampaign',
//'Referers_urlByWebsite',
//'Referers_urlByPartner',

