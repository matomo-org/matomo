<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Metrics;

use Piwik\Metrics;
use Piwik\DataTable\Row;

class Base
{
    protected $invalidDivision = 0;
    protected $roundPrecision = 2;

    protected function getNumVisits(Row $row)
    {
        return (int) $this->getColumn($row, Metrics::INDEX_NB_VISITS);
    }

    /**
     * Returns column from a given row.
     * Will work with 2 types of datatable
     * - raw datatables coming from the archive DB, which columns are int indexed
     * - datatables processed resulting of API calls, which columns have human readable english names
     *
     * @param Row|array $row
     * @param int $columnIdRaw see consts in Archive::
     * @param bool|array $mappingIdToName
     * @return mixed  Value of column, false if not found
     */
    public function getColumn($row, $columnIdRaw, $mappingIdToName = false)
    {
        if (empty($mappingIdToName)) {
            $mappingIdToName = Metrics::$mappingFromIdToName;
        }

        $columnIdReadable = $mappingIdToName[$columnIdRaw];

        if ($row instanceof Row) {
            $raw = $row->getColumn($columnIdRaw);
            if ($raw !== false) {
                return $raw;
            }
            return $row->getColumn($columnIdReadable);
        }

        if (isset($row[$columnIdRaw])) {
            return $row[$columnIdRaw];
        }

        if (isset($row[$columnIdReadable])) {
            return $row[$columnIdReadable];
        }

        return false;
    }
}