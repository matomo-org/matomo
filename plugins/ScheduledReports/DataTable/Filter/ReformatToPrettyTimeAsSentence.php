<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;

class ReformatToPrettyTimeAsSentence extends BaseFilter
{
    /**
     * @var string[]
     */
    private $durationColumns;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(DataTable $table, array $durationColumns, ?Formatter $formatter = null)
    {
        parent::__construct($table);
        $this->durationColumns = $durationColumns;
        $this->formatter = $formatter ?: new Formatter();
        $this->enableRecursive(true);
    }

    /**
     * @param $table
     * @return void
     */
    public function filter($table): void
    {
        if (empty($this->durationColumns)) {
            return;
        }

        foreach ($table->getRows() as $row) {
            $this->formatRow($row);
            $this->filterSubTable($row);
        }

        $summaryRow = $table->getSummaryRow();
        if ($summaryRow instanceof Row) {
            $this->formatRow($summaryRow);
        }

        $totalsRow = $table->getTotalsRow();
        if ($totalsRow instanceof Row) {
            $this->formatRow($totalsRow);
        }
    }

    /**
     * @param Row $row
     * @return void
     */
    private function formatRow(Row $row): void
    {
        foreach ($this->durationColumns as $columnId) {
            if (!$row->hasColumn($columnId)) {
                continue;
            }

            $formattedValue = $this->formatValue($row->getColumn($columnId));
            if ($formattedValue !== null) {
                $row->setColumn($columnId, $formattedValue);
            }
        }
    }

    /**
     * @param $value
     * @return string|null
     */
    private function formatValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $seconds = (float) $value;
        } elseif (is_string($value)) {
            $seconds = $this->formatter->convertPrettyTimeToSeconds($value);
            if ($seconds === null) {
                return null;
            }
        } else {
            return null;
        }

        return $this->formatter->getPrettyTimeFromSeconds($seconds, true);
    }
}
