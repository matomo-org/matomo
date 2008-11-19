<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Html.php 581 2008-07-27 23:07:52Z matt $
 * 
 * @package Piwik_ViewDataTable
 */

/**
 * 
 * Outputs an AJAX Table for a given DataTable.
 * 
 * Reads the requested DataTable from the API.
 * 
 * @package Piwik_ViewDataTable
 *
 */
class Piwik_ViewDataTable_Html extends Piwik_ViewDataTable
{
	/**
	 * Array of columns names to display
	 *
	 * @var array
	 */
	protected $columnsToDisplay = array();
	
	/**
	 * Array of columns names translations
	 *
	 * @var array
	 */
	protected $columnsTranslations = array();

	/**
	 * PHP array conversion of the Piwik_DataTable 
	 *
	 * @var array
	 */
	public $arrayDataTable; // phpArray
	
	/**
	 * @see Piwik_ViewDataTable::init()
	 */
	function init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod,						
						$actionToLoadTheSubTable = null )
	{
		parent::init($currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod,						
						$actionToLoadTheSubTable);
		$this->dataTableTemplate = 'CoreHome/templates/datatable.tpl';
		
		$this->variablesDefault['enable_sort'] = '1';
		$this->variablesDefault['showAllColumns'] = '0';
		if(Piwik_Common::getRequestVar('showAllColumns', '0', 'string') == '1')
		{
			$this->setShowAllColumns();
		}
		
		// load general columns translations
		$this->setColumnTranslation('nb_visits', Piwik_Translate('General_ColumnNbVisits'));
		$this->setColumnTranslation('label', Piwik_Translate('General_ColumnLabel'));
		$this->setColumnTranslation('nb_uniq_visitors', Piwik_Translate('General_ColumnNbUniqVisitors'));
	}
	
	/**
	 * @see Piwik_ViewDataTable::main()
	 *
	 */
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
		
		$this->loadDataTableFromAPI();
		
		if($this->getShowAllColumns())
		{
			$this->setColumnsToDisplay(array('label', 
											'nb_visits', 
											'nb_uniq_visitors', 
											'nb_actions_per_visit', 
											'avg_time_on_site', 
											'bounce_rate'));
			$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($this->dataTable, 'avg_time_on_site', create_function('$averageTimeOnSite', 'return Piwik::getPrettyTimeFromSeconds($averageTimeOnSite);'));
			$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace($this->dataTable, 'bounce_rate', create_function('$bounceRate', 'return $bounceRate."%";'));
			$this->setColumnTranslation('nb_actions_per_visit', Piwik_Translate('General_ColumnActionsPerVisit'));
			$this->setColumnTranslation('avg_time_on_site', Piwik_Translate('General_ColumnAvgTimeOnSite'));
			$this->setColumnTranslation('bounce_rate', Piwik_Translate('General_ColumnBounceRate'));
		}
		
		$this->view = $this->buildView();
	}

	/**
	 * @return Piwik_View with all data set
	 */
	protected function buildView()
	{
		$view = new Piwik_View($this->dataTableTemplate);
		
		$phpArray = $this->getPHPArrayFromDataTable();
		
		$view->arrayDataTable 	= $phpArray;
		$view->method = $this->method;
		
		$columns = $this->getColumnsToDisplay($phpArray);
		$view->dataTableColumns = $columns;
		
		$nbColumns = count($columns);
		// case no data in the array we use the number of columns set to be displayed 
		if($nbColumns == 0)
		{
			$nbColumns = count($this->columnsToDisplay);
		}
		
		$view->nbColumns = $nbColumns;
		
		$view->id = $this->getUniqIdTable();
		$view->javascriptVariablesToSet = $this->getJavascriptVariablesToSet();
		$view->showFooter = $this->getShowFooter();
		return $view;
	}
	
	/**
	 * Returns friendly php array from the Piwik_DataTable
	 * @see Piwik_DataTable_Renderer_Php
	 * @return array
	 */
	protected function getPHPArrayFromDataTable()
	{		
		$renderer = Piwik_DataTable_Renderer::factory('php');
		$renderer->setTable($this->dataTable);
		$renderer->setSerialize( false );
		// we get the php array from the datatable but conserving the original datatable format, 
		// ie. rows 'columns', 'metadata' and 'idsubdatatable'
		$phpArray = $renderer->originalRender();
		return $phpArray;
	}	
	
	/**
	 * Sets the columns that will be displayed in the HTML output
	 * By default all columns are displayed ($columnsNames = array() will display all columns)
	 * 
	 * @param array $columnsNames Array of column names eg. array('nb_visits','nb_hits')
	 */
	public function setColumnsToDisplay( $columnsNames)
	{
		$this->columnsToDisplay = $columnsNames;
	}
	
	/**
	 * Sets translation string for given column
	 *
	 * @param string $columnName column name
	 * @param string $columnTranslation column name translation
	 */
	public function setColumnTranslation( $columnName, $columnTranslation )
	{
		$this->columnsTranslations[$columnName] = $columnTranslation;
	}
	
	/**
	 * Returns column translation if available, in other case given column name
	 *
	 * @param string $columnName column name
	 */
	public function getColumnTranslation( $columnName )
	{
		if( isset($this->columnsTranslations[$columnName]) )
		{
			return $this->columnsTranslations[$columnName];
		}
		else
		{
			return $columnName;
		}
	}
	
	/**
	 * Sets columns translations array.
	 *
	 * @param array $columnsTranslations An associative array indexed by column names, eg. array('nb_visit'=>"Numer of visits")
	 */
	public function setColumnsTranslations( $columnsTranslations )
	{
		$this->columnsTranslations = $columnsTranslations;
	}
	
	/**
	 * Returns array(
	 * 				array('id' => 1, 'name' => 'nb_visits'),
	 * 				array('id' => 3, 'name' => 'nb_uniq_visitors'),
	 *
	 * @param array PHP array conversion of the data table
	 * @return array
	 */
	protected function getColumnsToDisplay($phpArray)
	{
		$metadataColumnToDisplay = array();
		if(count($phpArray) > 0)
		{
			// we show the columns in order specified in the setColumnsToDisplay
			// each column has a string name; 
			// this name will for example be used to specify the sorting column 
			$columnsInDataTable = array_keys($phpArray[0]['columns']);
			$columnsToDisplay = $this->columnsToDisplay;
		
			if(count($columnsToDisplay) == 0)
			{
				$columnsToDisplay = $columnsInDataTable;
			}
			
			foreach($columnsToDisplay as $columnToDisplay)
			{
				if(in_array($columnToDisplay, $columnsInDataTable))
				{
					$metadataColumnToDisplay[]	= array(
										'name' => $columnToDisplay, 
										'displayName' => $this->getColumnTranslation($columnToDisplay) 
									);
				}
			}
		}
		return $metadataColumnToDisplay	;
	}

	/**
	 * Sets the columns in the HTML table as not sortable (they are not clickable) 
	 *
	 * @return void
	 */
	public function disableSort()
	{
		$this->variablesDefault['enable_sort'] = 'false';
	}

	/**
	 * Sets the search on a table to be recursive (also searches in subtables)
	 * Works only on Actions/Downloads/Outlinks tables.
	 *
	 * @return bool If the pattern for a recursive search was set in the URL
	 */
	public function setSearchRecursive()
	{
		$this->variablesDefault['search_recursive'] = true;
		return $this->setRecursiveLoadDataTableIfSearchingForPattern();
	}
	
	protected function setShowAllColumns()
	{
		$this->variablesDefault['filter_add_columns_when_show_all_columns'] = '1';
		$this->variablesDefault['showAllColumns'] = '1';
	}
	
	protected function getShowAllColumns()
	{
		return $this->variablesDefault['showAllColumns'];
	}
	
	/**
	 * Set the flag to load the datatable recursively so we can search on subtables as well
	 *
	 * @return bool if recursive search is enabled
	 */
	protected function setRecursiveLoadDataTableIfSearchingForPattern()
	{
		try{
			$requestValue = Piwik_Common::getRequestVar('filter_column_recursive');
			$requestValue = Piwik_Common::getRequestVar('filter_pattern_recursive');
			// if the 2 variables are set we are searching for something.
			// we have to load all the children subtables in this case
			
			$this->recursiveDataTableLoad = true;
			return true;
		}
		catch(Exception $e) {
			$this->recursiveDataTableLoad = false;
			return false;
		}
	}
}

