<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Goals
 */

/**
 *
 * @package Piwik_Goals
 */
class Piwik_Goals_API 
{
	static private $instance = null;
	/**
	 * @return Piwik_Goals_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	/**
	 * Returns all Goals for a given website
	 * 
	 * @param $idSite
	 * @return Array of Goal attributes
	 */
	public function getGoals( $idSite )
	{
		$goals = Piwik_FetchAll("SELECT * 
											FROM ".Piwik_Common::prefixTable('goal')." 
											WHERE idsite = ?
												AND deleted = 0", $idSite);
		$cleanedGoals = array();
		foreach($goals as &$goal)
		{
			unset($goal['idsite']);
			if($goal['match_attribute'] == 'manually') {
			    unset($goal['pattern']);
			    unset($goal['pattern_type']);
			    unset($goal['case_sensitive']);
			}
			$cleanedGoals[$goal['idgoal']] = $goal;
		}
		return $cleanedGoals;
	}

	/**
	 * Creates a Goal for a given website.
	 * 
	 * @param $idSite
	 * @param $name
	 * @param $matchAttribute 'url', 'file', 'external_website' or 'manually'
	 * @param $pattern eg. purchase-confirmation.htm
	 * @param $patternType 'regex', 'contains', 'exact' 
	 * @param $caseSensitive bool
	 * @param $revenue If set, default revenue to assign to conversions
	 * @return int ID of the new goal
	 */
	public function addGoal( $idSite, $name, $matchAttribute, $pattern, $patternType, $caseSensitive = false, $revenue = false)
	{
		Piwik::checkUserHasAdminAccess($idSite);
		$this->checkPatternIsValid($patternType, $pattern);
		$name = $this->checkName($name);
		$pattern = $this->checkPattern($pattern);

		// save in db
		$db = Zend_Registry::get('db');
		$idGoal = $db->fetchOne("SELECT max(idgoal) + 1 
								FROM ".Piwik_Common::prefixTable('goal')." 
								WHERE idsite = ?", $idSite);
		if($idGoal == false)
		{
			$idGoal = 1;
		}
		$db->insert(Piwik_Common::prefixTable('goal'),
					array( 
						'idsite' => $idSite,
						'idgoal' => $idGoal,
						'name' => $name,
						'match_attribute' => $matchAttribute,
						'pattern' => $pattern,
						'pattern_type' => $patternType,
						'case_sensitive' => (int)$caseSensitive,
						'revenue' => (float)$revenue,
						'deleted' => 0,
					));
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);
		return $idGoal;
	}
	
	/**
	 * Updates a Goal
	 * 
	 * @see addGoal() for parameters description
	 * @return void
	 */
	public function updateGoal( $idSite, $idGoal, $name, $matchAttribute, $pattern, $patternType, $caseSensitive = false, $revenue = false)
	{
		Piwik::checkUserHasAdminAccess($idSite);
		$name = $this->checkName($name);
		$pattern = $this->checkPattern($pattern);
		$this->checkPatternIsValid($patternType, $pattern);
		Zend_Registry::get('db')->update( Piwik_Common::prefixTable('goal'), 
					array(
						'name' => $name,
						'match_attribute' => $matchAttribute,
						'pattern' => $pattern,
						'pattern_type' => $patternType,
						'case_sensitive' => $caseSensitive,
						'revenue' => $revenue,
						),
					"idsite = '$idSite' AND idgoal = '$idGoal'"
			);	
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);
	}

	private function checkPatternIsValid($patternType, $pattern)
	{
		if($patternType == 'exact' 
			&& substr($pattern, 0, 4) != 'http')
		{
			throw new Exception(Piwik_TranslateException('Goals_ExceptionInvalidMatchingString', array("http:// or https://", "http://www.yourwebsite.com/newsletter/subscribed.html")));
		}
	}
	
	private function checkName($name)
	{
		return urldecode($name);
	}
	
	private function checkPattern($pattern)
	{
		return urldecode($pattern);
	}
	
	/**
	 * Soft deletes a given Goal.
	 * Stats data in the archives will still be recorded, but not displayed.
	 * 
	 * @param $idSite
	 * @param $idGoal
	 * @return void
	 */
	public function deleteGoal( $idSite, $idGoal )
	{
		Piwik::checkUserHasAdminAccess($idSite);
		Piwik_Query("UPDATE ".Piwik_Common::prefixTable('goal')."
										SET deleted = 1
										WHERE idsite = ? 
											AND idgoal = ?",
									array($idSite, $idGoal));
		Piwik_Query("DELETE FROM ".Piwik_Common::prefixTable("log_conversion")." WHERE idgoal = ?", $idGoal);
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);
	}
	
	/**
	 * Returns Goals data
	 * 
	 * @param $idSite
	 * @param $period
	 * @param $date
	 * @param $idGoal
	 * @param $columns Comma separated list of metrics to fetch: nb_conversions, conversion_rate, revenue
	 * @return Piwik_DataTable
	 */
	public function get( $idSite, $period, $date, $idGoal = false, $columns = array() )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$columns = Piwik::getArrayFromApiParameter($columns);
		
		if(empty($columns))
		{
			$columns = array(
						'nb_conversions',
						'conversion_rate', 
						'revenue',
			);
		}
		$columnsToSelect = array();
		foreach($columns as &$columnName)
		{
			$columnsToSelect[] = Piwik_Goals::getRecordName($columnName, $idGoal);
		}
		$dataTable = $archive->getDataTableFromNumeric($columnsToSelect);
		
		// Rewrite column names as we expect them
		foreach($columnsToSelect as $id => $oldName)
		{
			$dataTable->renameColumn($oldName, $columns[$id]);
		}
		// conversion_rate has an appended % for consistency with other API outputs
		// This filter will work both on DataTable and DataTable_Array
		$dataTable->filter('ColumnCallbackReplace', array('conversion_rate', create_function('$label', 'return $label . "%";')));
		return $dataTable;
	}
	
	protected function getNumeric( $idSite, $period, $date, $toFetch )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getNumeric($toFetch);
		return $dataTable;		
	}

	public function getConversions( $idSite, $period, $date, $idGoal = false )
	{
		return $this->getNumeric( $idSite, $period, $date, Piwik_Goals::getRecordName('nb_conversions', $idGoal));
	}
	
	public function getConversionRate( $idSite, $period, $date, $idGoal = false )
	{
		return $this->getNumeric( $idSite, $period, $date, Piwik_Goals::getRecordName('conversion_rate', $idGoal));
	}
	
	public function getRevenue( $idSite, $period, $date, $idGoal = false )
	{
		return $this->getNumeric( $idSite, $period, $date, Piwik_Goals::getRecordName('revenue', $idGoal));
	}
}
