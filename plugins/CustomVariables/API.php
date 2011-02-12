<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CustomVariables
 */

/**
 * 
 * @package Piwik_CustomVariables
 */
class Piwik_CustomVariables_API 
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

	protected function getDataTable($idSite, $period, $date, $segment, $expanded, $idSubtable)
	{
	    $dataTable = Piwik_Archive::getDataTableFromArchive('CustomVariables_valueByName', $idSite, $period, $date, $segment, $expanded, $idSubtable);
		$dataTable->filter('Sort', array(Piwik_Archive::INDEX_NB_VISITS, 'desc', $naturalSort = false, $expanded));
		$dataTable->queueFilter('ReplaceColumnNames', array($expanded));
	    return $dataTable;
	}

	public function getCustomVariables($idSite, $period, $date, $segment = false, $expanded = false)
	{
	    $dataTable = $this->getDataTable($idSite, $period, $date, $segment, $expanded, $idSubtable = null);
		return $dataTable;
	}

	public function getCustomVariablesValuesFromNameId($idSite, $period, $date, $segment = false, $idSubtable)
	{
	    $dataTable = $this->getDataTable($idSite, $period, $date, $segment, $expanded = false, $idSubtable);
		return $dataTable;
	}
}

