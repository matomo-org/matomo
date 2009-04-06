<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_UserSettings
 */

require_once "UserSettings/functions.php";

/**
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
		$dataTable->queuefilter('ReplaceColumnNames');
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
		$dataTable->queuefilter('ColumnCallbackReplace', array('label', 'Piwik_getConfigurationLabel'));
		return $dataTable;
	}

	public function getOS( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_os', $idSite, $period, $date);
		$dataTable->queuefilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getOSLogo'));
		$dataTable->queuefilter('ColumnCallbackAddMetadata', array( 'label', 'shortLabel', 'Piwik_getOSShortLabel') );
		$dataTable->queuefilter('ColumnCallbackReplace', array( 'label', 'Piwik_getOSLabel') );
		return $dataTable;
	}
		
	public function getBrowser( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_browser', $idSite, $period, $date);
		$dataTable->queuefilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getBrowsersLogo'));
		$dataTable->queuefilter('ColumnCallbackAddMetadata', array('label', 'shortLabel', 'Piwik_getBrowserShortLabel'));
		$dataTable->queuefilter('ColumnCallbackReplace', array('label', 'Piwik_getBrowserLabel'));
		return $dataTable;
	}
	
	public function getBrowserType( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_browserType', $idSite, $period, $date);
		$dataTable->queuefilter('ColumnCallbackAddMetadata', array('label', 'shortLabel', 'ucfirst'));
		$dataTable->queuefilter('ColumnCallbackReplace', array('label', 'Piwik_getBrowserTypeLabel'));
		return $dataTable;
	}
	
	public function getWideScreen( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_wideScreen', $idSite, $period, $date);
		$dataTable->queuefilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getScreensLogo'));
		$dataTable->queuefilter('ColumnCallbackReplace', array('label', 'ucfirst'));
		return $dataTable;
	}
	
	public function getPlugin( $idSite, $period, $date )
	{
		$dataTable = $this->getDataTable('UserSettings_plugin', $idSite, $period, $date);
		$dataTable->queuefilter('ColumnCallbackAddMetadata', array('label', 'logo', 'Piwik_getPluginsLogo'));
		$dataTable->queuefilter('ColumnCallbackReplace', array('label', 'ucfirst'));
		return $dataTable;
	}	
}
