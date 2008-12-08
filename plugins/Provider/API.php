<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Provider
 */
require_once "Provider/functions.php";

/**
 * 
 * @package Piwik_Provider
 */
class Piwik_Provider_API 
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

	public function getProvider( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getDataTable('Provider_hostnameExt');
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddMetadata', array('label', 'url', 'Piwik_getHostnameUrl'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_getHostnameName'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
		return $dataTable;
	}
	
	/**
	 * Example of getting a RAW BLOB
	 *
	 * @return blob
	 */
	public function getProviderBlob( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getBlob('Provider_hostnameExt');
		return $dataTable;
	}
}

