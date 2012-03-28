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
 * This class is responsible for flattening data tables.
 * 
 * It loads subtables and combines them into a single table by concatenating the labels.
 * This manipulator is triggered by using flat=1 in the API request.
 * 
 * @package Piwik
 * @subpackage Piwik_API
 */
class Piwik_API_DataTableManipulator_Flattener extends Piwik_API_DataTableManipulator
{
	
	/**
	 * Separator for building recursive labels (or paths)
	 * @var string
	 */
	public $recursiveLabelSeparator = ' - ';
	
	public function flatten($dataTable)
	{
		if ($this->apiModule == 'Actions' || $this->apiMethod == 'getWebsites')
		{
			$this->recursiveLabelSeparator = '/';
		}
		
		return $this->manipulate($dataTable);
	}

	/**
	 * Template method called from self::manipulate.
	 * Flatten each data table.
	 */
	protected function doManipulate(Piwik_DataTable $dataTable, $date = false)
	{
		$newDataTable = $dataTable->getEmptyClone();
		foreach ($dataTable->getRows() as $row)
		{
			$this->flattenRow($row, $newDataTable, $date);
		}
		return $newDataTable;
	}
	
	private function flattenRow(Piwik_DataTable_Row $row, Piwik_DataTable $dataTable, $date, $labelPrefix = '') {
		$originalLabel = $label = trim($row->getColumn('label'));
		if (substr($label, 0, 1) == '/' && $this->recursiveLabelSeparator == '/')
		{
			$label = substr($label, 1);
		}
		$label = $labelPrefix . $label;
		$row->setColumn('label', $label);
		
		$subTable = $this->loadSubtable($row, $date);
		if ($subTable === null)
		{
			$row->removeSubtable();
			$dataTable->addRow($row);
		}
		else
		{
			$prefix = $originalLabel . $this->recursiveLabelSeparator;
			foreach ($subTable->getRows() as $row)
			{
				$this->flattenRow($row, $dataTable, $date, $prefix);
			}
		}
	}

	/** Remove the flat parameter from the subtable request */
	protected function manipulateSubtableRequest(&$request)
	{
		unset($request['flat']);
	}
}
