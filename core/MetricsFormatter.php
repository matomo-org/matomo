<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

use Piwik\Tracker\GoalManager;

/**
 * Class MetricsFormatter
 * @package Piwik
 *
 * @api
 */
class MetricsFormatter
{
    /**
     * Gets a prettified string representation of a number. The result will have
     * thousands separators and a decimal point specific to the current locale.
     *
     * @param number $value
     * @return string
     */
    public static function getPrettyNumber($value)
    {
        $locale = localeconv();

        $decimalPoint = $locale['decimal_point'];
        $thousandsSeparator = $locale['thousands_sep'];

        return number_format($value, 0, $decimalPoint, $thousandsSeparator);
    }

    /**
     * Pretty format a time
     *
     * @param int $numberOfSeconds
     * @param bool $displayTimeAsSentence If set to true, will output "5min 17s", if false "00:05:17"
     * @param bool $isHtml
     * @param bool $round to the full seconds
     * @return string
     */
    public static function getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence = true, $isHtml = true, $round = false)
    {
        $numberOfSeconds = $round ? (int)$numberOfSeconds : (float)$numberOfSeconds;

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
        if ($isHtml) {
            return str_replace(' ', '&nbsp;', $return);
        }
        return $return;
    }

    /**
     * Pretty format a memory size value
     *
     * @param number $size size in bytes
     * @param string $unit The specific unit to use, if any. If null, the unit is determined by $size.
     * @param int $precision The precision to use when rounding.
     * @return string
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
     * Pretty format monetary value for a site
     *
     * @param int|string $value
     * @param int $idSite
     * @param bool $htmlAllowed
     * @return string
     */
    public static function getPrettyMoney($value, $idSite, $htmlAllowed = true)
    {
        $currencyBefore = MetricsFormatter::getCurrencySymbol($idSite);

        $space = ' ';
        if ($htmlAllowed) {
            $space = '&nbsp;';
        }

        $currencyAfter = '';
        // manually put the currency symbol after the amount for euro
        // (maybe more currencies prefer this notation?)
        if (in_array($currencyBefore, array('€', 'kr'))) {
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
     * For the given value, based on the column name, will apply: pretty time, pretty money
     * @param int $idSite
     * @param string $columnName
     * @param mixed $value
     * @param bool $htmlAllowed
     * @return string
     */
    public static function getPrettyValue($idSite, $columnName, $value, $htmlAllowed)
    {
        // Display time in human readable
        if (strpos($columnName, 'time') !== false) {
            // Little hack: Display 15s rather than 00:00:15, only for "(avg|min|max)_generation_time"
            $timeAsSentence = (substr($columnName, -16) == '_time_generation');
            return self::getPrettyTimeFromSeconds($value, $timeAsSentence);
        }
        // Add revenue symbol to revenues
        if (strpos($columnName, 'revenue') !== false && strpos($columnName, 'evolution') === false) {
            return self::getPrettyMoney($value, $idSite, $htmlAllowed);
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
     * Get currency symbol for a site
     *
     * @param int $idSite
     * @return string
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
     * Returns a list of currency symbols
     *
     * @return array  array( currencyCode => symbol, ... )
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