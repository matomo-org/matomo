<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_UserCountry
 */


require_once "DataFiles/Countries.php";


/**
 * 
 * @package Piwik_UserCountry
 */
class Piwik_UserCountry_API extends Piwik_Apiable
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
	
	public function getCountry( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getDataTable('UserCountry_country');
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddDetail', array('label', 'code', create_function('$label', 'return $label;')));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddDetail', array('label', 'logo', 'Piwik_getFlagFromCode'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_CountryTranslate'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
		$dataTable->queueFilter('Piwik_DataTable_Filter_AddConstantDetail', array('logoWidth', 18));
		$dataTable->queueFilter('Piwik_DataTable_Filter_AddConstantDetail', array('logoHeight', 12));
		return $dataTable;
	}
	public function getContinent( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getDataTable('UserCountry_continent');
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddDetail', array('label', 'code', create_function('$label', 'return $label;')));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_ContinentTranslate'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
		return $dataTable;
	}
	
	
	function getNumberOfDistinctCountries($idSite, $period, $date)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		return $archive->getDataTableFromNumeric('UserCountry_distinctCountries');
	}
	
	
}

function Piwik_getFlagFromCode($code)
{
	$path = 'plugins/UserCountry/flags/%s.png';
	
	$normalPath = sprintf($path,$code);
	
	// flags not in the package !
	if(!file_exists($normalPath))
	{
		return sprintf($path, 'xx');			
	}
	return $normalPath;
}

function Piwik_ContinentTranslate($label)
{
	if($label == 'unk')
	{
		return Piwik_Translate('General_Unknown');
	}
	
	return Piwik_Translate('continent_'. $label);
}
function Piwik_CountryTranslate($label)
{
	if($label == 'xx')
	{
		return Piwik_Translate('General_Unknown');
	}
	
	return Piwik_Translate('country_'. $label);
}