<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: CalculateEvolutionFilter.php$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Goals
 */

/**
 * A DataTable filter that calculates the evolution of a metric and adds
 * it to each row as a percentage.
 * 
 * This filter cannot be used as a normal filter since it requires
 * corresponding data from another datatable. Instead, to use it,
 * you must manually perform a binary filter (see the MultiSites API).
 *
 * The evolution metric is calculated as:
 * <code>((currentValue - pastValue) / pastValue) * 100</code>
 */
class Piwik_MultiSites_CalculateEvolutionFilter extends Piwik_DataTable_Filter_ColumnCallbackAddColumnPercentage
{
	/**
	 * The the DataTable that contains past data.
	 */
	private $pastDataTable;

	/**
	 * Tells if column being added is the revenue evolution column.
	 */
	private $isRevenueEvolution = null;

	/**
	 * Constructor.
	 * 
	 * @param Piwik_DataTable $table The DataTable being filtered.
	 * @param string $columnToAdd
	 * @param string $columnToRead
	 * @param int $quotientPrecision
	 */
	function __construct($table, $pastDataTable, $columnToAdd, $columnToRead, $quotientPrecision = 0)
	{
		parent::__construct(
			$table, $columnToAdd, $columnToRead, $columnToRead, $quotientPrecision, $shouldSkipRows = true);
		
		$this->pastDataTable = $pastDataTable;
		
		$this->isRevenueEvolution = $columnToAdd == 'revenue_evolution';
	}

	/**
	 * Returns the difference between the column in the specific row and its
	 * sister column in the past DataTable.
	 * 
	 * @param Piwik_DataTable_Row $row
	 * @return int|float
	 */
	protected function getDividend($row)
	{
		$currentValue = $row->getColumn($this->columnValueToRead);

		// if the site this is for doesn't support ecommerce & this is for the revenue_evolution column,
		// we don't add the new column
		if ($currentValue === false
			&& $this->isRevenueEvolution
			&& !Piwik_Site::isEcommerceEnabledFor($row->getColumn('label')))
		{
			return false;
		}

		$pastRow = $this->getPastRowFromCurrent($row);
		if ($pastRow)
		{
			$pastValue = $pastRow->getColumn($this->columnValueToRead);
		}
		else
		{
			$pastValue = 0;
		}

		return $currentValue - $pastValue;
	}
	
	/**
	 * Returns the value of the column in $row's sister row in the past
	 * DataTable.
	 * 
	 * @param Piwik_DataTable_Row $row
	 * @return int|float
	 */
	protected function getDivisor($row)
	{
		$pastRow = $this->getPastRowFromCurrent($row);
		if (!$pastRow) return 0;

		return $pastRow->getColumn($this->columnNameUsedAsDivisor);
	}
	
	/**
	 * Calculates and formats a quotient based on a divisor and dividend.
	 * 
	 * Unlike Piwik_DataTable_Filter_ColumnCallbackAddColumnPercentage's,
	 * version of this method, this method will return 100% if the past
	 * value of a metric is 0, and the current value is not 0. For a
	 * value representative of an evolution, this makes sense.
	 * 
	 * @param int|float $value The dividend.
	 * @param int|float $divisor
	 * @return string
	 */
	protected function formatValue($value, $divisor)
	{
		if($value == 0)
		{
			$evolution = 0;
		}
		elseif($divisor == 0)
		{
			$evolution = 100;
		}
		else
		{
			$evolution = ($value / $divisor) * 100;
		}

		return round($evolution, $this->quotientPrecision).'%';
	}

	/**
	 * Utility function. Returns the current row in the past DataTable.
	 * 
	 * @param Piwik_DataTable_Row $row The row in the 'current' DataTable.
	 */
	private function getPastRowFromCurrent($row)
	{
		return $this->pastDataTable->getRowFromLabel($row->getColumn('label'));
	}
}
