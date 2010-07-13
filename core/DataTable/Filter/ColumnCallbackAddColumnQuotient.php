<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Adds a new column that is a division of two columns of the current row.
 * Useful to process bounce rates, exit rates, average time on page, etc.
 * 
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ColumnCallbackAddColumnQuotient extends Piwik_DataTable_Filter
{
	protected $columnValueToRead;
	protected $columnNameToAdd;
	protected $columnNameUsedAsDivisor;
	protected $totalValueUsedAsDivisor;
	protected $quotientPrecision;
	
	/**
	 * @param Piwik_DataTable $table
	 * @param string $columnValueToRead
	 * @param string $columnNameToAdd
	 * @param numeric|string $divisorValueOrDivisorColumnName 
	 * 						if a numeric value is given, we use this value as the divisor to process the percentage. 
	 * 						if a string is given, this string is the column name's value used as the divisor.
	 * @param numeric $quotientPrecision Division precision
	 */
	public function __construct( $table, $columnNameToAdd, $columnValueToRead, $divisorValueOrDivisorColumnName, $quotientPrecision = 0)
	{
		parent::__construct($table);
		$this->columnValueToRead = $columnValueToRead;
		$this->columnNameToAdd = $columnNameToAdd;
		if(is_numeric($divisorValueOrDivisorColumnName))
		{
			$this->totalValueUsedAsDivisor = $divisorValueOrDivisorColumnName;
		}
		else
		{
			$this->columnNameUsedAsDivisor = $divisorValueOrDivisorColumnName;
		}
		$this->quotientPrecision = $quotientPrecision;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			$value = $row->getColumn($this->columnValueToRead);
			if(!is_null($this->totalValueUsedAsDivisor))
			{
				$divisor = $this->totalValueUsedAsDivisor;
			}
			else
			{
				$divisor = $row->getColumn($this->columnNameUsedAsDivisor);
			}
			$formattedValue = $this->formatValue($value, $divisor); 
			$row->addColumn($this->columnNameToAdd, $formattedValue);
		}
	}
	
	protected function formatValue($value, $divisor)
	{
		$quotient = 0;
		if($divisor > 0 && $value > 0)
		{
			$quotient = round($value / $divisor, $this->quotientPrecision);
		}
		return $quotient;
	}
}
