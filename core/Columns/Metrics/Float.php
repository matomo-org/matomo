<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Columns\Metrics;

use Piwik\Metrics\Formatter;

class Float extends Number
{
    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyNumber($value, $precision = 2);
    }

}