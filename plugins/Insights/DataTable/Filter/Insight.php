<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;

use Piwik\DataTable;

class Insight extends DataTable\Filter\CalculateEvolutionFilter
{
    private $considerMovers;
    private $considerNew;
    private $considerDisappeared;
    private $currentDataTable;

    public function __construct($table, $currentDataTable, $pastDataTable, $columnToRead,
                                $considerMovers, $considerNew, $considerDisappeared)
    {
        parent::__construct($table, $pastDataTable, 'growth', $columnToRead, $quotientPrecision = 1);

        $this->currentDataTable = $currentDataTable;
        $this->considerMovers = $considerMovers;
        $this->considerNew = $considerNew;
        $this->considerDisappeared = $considerDisappeared;
    }

    public function filter($table)
    {
        foreach ($this->currentDataTable->getRows() as $key => $row) {
            $pastRow  = $this->getPastRowFromCurrent($row);
            $oldValue = 0;

            if (!$pastRow && !$this->considerNew) {
                continue;
            }

            if ($pastRow && $this->considerMovers) {
                $oldValue = $pastRow->getColumn($this->columnValueToRead);
            } elseif ($pastRow) {
                continue;
            }

            $difference = $this->getDividend($row);
            if ($difference === false) {
                continue;
            }

            $newValue = $row->getColumn($this->columnValueToRead);
            $divisor  = $this->getDivisor($row);

            $growthPercentage = $this->formatValue($difference, $divisor);

            $this->addRow($table, $row, $growthPercentage, $newValue, $oldValue, $difference);
        }

        if ($this->considerDisappeared) {
            foreach ($this->pastDataTable->getRows() as $key => $row) {

                if ($this->getRowFromTable($this->currentDataTable, $row)) {
                    continue;
                }

                $newValue   = 0;
                $oldValue   = $row->getColumn($this->columnValueToRead);
                $difference = $newValue - $oldValue;

                $growthPercentage = '-100%';

                $this->addRow($table, $row, $growthPercentage, $newValue, $oldValue, $difference);
            }
        }
    }

    private function getRowFromTable(DataTable $table, DataTable\Row $row)
    {
        return $table->getRowFromLabel($row->getColumn('label'));
    }

    private function addRow(DataTable $table, DataTable\Row $row, $growthPercentage, $newValue, $oldValue, $difference)
    {
        $columns = $row->getColumns();
        $columns['growth_percent'] = $growthPercentage;
        $columns['growth_percent_numeric'] = str_replace('%', '', $growthPercentage);
        $columns['grown']      = '-' != substr($growthPercentage, 0 , 1);
        $columns['value_old']  = $oldValue;
        $columns['value_new']  = $newValue;
        $columns['difference'] = $difference;
        $columns['importance'] = abs($difference);

        $table->addRowFromArray(array(DataTable\Row::COLUMNS => $columns));
    }
}