<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * This class is responsible for handling the label parameter that can be
 * added to every API call. If the parameter is set, only the row with the matching
 * label is returned.
 * 
 * The labels passed to this class should be urlencoded.
 * Some reports use recursive labels (e.g. action reports). Use > to join them.
 * 
 * This filter does not work when expanded=1 is set because it is designed to load
 * only the subtables on the path, not all existing subtables (which would happen with
 * expanded=1). Also, the aim of this filter is to return only the row matching the
 * label. With expanded=1, the subtables of the matching row would be returned as well.
 * 
 * @package Piwik
 * @subpackage Piwik_API
 */
class Piwik_API_DataTableManipulator_LabelFilter extends Piwik_API_DataTableManipulator
{
	
	const SEPARATOR_RECURSIVE_LABEL = '>';
	
	private $labelParts;

	/**
	 * Filter a data table by label.
	 * The filtered table is returned, which might be a new instance.
	 *
	 * $apiModule, $apiMethod and $request are needed load sub-datatables
	 * for the recursive search. If the label is not recursive, these parameters
	 * are not needed.
	 *
	 * @param string           $label      the label to search for
	 * @param Piwik_DataTable  $dataTable  the data table to be filtered
	 * @return Piwik_DataTable
	 */
	public function filter($label, $dataTable)
	{
		// make sure we have the right classes
		if (!($dataTable instanceof Piwik_DataTable)
				&& !($dataTable instanceof Piwik_DataTable_Array))
		{
			return $dataTable;
		}
		foreach ($this->getLabelVariations($label) as $label)
		{
			$label = explode(self::SEPARATOR_RECURSIVE_LABEL, $label);
			$label = array_map('urldecode', $label);

			if (count($label) > 1)
			{
				// do a recursive search
				$this->labelParts = $label;
				return $this->manipulate($dataTable);
			}
			$label = $label[0];

			// do a non-recursive search
			$result = $dataTable->getFilteredTableFromLabel($label);
			if ($result->getFirstRow() !== false)
			{
				return $result;
			}
		}
		return $result;
	}

	/**
	 * This method is called from parent::manipulate for each Piwik_DataTable.
	 * It starts the recursive descend and builds a table with one or zero rows.
	 *
	 * @param Piwik_DataTable  $dataTable
	 * @param bool             $date
	 * @return Piwik_DataTable
	 */
	protected function doManipulate(Piwik_DataTable $dataTable, $date=false)
	{
		$row = $this->doFilterRecursiveDescend($this->labelParts, $dataTable, $date);
		$newDataTable = $dataTable->getEmptyClone();
		if ($row !== false)
		{
			$newDataTable->addRow($row);
		}
		return $newDataTable;
	}

	/**
	 * Method for the recursive descend
	 *
	 * @param array            $labelParts
	 * @param Piwik_DataTable  $dataTable
	 * @param bool             $date
	 * @return Piwik_DataTable_Row|false
	 */
	private function doFilterRecursiveDescend($labelParts, $dataTable, $date=false)
	{
		
		// search for the first part of the tree search
        $labelPart = array_shift($labelParts);
		
		foreach ($this->getLabelVariations($labelPart) as $labelPart)
		{
			$row = $dataTable->getRowFromLabel($labelPart);
			if ($row !== false)
			{
				break;
			}
		}
		
		if ($row === false)
		{
			// not found
			return false;
		}

		// end of tree search reached
		if (count($labelParts) == 0)
		{
			return $row;
		}
		
		$subTable = $this->loadSubtable($row, $date);
		if ($subTable === null)
		{
			// no more subtables but label parts left => no match found
			return false;
		}
		
		return $this->doFilterRecursiveDescend($labelParts, $subTable, $date);
	}

	/**
	 * Clean up request for Piwik_API_ResponseBuilder to behave correctly
	 *
	 * @param $request
	 */
	protected function manipulateSubtableRequest(&$request)
	{
		unset($request['label']);
	}

	/**
	 * Use variations of the label to make it easier to specify the desired label
	 *
	 * Note: The HTML Encoded version must be tried first, since in Piwik_API_ResponseBuilder the $label is unsanitized
	 * via Piwik_Common::unsanitizeInputValue.
	 *
	 * @param string $label
	 * @return array
	 */
	private function getLabelVariations($label)
	{
		$variations = array();
		$label = trim($label);

		$sanitizedLabel = Piwik_Common::sanitizeInputValue($label);
		$variations[]  = $sanitizedLabel;
		
		if ($this->apiModule == 'Actions' 
			&& $this->apiMethod == 'getPageTitles')
		{
			// special case: the Actions.getPageTitles report prefixes some labels with a blank.
			// the blank might be passed by the user but is removed in Piwik_API_Request::getRequestArrayFromString.
			$variations[] = ' '.$sanitizedLabel;
			$variations[] = ' '.$label;
		}
		$variations[] = $label;
		
		return $variations;
	}
	
}