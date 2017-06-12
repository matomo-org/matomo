<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;

class DurationMilliseconds extends Number
{
    public function compute(Row $row)
    {
        return (int) $this->getMetric($row, $this->metric);
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyTimeFromSeconds($value, $displayAsSentence = true);
    }

}