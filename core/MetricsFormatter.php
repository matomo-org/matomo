<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Tracker\GoalManager;

/**
 * Contains helper function that format numerical values in different ways.
 *
 * @api
 */
class MetricsFormatter
{
    /**
     * Returns a prettified string representation of a number. The result will have
     * thousands separators and a decimal point specific to the current locale, eg,
     * `'1,000,000.05'` or `'1.000.000,05'`.
     *
     * @param number $value
     * @return string
     */
    public static function getPrettyNumber($value)
    {
        static $decimalPoint = null;
        static $thousandsSeparator = null;

        if ($decimalPoint === null) {
            $locale = localeconv();

            $decimalPoint = $locale['decimal_point'];
            $thousandsSeparator = $locale['thousands_sep'];
        }

        return number_format($value, 0, $decimalPoint, $thousandsSeparator);
    }

    /**
     * Returns a prettified time value (in seconds).
     *
     * @param int $numberOfSeconds The number of seconds.
     * @param bool $displayTimeAsSentence If set to true, will output `"5min 17s"`, if false `"00:05:17"`.
     * @param bool $isHtml If true, replaces all spaces with `'&nbsp;'`.
     * @param bool $round Whether to round to the nearest second or not.
     * @return string
     */
    public static function getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence = true, $isHtml = true, $round = false)
    {
        $numberOfSeconds = $round ? (int)$numberOfSeconds : (float)$numberOfSeconds;

        $isNegative = false;
        if ($numberOfSeconds < 0) {
            $numberOfSeconds = -1 * $numberOfSeconds;
            $isNegative = true;
        }

        // Display 01:45:17 time format
        if ($displayTimeAsSentence === false) {
            $hours = floor($numberOfSeconds / 3600);
            $minutes = floor(($reminder = ($numberOfSeconds - $hours * 3600)) / 60);
            $seconds = floor($reminder - $minutes * 60);
            $time = sprintf("%02s", $hours) . ':' . sprintf("%02s", $minutes) . ':' . sprintf("%02s", $seconds);
            $centiSeconds = ($numberOfSeconds * 100) % 100;
            if ($centiSeconds) {
                $time .= '.' . sprintf("%02s", $centiSeconds);
            }
            if ($isNegative) {
                $time = '-' . $time;
            }
            return $time;
        }
        $secondsInYear = 86400 * 365.25;

        $years = floor($numberOfSeconds / $secondsInYear);
        $minusYears = $numberOfSeconds - $years * $secondsInYear;
        $days = floor($minusYears / 86400);

        $minusDays = $numberOfSeconds - $days * 86400;
        $hours = floor($minusDays / 3600);

        $minusDaysAndHours = $minusDays - $hours * 3600;
        $minutes = floor($minusDaysAndHours / 60);

        $seconds = $minusDaysAndHours - $minutes * 60;
        $precision = ($seconds > 0 && $seconds < 0.01 ? 3 : 2);
        $seconds = round($seconds, $precision);

        if ($years > 0) {
            $return = sprintf(Piwik::translate('General_YearsDays'), $years, $days);
        } elseif ($days > 0) {
            $return = sprintf(Piwik::translate('General_DaysHours'), $days, $hours);
        } elseif ($hours > 0) {
            $return = sprintf(Piwik::translate('General_HoursMinutes'), $hours, $minutes);
        } elseif ($minutes > 0) {
            $return = sprintf(Piwik::translate('General_MinutesSeconds'), $minutes, $seconds);
        } else {
            $return = sprintf(Piwik::translate('General_Seconds'), $seconds);
        }

        if ($isNegative) {
            $return = '-' . $return;
        }

        if ($isHtml) {
            return str_replace(' ', '&nbsp;', $return);
        }
        return $return;
    }

    /**
     * Returns a prettified memory size value.
     *
     * @param number $size The size in bytes.
     * @param string $unit The specific unit to use, if any. If null, the unit is determined by $size.
     * @param int $precision The precision to use when rounding.
     * @return string eg, `'128 M'` or `'256 K'`.
     */
    public static function getPrettySizeFromBytes($size, $unit = null, $precision = 1)
    {
        if ($size == 0) {
            return '0 M';
        }

        $units = array('B', 'K', 'M', 'G', 'T');
        foreach ($units as $currentUnit) {
            if ($size >= 1024 && $unit != $currentUnit) {
                $size = $size / 1024;
            } else {
                break;
            }
        }
        return round($size, $precision) . " " . $currentUnit;
    }

    /**
     * Returns a pretty formated monetary value using the currency associated with a site.
     *
     * @param int|string $value The monetary value to format.
     * @param int $idSite The ID of the site whose currency will be used.
     * @param bool $isHtml If true, replaces all spaces with `'&nbsp;'`.
     * @return string
     */
    public static function getPrettyMoney($value, $idSite, $isHtml = true)
    {
        $currencyBefore = MetricsFormatter::getCurrencySymbol($idSite);

        $space = ' ';
        if ($isHtml) {
            $space = '&nbsp;';
        }

        $currencyAfter = '';
        // (maybe more currencies prefer this notation?)
        $currencySymbolToAppend = array('€', 'kr', 'zł');

        // manually put the currency symbol after the amount
        if (in_array($currencyBefore, $currencySymbolToAppend)) {
            $currencyAfter = $space . $currencyBefore;
            $currencyBefore = '';
        }

        // if the input is a number (it could be a string or INPUT form),
        // and if this number is not an int, we round to precision 2
        if (is_numeric($value)) {
            if ($value == round($value)) {
                // 0.0 => 0
                $value = round($value);
            } else {
                $precision = GoalManager::REVENUE_PRECISION;
                $value = sprintf("%01." . $precision . "f", $value);
            }
        }
        $prettyMoney = $currencyBefore . $space . $value . $currencyAfter;
        return $prettyMoney;
    }

    /**
     * Prettifies a metric value based on the column name.
     *
     * @param int $idSite The ID of the site the metric is for (used if the column value is an amount of money).
     * @param string $columnName The metric name.
     * @param mixed $value The metric value.
     * @param bool $isHtml If true, replaces all spaces with `'&nbsp;'`.
     * @return string
     */
    public static function getPrettyValue($idSite, $columnName, $value, $isHtml)
    {
        // Display time in human readable
        if (strpos($columnName, 'time') !== false) {
            // Little hack: Display 15s rather than 00:00:15, only for "(avg|min|max)_generation_time"
            $timeAsSentence = (substr($columnName, -16) == '_time_generation');
            return self::getPrettyTimeFromSeconds($value, $timeAsSentence);
        }
        // Add revenue symbol to revenues
        if (strpos($columnName, 'revenue') !== false && strpos($columnName, 'evolution') === false) {
            return self::getPrettyMoney($value, $idSite, $isHtml);
        }
        // Add % symbol to rates
        if (strpos($columnName, '_rate') !== false) {
            if (strpos($value, "%") === false) {
                return $value . "%";
            }
        }
        return $value;
    }

    /**
     * Returns the currency symbol for a site.
     *
     * @param int $idSite The ID of the site to return the currency symbol for.
     * @return string eg, `'$'`.
     */
    public static function getCurrencySymbol($idSite)
    {
        $symbols = MetricsFormatter::getCurrencyList();
        $site = new Site($idSite);
        $currency = $site->getCurrency();
        if (isset($symbols[$currency])) {
            return $symbols[$currency][0];
        }
        return '';
    }

    /**
     * Returns the list of all known currency symbols.
     *
     * @return array An array mapping currency codes to their respective currency symbols
     *               and a description, eg, `array('USD' => array('$', 'US dollar'))`.
     */
    public static function getCurrencyList()
    {
        static $currenciesList = null;
        if (is_null($currenciesList)) {
            require_once PIWIK_INCLUDE_PATH . '/core/DataFiles/Currencies.php';
            $currenciesList = $GLOBALS['Piwik_CurrencyList'];
        }
        return $currenciesList;
    }
}
