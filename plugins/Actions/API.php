<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */


require_once "DataFiles/Browsers.php";
require_once "DataFiles/OS.php";
require_once "Actions.php";
		
class Piwik_Actions_API extends Piwik_Apiable
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
	
	protected function getDataTable($name, $idSite, $period, $date, $expanded, $idSubtable )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		
		if($idSubtable === false)
		{
			$idSubtable = null;
		}
		
		if($expanded)
		{
			$dataTable = $archive->getDataTableExpanded($name, $idSubtable);			
		}
		else
		{
			$dataTable = $archive->getDataTable($name, $idSubtable);
		}
		
		$dataTable->queueFilter(	'Piwik_DataTable_Filter_ReplaceColumnNames', 
									array(Piwik_Actions::getColumnsMap())
						);
		return $dataTable;
	}
	
	public function getActions( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		return $this->getDataTable('Actions_actions', $idSite, $period, $date, $expanded, $idSubtable );
	}

	public function getDownloads( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		return $this->getDataTable('Actions_downloads', $idSite, $period, $date, $expanded, $idSubtable );
	}

	public function getOutlinks( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		return $this->getDataTable('Actions_outlink', $idSite, $period, $date, $expanded, $idSubtable );
	}
}

