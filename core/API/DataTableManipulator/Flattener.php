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
	
	private $includeAggregateRows = false;
	
	/**
	 * If the flattener is used after calling this method, aggregate rows will
	 * be included in the result. This can be useful when they contain data that
	 * the leafs don't have (e.g. conversion stats in some cases).
	 */
	public function includeAggregateRows()
	{
		$this->includeAggregateRows = true;
	}
	
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
	
	private function flattenRow(Piwik_DataTable_Row $row, Piwik_DataTable $dataTable, $date,
			$labelPrefix = '', $parentLogo = false) {
		
		$label = trim($row->getColumn('label'));
		if (substr($label, 0, 1) == '/' && $this->recursiveLabelSeparator == '/')
		{
			$label = substr($label, 1);
		}
		$label = $labelPrefix . $label;
		$row->setColumn('label', $label);
		
		$logo = $row->getMetadata('logo');
		if ($logo === false && $parentLogo !== false)
		{
			$logo = $parentLogo;
			$row->setMetadata('logo', $logo);
		}
		
		$subTable = $this->loadSubtable($row, $date);
		$row->removeSubtable();
		
		if ($subTable === null)
		{
			$dataTable->addRow($row);
		}
		else
		{
			if ($this->includeAggregateRows)
			{
				$aggregate = Piwik_Translate('CoreHome_Aggregate');
				$row->setColumn('label', $row->getColumn('label').' ['.$aggregate.']');
				$dataTable->addRow($row);
			}
			$prefix = $label . $this->recursiveLabelSeparator;
			foreach ($subTable->getRows() as $row)
			{
				$this->flattenRow($row, $dataTable, $date, $prefix, $logo);
			}
		}
	}

	/** Remove the flat parameter from the subtable request */
	protected function manipulateSubtableRequest(&$request)
	{
		unset($request['flat']);
	}
}
