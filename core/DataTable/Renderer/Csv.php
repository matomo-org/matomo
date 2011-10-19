<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * CSV export
 * 
 * When rendered using the default settings, a CSV report has the following characteristics:
 * The first record contains headers for all the columns in the report.
 * All rows have the same number of columns.
 * The default field delimiter string is a comma (,).
 * Formatting and layout are ignored.
 * 
 * Note that CSV output doesn't handle recursive dataTable. It will output only the first parent level of the tables.
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Renderer_Csv extends Piwik_DataTable_Renderer
{
	/**
	 * Column separator
	 *
	 * @var string
	 */
	public $separator = ",";
	
	/**
	 * Line end 
	 *
	 * @var string
	 */
	public $lineEnd = "\n";
	
	/**
	 * 'metadata' columns will be exported, prefixed by 'metadata_'
	 *
	 * @var bool
	 */
	public $exportMetadata = true;
	
	/**
	 * Converts the content to unicode so that UTF8 characters (eg. chinese) can be imported in Excel
	 *
	 * @var bool
	 */
	public $convertToUnicode = true;
	
	/**
	 * Whether to include inner nodes in the export or not
	 * (only works if expanded=1)
	 * 
	 * @var bool
	 */
	public $includeInnerNodes = false;
	
	/**
	 * Whether to translate column names (i.e. metric names) or not
	 * 
	 * @var bool
	 */
	public $translateColumnNames = false;
	
	/**
	 * Separator for building recursive labels (or paths)
	 * 
	 * @var string
	 */
	public $recursiveLabelSeparator = ' - ';
	
	/**
	 * The API method that has returned the data that should be rendered
	 * 
	 * @var string
	 */
	public $apiMethod = false;
	
	/**
	 * The current idSite
	 * 
	 * @var int
	 */
	public $idSite = 'all';
	
	/**
	 * idSubtable will be exported in a column called 'idsubdatatable'
	 *
	 * @var bool
	 */
	public $exportIdSubtable = true;
	
	public function render()
	{
		$str = $this->renderTable($this->table);
		if(empty($str))
		{
			return 'No data available';
		}

		self::renderHeader();

		if($this->convertToUnicode 
			&& function_exists('mb_convert_encoding'))
		{
			$str = chr(255) . chr(254) . mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
		}
		return $str;
	}
	
	function renderException()
	{
		$exceptionMessage = self::renderHtmlEntities($this->exception->getMessage());
		return 'Error: '.$exceptionMessage;
	}
	
	public function setConvertToUnicode($bool)
	{
		$this->convertToUnicode = $bool;
	}
	
	public function setIncludeInnerNodes($bool)
	{
		$this->includeInnerNodes = $bool;
	}
	
	public function setTranslateColumnNames($bool)
	{
		$this->translateColumnNames = $bool;
	}
	
	public function setSeparator($separator)
	{
		$this->separator = $separator;
	}
	
	public function setRecursiveLabelSeparator($separator)
	{
		$this->recursiveLabelSeparator = $separator;
	}
	
	public function setApiMethod($method)
	{
		$this->apiMethod = $method;
	}
	
	public function setIdSite($idSite)
	{
		$this->idSite = $idSite;
	}
	
	protected function renderTable($table)
	{
		if($table instanceof Piwik_DataTable_Array)
		{
			$str = $header = '';
			$prefixColumns = $table->getKeyName() . $this->separator;
			foreach($table->getArray() as $currentLinePrefix => $dataTable)
			{
				$returned = explode("\n",$this->renderTable($dataTable));
				// get the columns names
				if(empty($header))
				{
					$header = $returned[0];
				}
				$returned = array_slice($returned,1);
				
				// case empty datatable we dont print anything in the CSV export
				// when in xml we would output <result date="2008-01-15" />
				if(!empty($returned))
				{
					foreach($returned as &$row)
					{
						$row = $currentLinePrefix . $this->separator . $row;
					}
					$str .= "\n" .  implode("\n", $returned);
				}
			}
			if(!empty($header))
			{
				$str = $prefixColumns . $header . $str;
			}
		}
		else
		{
			$str = $this->renderDataTable($table);
		}
		return $str;
	}
	
	protected function renderDataTable( $table, $returnRowArray = false, $labelPrefix = false )
	{	
		if($table instanceof Piwik_DataTable_Simple)
		{
			$row = $table->getFirstRow();
			if($row !== false)
			{
				$columnNameToValue = $row->getColumns();
				if(count($columnNameToValue) == 1)
				{
					$value = array_values($columnNameToValue);
					$str = 'value' . $this->lineEnd . $this->formatValue($value[0]);
					return $str;
				}
			}
		}
		$csv = $allColumns = array();
		foreach($table->getRows() as $row)
		{
			$csvRow = array();
			
			$columns = $row->getColumns();
			foreach($columns as $name => $value)
			{
				//goals => array( 'idgoal=1' =>array(..), 'idgoal=2' => array(..))
				if(is_array($value))
				{
					foreach($value as $key => $subValues)
					{
						if(is_array($subValues))
						{
							foreach($subValues as $subKey => $subValue)
							{
								// goals_idgoal=1
								$columnName = $name . "_" . $key . "_" . $subKey;
								$allColumns[$columnName] = true;
								$csvRow[$columnName] = $subValue;
							}
						}
					}
				}
				else
				{
					$allColumns[$name] = true;
					if ($name == 'label' && $labelPrefix)
					{
						if (substr($value, 0, 1) == '/' && $this->recursiveLabelSeparator == '/')
						{
							$value = substr($value, 1);
						}
						$csvRow[$name] = $labelPrefix.$value;
					}
					else
					{
						$csvRow[$name] = $value;
					}
				}
			}

			if($this->exportMetadata)
			{
				$metadata = $row->getMetadata();
				foreach($metadata as $name => $value)
				{
					//if a metadata and a column have the same name make sure they dont overwrite
					if($this->translateColumnNames)
					{
						$name = Piwik_Translate('General_Metadata').': '.$name;
					}
					else
					{
						$name = 'metadata_'.$name;
					}
					
					$allColumns[$name] = true;
					$csvRow[$name] = $value;
				}
			}		
			
			if($this->exportIdSubtable)
			{
				$idsubdatatable = $row->getIdSubDataTable();
				if($idsubdatatable !== false
					&& $this->hideIdSubDatatable === false)
				{
					$csvRow['idsubdatatable'] = $idsubdatatable;
				}
			}
			
			if($this->isRenderSubtables()
				&& $row->getIdSubDataTable() !== null)
			{
				if($this->includeInnerNodes)
				{
					$csv[] = $csvRow;
				}
				try{
					$idSubTable = $row->getIdSubDataTable();
					$subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
					$prefix = isset($csvRow['label']) ? $csvRow['label'].$this->recursiveLabelSeparator : '';
					$csv = array_merge($csv, $this->renderDataTable($subTable, true, $prefix));
				} catch (Exception $e) {
					// the subtables are not loaded we don't do anything 
				}
			}
			else
			{
				$csv[] = $csvRow;
			}
		}
		
		// now we make sure that all the rows in the CSV array have all the columns
		foreach($csv as &$row)
		{
			foreach($allColumns as $columnName => $true)
			{
				if(!isset($row[$columnName]))
				{
					$row[$columnName] = '';
				}
			}
		}
		
		// return the array of rows instead of the formatted string
		// this is used for recursive calls
		if($returnRowArray)
		{
			return $csv;
		}
		
		$str = '';		
		
		// specific case, we have only one column and this column wasn't named properly (indexed by a number)
		// we don't print anything in the CSV file => an empty line
		if(sizeof($allColumns) == 1 
			&& reset($allColumns) 
			&& !is_string(key($allColumns)))
		{
			$str .= '';
		}
		else
		{
			// render row names
			$keys = array_keys($allColumns);
			if ($this->translateColumnNames)
			{
				$keys = $this->translateColumnNames($keys);
			}
			$str .= implode($this->separator, $keys);
			$str .= $this->lineEnd;
		}
		
		// we render the CSV
		foreach($csv as $theRow)
		{
			$rowStr = '';
			foreach($allColumns as $columnName => $true)
			{
				$rowStr .= $this->formatValue($theRow[$columnName]) . $this->separator;
			}
			// remove the last separator
			$rowStr = substr_replace($rowStr,"",-strlen($this->separator));
			$str .= $rowStr . $this->lineEnd;
		}
		$str = substr($str, 0, -strlen($this->lineEnd));
		return $str;
	}
	
	protected function translateColumnNames($names)
	{
		if (!$this->apiMethod)
		{
			return $names;
		}
		
		list($apiModule, $apiAction) = explode('.', $this->apiMethod);
		
		if(!$apiModule || !$apiAction)
		{
			return $names;
		}
		
		$api = Piwik_API_API::getInstance();
		$meta = $api->getMetadata($this->idSite, $apiModule, $apiAction);
		if (is_array($meta[0]))
		{
			$meta = $meta[0];
		}
		
		$t = array_merge($api->getDefaultMetrics(), $api->getDefaultProcessedMetrics(), array(
			'label' => 'General_ColumnLabel',
			'avg_time_on_page' => 'General_ColumnAverageTimeOnPage',
			'sum_time_spent' => 'General_ColumnSumVisitLength',
			'sum_visit_length' => 'General_ColumnSumVisitLength',
			'bounce_count' => 'General_ColumnBounces',
			'max_actions' => 'General_ColumnMaxActions',
			'nb_visits_converted' => 'General_ColumnVisitsWithConversions',
			'nb_conversions' => 'Goals_ColumnConversions',
			'revenue' => 'Goals_ColumnRevenue',
			'nb_hits' => 'General_ColumnPageviews',
			'entry_nb_visits' => 'General_ColumnEntrances',
			'entry_nb_uniq_visitors' => 'General_ColumnUniqueEntrances',
			'exit_nb_visits' => 'General_ColumnExits',
			'exit_nb_uniq_visitors' => 'General_ColumnUniqueExits',
			'entry_bounce_count' => 'General_ColumnBounces',
			'exit_bounce_count' => 'General_ColumnBounces',
			'exit_rate' => 'General_ColumnExitRate'
		));
		
		$t = array_map('Piwik_Translate', $t);
		
		$dailySum = ' ('.Piwik_Translate('General_DailySum').')';
		$afterEntry = ' '.Piwik_Translate('General_AfterEntry');
		
		$t['sum_daily_nb_uniq_visitors'] = Piwik_Translate('General_ColumnNbUniqVisitors').$dailySum;
		$t['sum_daily_entry_nb_uniq_visitors'] = Piwik_Translate('General_ColumnUniqueEntrances').$dailySum;
		$t['sum_daily_exit_nb_uniq_visitors'] = Piwik_Translate('General_ColumnUniqueExits').$dailySum;
		$t['entry_nb_actions'] = Piwik_Translate('General_ColumnNbActions').$afterEntry;
		$t['entry_sum_visit_length'] = Piwik_Translate('General_ColumnSumVisitLength').$afterEntry;
		
		foreach (array('metrics', 'processedMetrics', 'metricsGoal', 'processedMetricsGoal') as $index)
		{
			if (isset($meta[$index]) && is_array($meta[$index]))
			{
				$t = array_merge($t, $meta[$index]);
			}
		}
		
		foreach ($names as &$name)
		{
			if (isset($t[$name]))
			$name = $t[$name];
		}
		
		return $names;
	}

	protected function formatValue($value)
	{
		if(is_string($value)
			&& !is_numeric($value)) 
		{
			$value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
		}
		elseif($value === false)
		{
			$value = 0;
		}
		if(is_string($value)
			&& (strpos($value, '"') !== false 
				|| strpos($value, $this->separator) !== false )
		)
		{
			$value = '"'. str_replace('"', '""', $value). '"';
		}
		return $value;
	}
	
	protected static function renderHeader()
	{
		// silent fail otherwise unit tests fail
		@header('Content-Type: application/vnd.ms-excel');
		@header('Content-Disposition: attachment; filename=piwik-report-export.csv');
		Piwik::overrideCacheControlHeaders();
	}
}
