<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_UserSettings
 */

/**
 * @see plugins/UserSettings/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/UserSettings/functions.php';

/**
 *
 * @package Piwik_UserSettings
 */
class Piwik_UserSettings_API 
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
	
	protected function getDataTable($name, $idSite, $period, $date)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getDataTable($name);
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS));
		$dataTable->queueFilter('ReplaceColumnNames');
		$dataTable->queueFilter('ReplaceSummaryRowLabel');
		return $dataTable;
	}

	public function getResolution( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_resolution', $idSite, $period, $date);
		return $dataTable;
	}

	public function getConfiguration( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_configuration', $idSite, $period, $date);
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getConfigurationLabel'));
		return $dataTable;
	}

	public function getOS( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_os', $idSite, $period, $date);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getOSLogo'));
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array( 'label', 'shortLabel', 'Piwik_getOSShortLabel') );
		$dataTable->queueFilter('ColumnCallbackReplace', array( 'label', 'Piwik_getOSLabel') );
		return $dataTable;
	}
		
	public function getBrowser( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_browser', $idSite, $period, $date);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getBrowsersLogo'));
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'shortLabel', 'Piwik_getBrowserShortLabel'));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getBrowserLabel'));
		return $dataTable;
	}
	
	public function getBrowserType( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_browserType', $idSite, $period, $date);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'shortLabel', 'ucfirst'));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getBrowserTypeLabel'));
		return $dataTable;
	}
	
	public function getWideScreen( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_wideScreen', $idSite, $period, $date);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getScreensLogo'));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'ucfirst'));
		return $dataTable;
	}
	
	public function getPlugin( $idSite, $period, $date )
	{
		// fetch all archive data required
		$dataTable = $this->getDataTable('UserSettings_plugin', $idSite, $period, $date);
		$browserTypes	= $this->getDataTable('UserSettings_browserType', $idSite, $period, $date);
		$archive		= Piwik_Archive::build($idSite, $period, $date);
		$visitsSums		= $archive->getNumeric('nb_visits');

		// check wether given tables are arrays
		if($dataTable instanceof Piwik_DataTable_Array) {
			$tableArray			= $dataTable->getArray();
			$browserTypesArray	= $browserTypes->getArray();
			$visitSumsArray		= $visitsSums->getArray();
		} else {
			$tableArray 		= Array($dataTable);
			$browserTypesArray 	= Array($browserTypes);
			$visitSumsArray 	= Array($visitsSums);
		}
		
		// walk through the results and calculate the percentage
		foreach($tableArray as $key => $table) {
			
			// get according browserType table
			foreach($browserTypesArray AS $k => $browsers) {
				if($k == $key) {
					$browserType = $browsers;
				}
			}
			
			// get according visitsSum
			foreach($visitSumsArray AS $k => $visits) {
				if($k == $key) {
					if(is_object($visits)) {
						$visitsSumTotal = (float)$visits->getFirstRow()->getColumn(0);
					} else {
						$visitsSumTotal = (float)$visits;
					}
				}
			}
			
			$ieStats		= $browserType->getRowFromLabel('ie');

			$ieVisits 	= 0;
			if($ieStats !== false)
			{
				$ieVisits = $ieStats->getColumn(Piwik_Archive::INDEX_NB_VISITS);
			}
			$visitsSum		= $visitsSumTotal - $ieVisits;
		
			// Calculate percentage, but ignore IE users cause plugin detection doesn't work on IE
			// The filter must be applied now so that the new column can 
			// be sorted by the generic filters (applied right after this function exits)
			$table->filter('ColumnCallbackAddColumnPercentage', array('nb_visits_percentage', Piwik_Archive::INDEX_NB_VISITS, $visitsSum, 1));
		
			// correct the cookie value (as detection works in IE, too)
			$row = $table->getRowFromLabel('cookie');
			if($row) {
				$percentage = Piwik::getPercentageSafe($row->getColumn(Piwik_Archive::INDEX_NB_VISITS), $visitsSumTotal, 1) . '%';
				$row->setColumn('nb_visits_percentage', $percentage);
			}
			
		}
		
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getPluginsLogo'));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'ucfirst'));
		
		return $dataTable;
	}
	
}
