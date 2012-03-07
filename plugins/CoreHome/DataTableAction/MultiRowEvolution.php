<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 * MULTI ROW EVOLUTION
 * The class handles the popup that shows the evolution of a multiple rows in a data table
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome_DataTableAction_MultiRowEvolution
		extends Piwik_CoreHome_DataTableAction_RowEvolution
{
	
	/** The requested labels */
	protected $labels;
	
	/** The requested metric */
	protected $metric;
	
	/** Show all metrics in the evolution graph when the popup opens */
	protected $initiallyShowAllMetrics = true;
	
	/** The metrics available in the metrics select */
	protected $metricsForSelect;
	
	/**
	 * The constructor
	 * @param int
	 * @param Piwik_Date ($this->date from controller)
	 */
	public function __construct($idSite, $date)
	{
		parent::__construct($idSite, $date);
		
		// extract multiple labels from the label parameter
		$this->labels = explode(',', $this->label);
		$this->labels = array_map('urldecode', $this->labels);
		
		if (count($this->labels) == 1)
		{
			throw new Exception("Expecting at least two labels.");
		}
		
		// get selected metric
		$this->metric = Piwik_Common::getRequestVar('column', '', 'string');
		if (empty($this->metric))
		{
			$this->metric = reset(array_keys($this->availableMetrics));
		}
	}
	
	/**
	 * Load the data tables from the API and combine them into a data table
	 * that can be plotted
	 */
	protected function loadDataTable()
	{
		$metadata = false;
		$dataTableArrays = array();
		
		// load the tables for each label
		foreach ($this->labels as $rowLabelIndex => $rowLabel)
		{
			// TODO this used to be: $_GET['label'] = $this->label = $rowLabel;
			// is the $_GET assignment obsolete after label filter refactorings?
			// maybe it is needed for the export icon?
			$this->label = $rowLabel;
			$table = $this->doLoadDataTable();
			$dataTableArrays[$rowLabelIndex] = $table->getArray();
			if (!$metadata)
			{
				$metadata = $table->metadata;
			}
			
			$urlFound = false;
			foreach ($dataTableArrays[$rowLabelIndex] as $table)
			{
				if ($table->getRowsCount() > 0)
				{
					$firstRow = $table->getFirstRow();
					
					// in case labels were replaced in the data table (e.g. for browsers report),
					// display the label from the table, not the one passed as filter
					$label = $firstRow->getColumn('label');
					if (!empty($label))
					{
						$this->labels[$rowLabelIndex] = $label;
						
						// special case: websites report
						if ($this->apiMethod == 'Referers.getWebsites')
						{
							$this->labels[$rowLabelIndex] = html_entity_decode($this->labels[$rowLabelIndex]);
							$urlFound = true;
						}
					}
					
					// if url is available as metadata, use it (only for actions reports)
					if (substr($this->apiMethod, 0, 7) == 'Actions' && $url = $firstRow->getMetadata('url'))
					{
						$this->labels[$rowLabelIndex] = $url;
						$urlFound = true;
					}
					
					break;
				}
			}
			
			if (!$urlFound && strpos($rowLabel, '>') !== false)
			{
				// if we have a recursive label and no url, use the path
				$this->labels[$rowLabelIndex] = str_replace('>', ' - ', $rowLabel);
			}
		}
		
		// combine the tables
		$dataTable = new Piwik_DataTable_Array;
		$dataTable->metadata = $metadata;
		
		foreach (array_keys(reset($dataTableArrays)) as $dateLabel)
		{
			$newRow = new Piwik_DataTable_Row;
			foreach ($dataTableArrays as $rowLabelIndex => $tableArray)
			{
				$table = $tableArray[$dateLabel];
				if ($table->getRowsCount() == 0)
				{
					$value = 0;
				}
				else
				{
					$value = $table->getFirstRow()->getColumn($this->metric);
					$value = floatVal(str_replace(',', '.', $value));
					if ($value == '')
					{
						$value = 0;
					}
				}
				// keep metric in the label so that unit (%, s, ...) can be guessed correctly
				$label = $this->metric.'_'.$rowLabelIndex;
				$newRow->addColumn($label, $value);
			}
			
			$newTable = new Piwik_DataTable;
			$newTable->addRow($newRow);
			$dataTable->addTable($newTable, $dateLabel);
		}
		
		// available metrics for metrics picker
		$this->metricsForSelect = $this->availableMetrics;
		$this->availableMetrics = array();
		foreach ($this->labels as $rowLabelIndex => $label) {
			// add metric name
			$label .= ' ('.$this->metricsForSelect[$this->metric].')';
			$this->availableMetrics[$this->metric.'_'.$rowLabelIndex] = $label;
		}
		
		return $dataTable;
	}
	
	/**
	 * Render the popup
	 * @param Piwik_CoreHome_Controller
	 * @param Piwik_View (the popup_rowevolution template)
	 */
	public function renderPopup($controller, $view)
	{
		// add data for metric select box
		$view->availableMetrics = $this->availableMetrics;
		$view->selectedMetric = $this->metric;
		
		$view->availableRecordsText = $this->metaData['dimension'].': '
				.Piwik_Translate('RowEvolution_ComparingRecords', array(count($this->labels)));
		
		return parent::renderPopup($controller, $view);
	}
	
}
