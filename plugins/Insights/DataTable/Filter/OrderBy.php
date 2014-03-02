<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;

class OrderBy extends BaseFilter
{
    private $columnToRead;

    public function __construct($table, $columnToRead)
    {
        $this->columnToRead = $columnToRead;
    }

    public function filter($table)
    {
        if (!$table->getRowsCount()) {
            return;
        }

        $table->sort(array($this, 'sort'), $this->columnToRead);
    }

    public function sort(Row $a, Row $b)
    {
        $valA = $a->getColumn($this->columnToRead);
        $valB = $b->getColumn($this->columnToRead);

        if (!isset($valA) && !isset($valB)) {
            return 0;
        }

        if (!isset($valA)) {
            return 1;
        }

        if (!isset($valB)) {
            return -1;
        }

        if ($valA > 0 && $valB < 0) {
            return -1;
        }

        if ($valA < 0 && $valB < 0) {
            return $valA < $valB ? -1 : 1;
        }

        if ($valA != $valB) {
            return $valA < $valB ? 1 : -1;
        }

        return strnatcasecmp(
            $a->getColumn('nb_visits'),
            $b->getColumn('nb_visits')
        );
    }

}