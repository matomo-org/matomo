<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreVisualizations\Metrics\Formatter;

use Piwik\Common;
use Piwik\Metrics\Formatter;

/**
 * A metrics formatter that prettifies metric values without returning string values.
 * Results of this class can be converted to numeric values and processed further in
 * some way.
 */
class Numeric extends Formatter
{
    public function getPrettyNumber($value, $precision = 0)
    {
        return round($value, $precision);
    }

    public function getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence = false, $round = false)
    {
        return $round ? (int)$numberOfSeconds : (float) Common::forceDotAsSeparatorForDecimalPoint($numberOfSeconds);
    }

    public function getPrettySizeFromBytes($size, $unit = null, $precision = 1)
    {
        list($size, $sizeUnit) = $this->getPrettySizeFromBytesWithUnit($size, $unit, $precision);
        return $size;
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