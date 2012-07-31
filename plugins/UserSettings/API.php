<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
 * The UserSettings API lets you access reports about your Visitors technical settings: browsers, browser types (rendering engine),
 * operating systems, plugins supported in their browser, Screen resolution and Screen types (normal, widescreen, dual screen or mobile).
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
			self::$instance = new self;
		}
		return self::$instance;
	}

	protected function getDataTable($name, $idSite, $period, $date, $segment)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
		$dataTable = $archive->getDataTable($name);
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS));
		$dataTable->queueFilter('ReplaceColumnNames');
		$dataTable->queueFilter('ReplaceSummaryRowLabel');
		return $dataTable;
	}

	public function getResolution( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getDataTable('UserSettings_resolution', $idSite, $period, $date, $segment);
		return $dataTable;
	}

	public function getConfiguration( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getDataTable('UserSettings_configuration', $idSite, $period, $date, $segment);
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getConfigurationLabel'));
		return $dataTable;
	}

	public function getOS( $idSite, $period, $date, $segment = false, $addShortLabel = true )
	{
		$dataTable = $this->getDataTable('UserSettings_os', $idSite, $period, $date, $segment);
		// these filters are applied directly so other API methods can use GroupBy on the result of this method
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getOSLogo'));
		if ($addShortLabel)
		{
			$dataTable->filter(
				'ColumnCallbackAddMetadata', array( 'label', 'shortLabel', 'Piwik_getOSShortLabel') );
		}
		$dataTable->filter('ColumnCallbackReplace', array( 'label', 'Piwik_getOSLabel') );
		return $dataTable;
	}
	
	/**
	 * Gets a DataTable displaying number of visits by operating system family. The operating
	 * system families are listed in /libs/UserAgentParser/UserAgentParser.php.
	 */
	public function getOSFamily( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getOS($idSite, $period, $date, $segment, $addShortLabel = false);
		$dataTable->filter('GroupBy', array('label', 'Piwik_UserSettings_getOSFamily'));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_Translate'));
		return $dataTable;
	}
	
	/**
	 * Gets a DataTable displaying number of visits by device type (mobile vs. desktop).
	 */
	public function getMobileVsDesktop( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getOS($idSite, $period, $date, $segment, $addShortLabel = false);
		$dataTable->filter('GroupBy', array('label', 'Piwik_UserSettings_getDeviceTypeFromOS'));
		
		// make sure the datatable has a row for mobile & desktop (if it has rows)
		$empty = new Piwik_DataTable();
		$empty->addRowsFromSimpleArray(array(
			array('label' => 'General_Desktop', Piwik_Archive::INDEX_NB_UNIQ_VISITORS => 0),
			array('label' => 'General_Mobile', Piwik_Archive::INDEX_NB_UNIQ_VISITORS => 0)
		));
		self::addDataTableRecursive($dataTable, $empty);
		
		// set the logo metadata
		$dataTable->queueFilter('MetadataCallbackReplace',
			array('logo', 'Piwik_UserSettings_getDeviceTypeImg', null, array('label')));
		
		// translate the labels
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_Translate'));
		
		return $dataTable;
	}
	
	public function getBrowserVersion( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getDataTable('UserSettings_browser', $idSite, $period, $date, $segment);
		// these filters are applied directly so getBrowser can use GroupBy on the result of this method
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getBrowsersLogo'));
		$dataTable->filter('ColumnCallbackAddMetadata', array('label', 'shortLabel', 'Piwik_getBrowserShortLabel'));
		$dataTable->filter('ColumnCallbackReplace', array('label', 'Piwik_getBrowserLabel'));
		return $dataTable;
	}
	
	/**
	 * Gets a DataTable displaying number of visits by browser (ie, Firefox, Chrome, etc.).
	 * The browser version is not included in this report.
	 */
	public function getBrowser( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getBrowserVersion($idSite, $period, $date, $segment);
		
		$getBrowserFromBrowserVersion = 'Piwik_UserSettings_getBrowserFromBrowserVersion';
		$dataTable->filter('GroupBy', array('label', $getBrowserFromBrowserVersion));
		
		return $dataTable;
	}
	
	public function getBrowserType( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getDataTable('UserSettings_browserType', $idSite, $period, $date, $segment);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'shortLabel', 'ucfirst'));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getBrowserTypeLabel'));
		return $dataTable;
	}

	public function getWideScreen( $idSite, $period, $date, $segment = false )
	{
		$dataTable = $this->getDataTable('UserSettings_wideScreen', $idSite, $period, $date, $segment);
		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getScreensLogo'));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'ucfirst'));
		return $dataTable;
	}

	public function getPlugin( $idSite, $period, $date, $segment = false )
	{
		// fetch all archive data required
		$dataTable = $this->getDataTable('UserSettings_plugin', $idSite, $period, $date, $segment);
		$browserTypes	= $this->getDataTable('UserSettings_browserType', $idSite, $period, $date, $segment);
		$archive		= Piwik_Archive::build($idSite, $period, $date, $segment);
		$visitsSums		= $archive->getNumeric('nb_visits');

		// check whether given tables are arrays
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

			// Calculate percentage, but ignore IE users because plugin detection doesn't work on IE
			$ieVisits = 0;

			$ieStats = $browserType->getRowFromLabel('ie');
			if($ieStats !== false)
			{
				$ieVisits = $ieStats->getColumn(Piwik_Archive::INDEX_NB_VISITS);
			}

			$visitsSum = $visitsSumTotal - $ieVisits;

			// The filter must be applied now so that the new column can
			// be sorted by the generic filters (applied right after this loop exits)
			$table->filter('ColumnCallbackAddColumnPercentage', array('nb_visits_percentage', Piwik_Archive::INDEX_NB_VISITS, $visitsSum, 1));
			$table->filter('RangeCheck', array('nb_visits_percentage'));
		}

		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getPluginsLogo'));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'ucfirst'));

		return $dataTable;
	}
	
	/**
	 * Adds a DataTable to another. Will recurse on Piwik_DataTable_Arrays.
	 * 
	 * @ignore
	 */
	private static function addDataTableRecursive( $dataTable, $toAdd )
	{
		if ($dataTable instanceof Piwik_DataTable_Array)
		{
			foreach ($dataTable->getArray() as $childTable)
			{
				self::addDataTableRecursive($childTable, $toAdd);
			}
		}
		else
		{
			if ($dataTable->getRowsCount() > 0)
			{
				$dataTable->addDataTable($toAdd);
			}
		}
	}
}
