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
 * Add a new 'metadata' column to the table based on the value resulting 
 * from a callback function with the parameter being another column's value
 * 
 * For example from the "label" column we can to create an "icon" 'metadata' column 
 * with the icon URI built from the label (LINUX => UserSettings/icons/linux.png)
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnCallbackAddMetadata extends Piwik_DataTable_Filter
{
	private $columnToRead;
	private $functionToApply;
	private $functionParameters;
	private $metadataToAdd;
	private $applyToSummaryRow;

	/**
	 * @param Piwik_DataTable $table
	 * @param $columnToRead
	 * @param $metadataToAdd
	 * @param null $functionToApply
	 * @param null $functionParameters
	 */
	public function __construct( $table, $columnToRead, $metadataToAdd, $functionToApply = null,
								   $functionParameters = null, $applyToSummaryRow = true )
	{
		parent::__construct($table);
		$this->functionToApply = $functionToApply;
		$this->functionParameters = $functionParameters;
		$this->columnToRead = $columnToRead;
		$this->metadataToAdd = $metadataToAdd;
		$this->applyToSummaryRow = $applyToSummaryRow;
	}

	/**
	 * Filters the given data table
	 *
	 * @param Piwik_DataTable  $table
	 */
	public function filter($table)
	{
		foreach($table->getRows() as $key => $row)
		{
			if (!$this->applyToSummaryRow && $key == Piwik_DataTable::ID_SUMMARY_ROW)
			{
				continue;
			}
			
			$oldValue = $row->getColumn($this->columnToRead);
			$parameters = array($oldValue);
			if(!is_null($this->functionParameters))
			{
				$parameters = array_merge($parameters, $this->functionParameters);
			}
			if(!is_null($this->functionToApply))
			{
				$newValue = call_user_func_array( $this->functionToApply, $parameters);
			}
			else
			{
				$newValue = $oldValue;
			}
			if ($newValue !== false)
			{
				$row->addMetadata($this->metadataToAdd, $newValue);
			}
		}
	}
}
