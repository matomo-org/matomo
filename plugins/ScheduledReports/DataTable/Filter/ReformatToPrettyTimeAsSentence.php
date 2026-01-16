<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports\DataTable\Filter;

use Piwik\Columns\Dimension;
use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;

class ReformatToPrettyTimeAsSentence extends BaseFilter
{
    /**
     * @var array <string, string>
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
     * @param DataTable $table
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
        foreach ($this->durationColumns as $columnId => $metricType) {
            if (!$row->hasColumn($columnId)) {
                continue;
            }

            $formattedValue = $this->formatValue($row->getColumn($columnId), $metricType);
            if ($formattedValue !== null) {
                $row->setColumn($columnId, $formattedValue);
            }
        }
    }

    /**
     * @param mixed|bool $value
     * @param string $metricType
     * @return string|null
     */
    private function formatValue($value, string $metricType): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $number = (float) $value;
        } elseif (is_string($value)) {
            $number = $this->formatter->getSecondsFromPrettyTime($value);
            if ($number === null) {
                return null;
            }
        } else {
            return null;
        }
        if ($metricType === Dimension::TYPE_DURATION_MS) {
            $number = $this->formatter->converMillisecondsToSeconds($number);
        }
        return $this->formatter->getPrettyTimeFromSeconds($number, true);
    }
}
