<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Actions
 */

/**
 * Actions API
 *
 * @package Piwik_Actions
 */
class Piwik_Actions_API
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
	
	protected function getDataTable($name, $idSite, $period, $date, $expanded, $idSubtable )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
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
		$dataTable->filter('Sort', array('nb_visits', 'desc', $naturalSort = false, $expanded));
		$dataTable->queueFilter('ReplaceSummaryRowLabel');
		return $dataTable;
	}

	/**
	 * Backward compatibility. Fallsback to getPageTitles() instead.
	 * @deprecated Deprecated since Piwik 0.5
	 */
	public function getActions( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
	    return $this->getPageTitles( $idSite, $period, $date, $expanded, $idSubtable );
	}
	
	public function getPageUrls( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		return $this->getDataTable('Actions_actions_url', $idSite, $period, $date, $expanded, $idSubtable );
	}

	public function getPageTitles( $idSite, $period, $date, $expanded = false, $idSubtable = false)
	{
		$dataTable = $this->getDataTable('Actions_actions', $idSite, $period, $date, $expanded, $idSubtable);
		return $dataTable;
	}

	public function getDownloads( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		$dataTable = $this->getDataTable('Actions_downloads', $idSite, $period, $date, $expanded, $idSubtable );
		return $dataTable;
	}

	public function getOutlinks( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		$dataTable = $this->getDataTable('Actions_outlink', $idSite, $period, $date, $expanded, $idSubtable );
		return $dataTable;
	}
}

