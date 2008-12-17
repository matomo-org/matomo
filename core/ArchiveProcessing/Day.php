<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Day.php 504 2008-06-01 20:19:28Z matt $
 * 
 * @package Piwik_ArchiveProcessing
 */


/**
 * Handles the archiving process for a day. 
 * The class provides generic helper methods to manipulate data from the DB, 
 * easily create Piwik_DataTable objects from running SELECT ... GROUP BY on the log_visit table.
 * 
 * All the logic of the archiving is done inside the plugins listening to the event 'ArchiveProcessing_Day.compute'
 * 
 * @package Piwik_ArchiveProcessing
 * 
 */
class Piwik_ArchiveProcessing_Day extends Piwik_ArchiveProcessing
{
	/**
	 * If the archive has at least 1 visit, this is set to true.
	 *
	 * @var bool
	 */
	public $isThereSomeVisits = false;
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->db = Zend_Registry::get('db');
	}
	
	/**
	 * Main method to process logs for a day. The only logic done here is computing the number of visits, actions, etc.
	 * All the other reports are computed inside plugins listening to the event 'ArchiveProcessing_Day.compute'.
	 * See some of the plugins for an example eg. 'Provider'
	 * 
	 * @return void
	 */
	protected function compute()
	{
		$query = "SELECT 	count(distinct visitor_idcookie) as nb_uniq_visitors, 
							count(*) as nb_visits,
							sum(visit_total_actions) as nb_actions, 
							max(visit_total_actions) as max_actions, 
							sum(visit_total_time) as sum_visit_length,
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count,
							sum(case visit_goal_converted when 1 then 1 else 0 end) as nb_visits_converted
					FROM ".$this->logTable."
					WHERE visit_server_date = ?
						AND idsite = ?
					GROUP BY visit_server_date
					ORDER BY NULL
				 ";
		$row = $this->db->fetchRow($query, array($this->strDateStart,$this->idsite ) );
		
		if($row === false)
		{
			return;
		}
		$this->isThereSomeVisits = true;
	
		foreach($row as $name => $value)
		{
			$record = new Piwik_ArchiveProcessing_Record_Numeric($name, $value);
		}
		$this->setNumberOfVisits($row['nb_visits']);
		$this->setNumberOfVisitsConverted($row['nb_visits_converted']);
		Piwik_PostEvent('ArchiveProcessing_Day.compute', $this);
	}
	
	/**
	 * Called at the end of the archiving process.
	 * Does some cleaning job in the database.
	 * 
	 * @return void
	 */
	protected function postCompute()
	{
		parent::postCompute();
		
		//TODO should be done in a different asynchronous job
		if(rand(0, 15) == 5)
		{
			// we delete out of date records
			// = archives that for day N computed on day N (means they are only partial)
			$blobTable = $this->tableArchiveBlob->getTableName();
			$numericTable = $this->tableArchiveNumeric->getTableName();
			
			$query = "/* SHARDING_ID_SITE = ".$this->idsite." */ 	DELETE 
						FROM %s
						WHERE period = ? 
							AND date1 = DATE(ts_archived)
							AND DATE(ts_archived) <> CURRENT_DATE()
						";
			
			Zend_Registry::get('db')->query(sprintf($query, $blobTable), $this->periodId);
			Zend_Registry::get('db')->query(sprintf($query, $numericTable), $this->periodId);
		}
	}
	
	/**
	 * Helper function that returns a DataTable containing the $select fields / value pairs.
	 * IMPORTANT: The $select must return only one row!!
	 * 
	 * Example $select = "count(distinct( config_os )) as countDistinctOs, 
	 * 						sum( config_flash ) / count(distinct(idvisit)) as percentFlash "
	 * 		   $labelCount = "test_column_name"
	 * will return a dataTable that looks like
	 * 		label  				test_column_name  	
	 * 		CountDistinctOs 	9 	
	 * 		PercentFlash 		0.5676
	 * 						
	 *
	 * @param string $select 
	 * @param string $labelCount
	 * @return Piwik_DataTable
	 */
	public function getSimpleDataTableFromSelect($select, $labelCount)
	{
		$query = "SELECT $select 
			 	FROM ".$this->logTable." 
			 	WHERE visit_server_date = ?
			 		AND idsite = ?";
		$data = $this->db->fetchRow($query, array( $this->strDateStart, $this->idsite ));
		
		foreach($data as $label => &$count)
		{
			$count = array($labelCount => $count);
		}
		$table = new Piwik_DataTable;
		$table->addRowsFromArrayWithIndexLabel($data);
		return $table;
	}
	
	public function getDataTableFromArray( $array )
	{
		$table = new Piwik_DataTable;
		$table->addRowsFromArrayWithIndexLabel($array);
		return $table;
	}
	
	/**
	 * Output:
	 * 		array(
	 * 			LABEL => array(
	 * 						Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> 0, 
	 *						Piwik_Archive::INDEX_NB_VISITS 			=> 0
	 *					),
	 *			LABEL2 => array(
	 *					[...]
	 *					)
	 * 		)
 	 *
	 * Helper function that returns an array with common statistics for a given database field distinct values.
	 * 
	 * The statistics returned are:
	 *  - number of unique visitors
	 *  - number of visits
	 *  - number of actions
	 *  - maximum number of action for a visit
	 *  - sum of the visits' length in sec
	 *  - count of bouncing visits (visits with one page view)
	 * 
	 * For example if $label = 'config_os' it will return the statistics for every distinct Operating systems
	 * The returned array will have a row per distinct operating systems, 
	 * and a column per stat (nb of visits, max  actions, etc)
	 * 
	 * 'label'	Piwik_Archive::INDEX_NB_UNIQ_VISITORS	Piwik_Archive::INDEX_NB_VISITS	etc.	
	 * Linux	27	66	...
	 * Windows XP	12	...	
	 * Mac OS	15	36	...
	 * 
	 * @param string $label Table log_visit field name to be use to compute common stats
	 * @return array
	 */
	public function getArrayInterestForLabel($label)
	{
		$query = "SELECT 	$label as label,
							count(distinct visitor_idcookie) as nb_uniq_visitors, 
							count(*) as nb_visits,
							sum(visit_total_actions) as nb_actions, 
							max(visit_total_actions) as max_actions, 
							sum(visit_total_time) as sum_visit_length,
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count,
							sum(case visit_goal_converted when 1 then 1 else 0 end) as nb_visits_converted
				FROM ".$this->logTable."
				WHERE visit_server_date = ?
					AND idsite = ?
				GROUP BY label";
		$query = $this->db->query($query, array( $this->strDateStart, $this->idsite ) );

		$interest = array();
		while($row = $query->fetch())
		{
			if(!isset($interest[$row['label']])) $interest[$row['label']]= $this->getNewInterestRow();
			$this->updateInterestStats( $row, $interest[$row['label']]);
		}
		return $interest;
	}
	
	/**
	 * Generates a dataTable given a multidimensional PHP array that associates LABELS to Piwik_DataTableRows
	 * This is used for the "Actions" DataTable, where a line is the aggregate of all the subtables
	 * Example: the category /blog has 3 visits because it has /blog/index (2 visits) + /blog/about (1 visit) 
	 *
	 * @param array $table
	 * @return Piwik_DataTable
	 */
	static public function generateDataTable( $table )
	{
		$dataTableToReturn = new Piwik_DataTable;
		
		foreach($table as $label => $maybeDatatableRow)
		{
			// case the aInfo is a subtable-like array
			// it means that we have to go recursively and process it
			// then we build the row that is an aggregate of all the children
			// and we associate this row to the subtable
			if( !($maybeDatatableRow instanceof Piwik_DataTable_Row) )
			{
				$subTable = self::generateDataTable($maybeDatatableRow);
				$row = new Piwik_DataTable_Row_DataTableSummary( $subTable );
				$row->addSubtable($subTable);
				$row->setColumn('label', $label);
			}
			// if aInfo is a simple Row we build it
			else
			{
				$row = $maybeDatatableRow;
			}
			
			$dataTableToReturn->addRow($row);
		}
		
		return $dataTableToReturn;
	}
	
	/**
	 * Helper function that returns the serialized DataTable of the given PHP array.
	 * The array must have the format of Piwik_DataTable::addRowsFromArrayWithIndexLabel()
	 * Example: 	array (
	 * 	 				LABEL => array(col1 => X, col2 => Y),
	 * 	 				LABEL2 => array(col1 => X, col2 => Y),
	 * 				)
	 * 
	 * @param array $array at the given format
	 * @return array Array with one element: the serialized data table string
	 */
	public function getDataTableSerialized( $array )
	{
		$table = new Piwik_DataTable;
		$table->addRowsFromArrayWithIndexLabel($array );
		$toReturn = $table->getSerialized();
		return $toReturn;
	}
	
	
	/**
	 * Helper function that returns the multiple serialized DataTable of the given PHP array.
	 * The DataTable here associates a subtable to every row of the level 0 array.
	 * This is used for example for search engines. 
	 * Every search engine (level 0) has a subtable containing the keywords.
	 * 
	 * The $arrayLevel0 must have the format 
	 * Example: 	array (
	 * 					// Yahoo.com => array( kwd1 => stats, kwd2 => stats )
	 * 	 				LABEL => array(col1 => X, col2 => Y),
	 * 	 				LABEL2 => array(col1 => X, col2 => Y),
	 * 				)
	 * 
	 * The $subArrayLevel1ByKey must have the format
	 * Example: 	array(
	 * 					// Yahoo.com => array( stats )
	 * 					LABEL => #Piwik_DataTable_ForLABEL,
	 * 					LABEL2 => #Piwik_DataTable_ForLABEL2,
	 * 				)
	 * 
	 * 
	 * @param array $arrayLevel0 
	 * @param array of Piwik_DataTable $subArrayLevel1ByKey 
	 * @return array Array with N elements: the strings of the datatable serialized 
	 */
	public function getDataTableWithSubtablesFromArraysIndexedByLabel( $arrayLevel0, $subArrayLevel1ByKey )
	{
		$tablesByLabel = array();
		foreach($arrayLevel0 as $label => $aAllRowsForThisLabel)
		{
			$table = new Piwik_DataTable;
			$table->addRowsFromArrayWithIndexLabel($aAllRowsForThisLabel);
			$tablesByLabel[$label] = $table;
		}
		$parentTableLevel0 = new Piwik_DataTable;
		$parentTableLevel0->addRowsFromArrayWithIndexLabel($subArrayLevel1ByKey, $tablesByLabel);

		return $parentTableLevel0;
	}
	
	/**
	 * Returns an empty row containing default values for the common stat
	 *
	 * @return array
	 */
	public function getNewInterestRow()
	{
		return array(	Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> 0, 
						Piwik_Archive::INDEX_NB_VISITS 			=> 0, 
						Piwik_Archive::INDEX_NB_ACTIONS 		=> 0, 
						Piwik_Archive::INDEX_MAX_ACTIONS 		=> 0, 
						Piwik_Archive::INDEX_SUM_VISIT_LENGTH 	=> 0, 
						Piwik_Archive::INDEX_BOUNCE_COUNT 		=> 0,
						Piwik_Archive::INDEX_NB_VISITS_CONVERTED=> 0,
						);
	}
	
	
	/**
	 * Returns a Piwik_DataTable_Row containing default values for common stat, 
	 * plus a column 'label' with the value $label
	 *
	 * @param string $label
	 * @return Piwik_DataTable_Row
	 */
	public function getNewInterestRowLabeled( $label )
	{
		return new Piwik_DataTable_Row(
				array( 
					Piwik_DataTable_Row::COLUMNS => 		array(	'label' => $label) 
															+ $this->getNewInterestRow()
					)
				); 
	}
	
	/**
	 * Adds the given row $newRowToAdd to the existing  $oldRowToUpdate passed by reference
	 *
	 * The rows are php arrays Name => value
	 * 
	 * @param array $newRowToAdd
	 * @param array $oldRowToUpdate
	 */
	public function updateInterestStats( $newRowToAdd, &$oldRowToUpdate)
	{
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_UNIQ_VISITORS]		+= $newRowToAdd['nb_uniq_visitors'];
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_VISITS] 			+= $newRowToAdd['nb_visits'];
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_ACTIONS] 			+= $newRowToAdd['nb_actions'];
		$oldRowToUpdate[Piwik_Archive::INDEX_MAX_ACTIONS] 		 	= (float)max($newRowToAdd['max_actions'], $oldRowToUpdate[Piwik_Archive::INDEX_MAX_ACTIONS]);
		$oldRowToUpdate[Piwik_Archive::INDEX_SUM_VISIT_LENGTH]		+= $newRowToAdd['sum_visit_length'];
		$oldRowToUpdate[Piwik_Archive::INDEX_BOUNCE_COUNT] 			+= $newRowToAdd['bounce_count'];
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_VISITS_CONVERTED] 	+= $newRowToAdd['nb_visits_converted'];
	}
	
	//TODO comment
	public function queryConversionsBySegment($segments = '')
	{
		if(!empty($segments))
		{
			$segments = ", ". $segments;
		}
		$query = "SELECT idgoal,
						count(*) as nb_conversions,
						sum(revenue) as revenue
						$segments
			 	FROM ".$this->logConversionTable."
			 	WHERE visit_server_date = ?
			 		AND idsite = ?
			 	GROUP BY idgoal $segments";
		$query = $this->db->query($query, array( $this->strDateStart, $this->idsite ));
		return $query;
	}
	
	public function queryConversionsBySingleSegment($segment)
	{
		$query = "SELECT idgoal,
						count(*) as nb_conversions,
						sum(revenue) as revenue,
						$segment as label
			 	FROM ".$this->logConversionTable."
			 	WHERE visit_server_date = ?
			 		AND idsite = ?
			 	GROUP BY idgoal, label";
		$query = $this->db->query($query, array( $this->strDateStart, $this->idsite ));
		return $query;
	}
	
	/**
	 * Input: 
	 * 		array( 
	 * 			LABEL  => array( Piwik_Archive::INDEX_NB_VISITS => X, 
	 * 							 Piwik_Archive::INDEX_GOALS => array(
	 * 								idgoal1 => array( [...] ), 
	 * 								idgoal2 => array( [...] ),
	 * 							),
	 * 							[...] ),
	 * 			LABEL2 => array( Piwik_Archive::INDEX_NB_VISITS => Y, [...] )
	 * 			);
	 * 
	 * Output:
	 * 		array(
	 * 			LABEL  => array( Piwik_Archive::INDEX_NB_VISITS => X, 
	 * 							 
	 * 							 Piwik_Archive::INDEX_GOALS => array(
	 * 								idgoal1 => array( [...] ), 
	 * 								idgoal2 => array( [...] ),
	 * 							),
	 * 							[...] ),
	 * 			LABEL2 => array( Piwik_Archive::INDEX_NB_VISITS => Y, [...] )
	 * 			);
	 * 		)
	 * @param array by reference, will be modified
	 * @return void (array by reference is modified)
	 */
	function enrichConversionsByLabelArray(&$interestByLabel)
	{
		foreach($interestByLabel as $label => &$values)
		{
			if(isset($values[Piwik_Archive::INDEX_GOALS]))
			{
				$revenue = $conversions = 0;
				foreach($values[Piwik_Archive::INDEX_GOALS] as $idgoal => $goalValues)
				{
					$revenue += $goalValues[Piwik_Archive::INDEX_GOAL_REVENUE];
					$conversions += $goalValues[Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS];
				}
				$values[Piwik_Archive::INDEX_NB_CONVERSIONS] = $conversions;
				$values[Piwik_Archive::INDEX_REVENUE] = $revenue;
			}
		}
	}

	/**
	 * @param array $interestByLabelAndSubLabel
	 * @return void (array by reference is modified)
	 */
	function enrichConversionsByLabelArrayHasTwoLevels(&$interestByLabelAndSubLabel)
	{
		foreach($interestByLabelAndSubLabel as $mainLabel => &$interestBySubLabel)
		{
			$this->enrichConversionsByLabelArray($interestBySubLabel);
		}
	}

	function updateGoalStats($newRowToAdd, &$oldRowToUpdate)
	{
		$oldRowToUpdate[Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS]	+= $newRowToAdd['nb_conversions'];
		$oldRowToUpdate[Piwik_Archive::INDEX_GOAL_REVENUE] 			+= $newRowToAdd['revenue'];
	}
	
	function getNewGoalRow()
	{
		return array(	Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS 	=> 0, 
						Piwik_Archive::INDEX_GOAL_REVENUE 			=> 0, 
					);
	}
	
	function getGoalRowFromQueryRow($queryRow)
	{
		return array(	Piwik_Archive::INDEX_GOAL_NB_CONVERSIONS 	=> $queryRow['nb_conversions'], 
						Piwik_Archive::INDEX_GOAL_REVENUE 			=> $queryRow['revenue'], 
					);
	}
}


