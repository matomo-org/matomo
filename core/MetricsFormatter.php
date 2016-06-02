<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik;

use Piwik\Metrics\Formatter;
use Piwik\Plugins\API\ProcessedReport;

/**
 * Contains helper function that format numerical values in different ways.
 *
 * @deprecated
 */
class MetricsFormatter
{
    private static $formatter = null;
    private static $htmlFormatter = null;

    public static function getFormatter($isHtml = false)
    {
        if ($isHtml) {
            if (self::$formatter === null) {
                self::$formatter = new Formatter();
            }
            return self::$formatter;
        } else {
            if (self::$htmlFormatter === null) {
                self::$htmlFormatter = new Formatter\Html();
            }
            return self::$htmlFormatter;
        }
    }

    public static function getPrettyNumber($value)
    {
        return self::getFormatter()->getPrettyNumber($value);
    }

    public static function getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence = true, $isHtml = true, $round = false)
    {
        return self::getFormatter($isHtml)->getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence, $round);
    }

    public static function getPrettySizeFromBytes($size, $unit = null, $precision = 1)
    {
        return self::getFormatter()->getPrettySizeFromBytes($size, $unit, $precision);
    }

    public static function getPrettyMoney($value, $idSite, $isHtml = true)
    {
        return self::getFormatter($isHtml)->getPrettyMoney($value, $idSite);
    }

    public static function getPrettyValue($idSite, $columnName, $value, $isHtml)
    {
        return ProcessedReport::getPrettyValue(self::getFormatter($isHtml), $idSite, $columnName, $value);
    }

    public static function getCurrencySymbol($idSite)
    {
        return Site::getCurrencySymbolFor($idSite);
    }

    public static function getCurrencyList()
    {
        return Site::getCurrencyList();
    }
}
