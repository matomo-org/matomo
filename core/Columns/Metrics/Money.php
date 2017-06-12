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

class Money extends Number
{
    public function compute(Row $row)
    {
        return round($this->getMetric($row, $this->metric), 2);
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyMoney($value, $this->idSite);
    }

}