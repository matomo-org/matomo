<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Metrics\Formatter;

use Piwik\Metrics\Formatter;

/**
 * A metrics formatter that prettifies metric values without returning string values.
 * Results of this class can be converted to numeric values and processed further in
 * some way.
 *
 * @api
 */
class Numeric extends Formatter
{
    public function getPrettyNumber($value, $precision = 0)
    {
        return round($value, $precision);
    }

    public function getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence = false, $round = false)
    {
        return $round ? (int)$numberOfSeconds : (float)$numberOfSeconds;
    }

    public function getPrettySizeFromBytes($size, $unit = null, $precision = 1)
    {
        list($size, $sizeUnit) = $this->getPrettySizeFromBytesWithUnit($size, $unit, $precision);
        return $size;
    }

    protected function getPrettySizeFromBytesWithUnit($size, $unit = null, $precision = 1)
    {
        $units = array('B', 'K', 'M', 'G', 'T');
        $numUnits = count($units) - 1;

        $currentUnit = null;
        foreach ($units as $idx => $currentUnit) {
            if ($unit && $unit !== $currentUnit) {
                $size = $size / 1024;
            } elseif ($unit && $unit === $currentUnit) {
                break;
            } elseif ($size >= 1024 && $idx != $numUnits) {
                $size = $size / 1024;
            } else {
                break;
            }
        }

        $size = round($size, $precision);

        return array($size, $currentUnit);
    }

    public function getPrettyMoney($value, $idSite)
    {
        return $value;
    }

    public function getPrettyPercentFromQuotient($value)
    {
        return $value * 100;
    }
}