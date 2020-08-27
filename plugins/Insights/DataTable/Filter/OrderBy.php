<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;

/**
 * Goal is to list all positive values first (the higher the better) and then all negative values (the lower the better).
 *
 * 40%
 * 20%
 * 0%
 * -40%
 * -20%
 */
class OrderBy extends BaseFilter
{
    private $columnsToCheck;

    public function __construct($table, $columnToRead, $columnSecondOrder, $columnThirdOrder = '')
    {
        $this->columnsToCheck = array($columnToRead, $columnSecondOrder, $columnThirdOrder);
    }

    public function filter($table)
    {
        if (!$table->getRowsCount()) {
            return;
        }

        $table->sort(array($this, 'sort'), $this->columnsToCheck[0]);
    }

    public function sort(Row $a, Row $b)
    {
        foreach ($this->columnsToCheck as $column) {
            if ($column) {

                $valA = $a->getColumn($column);
                $valB = $b->getColumn($column);
                $sort = $this->sortVal($valA, $valB);

                if (isset($sort)) {
                    return $sort;
                }
            }
        }

        return 0;
    }

    private function sortVal($valA, $valB)
    {
        if ((!isset($valA) || $valA === false) && (!isset($valB) || $valB === false)) {
            return 0;
        }

        if (!isset($valA) || $valA === false) {
            return 1;
        }

        if (!isset($valB) || $valB === false) {
            return -1;
        }

        if ($valA === $valB) {
            return null;
        }

        if ($valA >= 0 && $valB < 0) {
            return -1;
        }

        if ($valA < 0 && $valB < 0) {
            return $valA < $valB ? -1 : 1;
        }

        if ($valA != $valB) {
            return $valA < $valB ? 1 : -1;
        }

        return null;
    }

}