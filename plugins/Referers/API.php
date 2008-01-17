<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Referers
 */


require_once "DataFiles/Browsers.php";
require_once "DataFiles/OS.php";
require_once "Actions.php";
		

/**
 * 
 * @package Piwik_Referers
 */
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
	
	private function getDataTable($name, $idSite, $period, $date, $idSubtable = null)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		
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
	{
		$dataTable = $this->getDataTable('Referers_keywordBySearchEngine',$idSite, $period, $date, $idSubtable);
		return $dataTable;		
	}
	
	function getCampaigns($idSite, $period, $date)
	{
		$dataTable = $this->getDataTable('Referers_keywordByCampaign',$idSite, $period, $date);
		return $dataTable;
	}
	
	function getKeywordsFromCampaignId($idSite, $period, $date, $idSubtable)
	{
		$dataTable = $this->getDataTable('Referers_keywordByCampaign',$idSite, $period, $date, $idSubtable);
		return $dataTable;	
	}
	
	function getWebsites($idSite, $period, $date)
	{
		$dataTable = $this->getDataTable('Referers_urlByWebsite',$idSite, $period, $date);
		return $dataTable;
	}
	function getUrlsFromWebsiteId($idSite, $period, $date, $idSubtable)
	{	
		$dataTable = $this->getDataTable('Referers_urlByWebsite',$idSite, $period, $date, $idSubtable);
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddDetail', array( 'label', 'url', create_function('$label', 'return $label;')) );
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_getPathFromUrl'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_truncatePath'));
		return $dataTable;		
	}
	
	function getPartners($idSite, $period, $date)
	{
		$dataTable = $this->getDataTable('Referers_urlByPartner',$idSite, $period, $date);
		return $dataTable;
	}
	
	function getUrlsFromPartnerId($idSite, $period, $date, $idSubtable)
	{	
		$dataTable = $this->getDataTable('Referers_urlByPartner',$idSite, $period, $date, $idSubtable);
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddDetail', array( 'label', 'url', create_function('$label', 'return $label;')) );
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_getPathFromUrl'));
		return $dataTable;		
	}
	
	private function getNumeric($name, $idSite, $period, $date)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		return $archive->getDataTableFromNumeric($name);
	}
	
	function getNumberOfDistinctSearchEngines($idSite, $period, $date)
	{
		return $this->getNumeric('Referers_distinctSearchEngines', $idSite, $period, $date);
	}
	
	function getNumberOfDistinctKeywords($idSite, $period, $date)
	{
		return $this->getNumeric('Referers_distinctKeywords', $idSite, $period, $date);
	}
	function getNumberOfDistinctCampaigns($idSite, $period, $date)
	{
		return $this->getNumeric('Referers_distinctCampaigns', $idSite, $period, $date);
	}
	function getNumberOfDistinctWebsites($idSite, $period, $date)
	{
		return $this->getNumeric('Referers_distinctWebsites', $idSite, $period, $date);
	}
	function getNumberOfDistinctWebsitesUrls($idSite, $period, $date)
	{
		return $this->getNumeric('Referers_distinctWebsitesUrls', $idSite, $period, $date);
	}
	function getNumberOfDistinctPartners($idSite, $period, $date)
	{
		return $this->getNumeric('Referers_distinctPartners', $idSite, $period, $date);
	}
	function getNumberOfDistinctPartnersUrls($idSite, $period, $date)
	{
		return $this->getNumeric('Referers_distinctPartnersUrls', $idSite, $period, $date);
	}
}

function Piwik_getPathFromUrl($url)
{
	$path = Piwik_Common::getPathAndQueryFromUrl($url);
	if(empty($path))
	{
		return 'index';
	}
	return $path;
}

function Piwik_truncatePath( $path )
{
	$limit = 27;
	$path = htmlspecialchars_decode($path);
	$len = strlen($path);
	if($len > $limit)
	{
		$path = substr($path, 0, $limit-3) . "...";
	}
	return htmlspecialchars($path);
}

function Piwik_getSearchEngineUrlFromName($name)
{
	require_once PIWIK_DATAFILES_INCLUDE_PATH . "/SearchEngines.php";
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
	require_once PIWIK_DATAFILES_INCLUDE_PATH . "/SearchEngines.php";
	$path = PIWIK_PLUGINS_PATH . '/Referers/images/searchEngines/%s.png';
	$beginningUrl = strpos($url,'//') + 2;
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

