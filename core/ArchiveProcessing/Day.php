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
 * The class provides generic methods to manipulate data from the DB, easily create Piwik_DataTable objects.
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
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count 
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
		
		// we delete out of date records
		// = archives that for day N computed on day N (means they are only partial)
		$blobTable = $this->tableArchiveBlob->getTableName();
		$numericTable = $this->tableArchiveNumeric->getTableName();
		
		$query = "	DELETE 
					FROM %s
					WHERE period = ? 
						AND date1 = DATE(ts_archived)
						AND DATE(ts_archived) <> CURRENT_DATE()
					";
		
		Zend_Registry::get('db')->query(sprintf($query, $blobTable), $this->periodId);
		Zend_Registry::get('db')->query(sprintf($query, $numericTable), $this->periodId);
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
		$table->loadFromArrayLabelIsKey($data);
		return $table;
	}
	
	/**
	 * Helper function that returns common statistics for a given database field distinct values.
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
	 * The returned DataTable will have a row per distinct operating systems, 
	 *  and a column per stat (nb of visits, max  actions, etc)
	 * 
	 * label	nb_uniq_visitors	nb_visits	nb_actions	max_actions	sum_visit_length	bounce_count	
	 * Linux	27	66	66	1	660	66	
	 * Windows XP	12	39	39	1	390	39	
	 * Mac OS	15	36	36	1	360	36	
	 * 
	 * @param string $label Table log_visit field name to be use to compute common stats
	 * @return Piwik_DataTable
	 */
	public function getDataTableInterestForLabel( $label )
	{
		$query = "SELECT 	$label as label,
							count(distinct visitor_idcookie) as nb_uniq_visitors, 
							count(*) as nb_visits,
							sum(visit_total_actions) as nb_actions, 
							max(visit_total_actions) as max_actions, 
							sum(visit_total_time) as sum_visit_length,
							sum(case visit_total_actions when 1 then 1 else 0 end) as bounce_count
				FROM ".$this->logTable."
				WHERE visit_server_date = ?
					AND idsite = ?
				GROUP BY label";

		$query = $this->db->query($query, array( $this->strDateStart, $this->idsite ) );

		$interest = array();
		while($rowBefore = $query->fetch())
		{
			$row = array(
				Piwik_Archive::INDEX_NB_UNIQ_VISITORS 	=> $rowBefore['nb_uniq_visitors'], 
				Piwik_Archive::INDEX_NB_VISITS 			=> $rowBefore['nb_visits'], 
				Piwik_Archive::INDEX_NB_ACTIONS 		=> $rowBefore['nb_actions'], 
				Piwik_Archive::INDEX_MAX_ACTIONS 		=> $rowBefore['max_actions'], 
				Piwik_Archive::INDEX_SUM_VISIT_LENGTH 	=> $rowBefore['sum_visit_length'], 
				Piwik_Archive::INDEX_BOUNCE_COUNT 		=> $rowBefore['bounce_count'],
				'label'									=> $rowBefore['label']
				);
				
			if(!isset($interest[$row['label']])) $interest[$row['label']]= $this->getNewInterestRow();
			$this->updateInterestStats( $row, $interest[$row['label']]);
		}

		$table = new Piwik_DataTable;
		$table->loadFromArrayLabelIsKey($interest);
		return $table;
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
	 * The array must have the format of Piwik_DataTable::loadFromArrayLabelIsKey()
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
		$table->loadFromArrayLabelIsKey($array );
		$toReturn = $table->getSerialized();
		return $toReturn;
	}
	
	
	/**
	 * Helper function that returns the multiple serialized DataTable of the given PHP array.
	 * The DataTable here associates a subtable to every row of the level 0 array.
	 * This is used for example for search engines. Every search engine (level 0) has a subtable containing the
	 * keywords.
	 * 
	 * The $arrayLevel0 must have the format 
	 * Example: 	array (
	 * 	 				LABEL => array(col1 => X, col2 => Y),
	 * 	 				LABEL2 => array(col1 => X, col2 => Y),
	 * 				)
	 * 
	 * The $subArrayLevel1ByKey must have the format
	 * Example: 	array(
	 * 					LABEL => #Piwik_DataTable_ForLABEL,
	 * 					LABEL2 => #Piwik_DataTable_ForLABEL2,
	 * 				)
	 * 
	 * 
	 * @param array $arrayLevel0 
	 * @param array of Piwik_DataTable $subArrayLevel1ByKey 
	 * @return array Array with N elements: the strings of the datatable serialized 
	 */
	public function getDataTablesSerialized( $arrayLevel0, $subArrayLevel1ByKey, $maximumRowsInDataTableLevelZero = null, $maximumRowsInSubDataTable = null)
	{
		$tablesByLabel = array();

		foreach($arrayLevel0 as $label => $aAllRowsForThisLabel)
		{
			$table = new Piwik_DataTable;
			$table->loadFromArrayLabelIsKey($aAllRowsForThisLabel);
			$tablesByLabel[$label] = $table;
		}
		$parentTableLevel0 = new Piwik_DataTable;
		$parentTableLevel0->loadFromArrayLabelIsKey($subArrayLevel1ByKey, $tablesByLabel);

		$toReturn = $parentTableLevel0->getSerialized($maximumRowsInDataTableLevelZero, $maximumRowsInSubDataTable);
		return $toReturn;
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
						Piwik_Archive::INDEX_BOUNCE_COUNT 		=> 0
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
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_UNIQ_VISITORS]	+= $newRowToAdd[Piwik_Archive::INDEX_NB_UNIQ_VISITORS];
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_VISITS] 		+= $newRowToAdd[Piwik_Archive::INDEX_NB_VISITS];
		$oldRowToUpdate[Piwik_Archive::INDEX_NB_ACTIONS] 		+= $newRowToAdd[Piwik_Archive::INDEX_NB_ACTIONS];
		$oldRowToUpdate[Piwik_Archive::INDEX_MAX_ACTIONS] 		 = (float)max($newRowToAdd[Piwik_Archive::INDEX_MAX_ACTIONS], $oldRowToUpdate[Piwik_Archive::INDEX_MAX_ACTIONS]);
		$oldRowToUpdate[Piwik_Archive::INDEX_SUM_VISIT_LENGTH]	+= $newRowToAdd[Piwik_Archive::INDEX_SUM_VISIT_LENGTH];
		$oldRowToUpdate[Piwik_Archive::INDEX_BOUNCE_COUNT] 		+= $newRowToAdd[Piwik_Archive::INDEX_BOUNCE_COUNT];
	}
}


