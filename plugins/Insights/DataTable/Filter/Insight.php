<?php
/**
 * Piwik - free/libre analytics platform
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
        foreach ($this->currentDataTable->getRows() as $row) {
            $this->addRowIfNewOrMover($table, $row);
        }

        if ($this->considerDisappeared) {
            foreach ($this->pastDataTable->getRows() as $row) {
                $this->addRowIfDisappeared($table, $row);
            }
        }
    }

    private function addRowIfDisappeared(DataTable $table, DataTable\Row $row)
    {
        if ($this->getRowFromTable($this->currentDataTable, $row)) {
            return;
        }

        $newValue   = 0;
        $oldValue   = $row->getColumn($this->columnValueToRead);
        $difference = $newValue - $oldValue;

        if ($oldValue == 0 && $newValue == 0) {
            $growthPercentage = '0%';
        } else {
            $growthPercentage = '-100%';
        }

        $this->addRow($table, $row, $growthPercentage, $newValue, $oldValue, $difference, $isDisappeared = true);
    }

    private function addRowIfNewOrMover(DataTable $table, DataTable\Row $row)
    {
        $pastRow = $this->getPastRowFromCurrent($row);

        if (!$pastRow && !$this->considerNew) {
            return;
        } elseif ($pastRow && !$this->considerMovers) {
            return;
        }

        $isNew   = false;
        $isMover = false;
        $isDisappeared = false;

        if (!$pastRow) {
            $isNew    = true;
            $oldValue = 0;
        } else {
            $isMover  = true;
            $oldValue = $pastRow->getColumn($this->columnValueToRead);
        }

        $difference = $this->getDividend($row);
        if ($difference === false) {
            return;
        }

        $newValue = $row->getColumn($this->columnValueToRead);
        $divisor  = $this->getDivisor($row);

        $growthPercentage = $this->formatValue($difference, $divisor);

        $this->addRow($table, $row, $growthPercentage, $newValue, $oldValue, $difference, $isDisappeared, $isNew, $isMover);
    }

    private function getRowFromTable(DataTable $table, DataTable\Row $row)
    {
        return $table->getRowFromLabel($row->getColumn('label'));
    }

    private function addRow(DataTable $table, DataTable\Row $row, $growthPercentage, $newValue, $oldValue, $difference, $disappeared = false, $isNew = false, $isMover = false)
    {
        $columns = $row->getColumns();
        $columns['growth_percent'] = $growthPercentage;
        $columns['growth_percent_numeric'] = str_replace('%', '', $growthPercentage);
        $columns['grown']      = '-' != substr($growthPercentage, 0 , 1);
        $columns['value_old']  = $oldValue;
        $columns['value_new']  = $newValue;
        $columns['difference'] = $difference;
        $columns['importance'] = abs($difference);
        $columns['isDisappeared'] = $disappeared;
        $columns['isNew']   = $isNew;
        $columns['isMover'] = $isMover;

        $table->addRowFromArray(array(DataTable\Row::COLUMNS => $columns));
    }
}