<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Metrics;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Intl\Data\Provider\CurrencyDataProvider;
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Piwik\Plugin\Metric;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugin\Report;
use Piwik\Site;
use Piwik\Tracker\GoalManager;

/**
 * Contains methods to format metric values. Passed to the {@link \Piwik\Plugin\Metric::format()}
 * method when formatting Metrics.
 */
class Formatter
{
    const PROCESSED_METRICS_FORMATTED_FLAG = 'processed_metrics_formatted';

    private $decimalPoint = null;
    private $thousandsSeparator = null;

    /**
     * Returns a prettified string representation of a number. The result will have
     * thousands separators and a decimal point specific to the current locale, eg,
     * `'1,000,000.05'` or `'1.000.000,05'`.
     *
     * @param number $value
     * @return string
     * @api
     */
    public function getPrettyNumber($value, $precision = 0)
    {
        if ($this->decimalPoint === null) {
            $locale = localeconv();

            $this->decimalPoint = $locale['decimal_point'];
            $this->thousandsSeparator = $locale['thousands_sep'];
        }

        return number_format($value, $precision, $this->decimalPoint, $this->thousandsSeparator);
    }

    /**
     * Returns a prettified time value (in seconds).
     *
     * @param int $numberOfSeconds The number of seconds.
     * @param bool $displayTimeAsSentence If set to true, will output `"5min 17s"`, if false `"00:05:17"`.
     * @param bool $round Whether to round to the nearest second or not.
     * @return string
     * @api
     */
    public function getPrettyTimeFromSeconds($numberOfSeconds, $displayTimeAsSentence = false, $round = false)
    {
        $numberOfSeconds = $round ? (int)$numberOfSeconds : (float)$numberOfSeconds;

        $isNegative = false;
        if ($numberOfSeconds < 0) {
            $numberOfSeconds = -1 * $numberOfSeconds;
            $isNegative = true;
        }

        // Display 01:45:17 time format
        if ($displayTimeAsSentence === false) {
            $hours   = floor($numberOfSeconds / 3600);
            $minutes = floor(($reminder = ($numberOfSeconds - $hours * 3600)) / 60);
            $seconds = floor($reminder - $minutes * 60);
            $time    = sprintf("%02s", $hours) . ':' . sprintf("%02s", $minutes) . ':' . sprintf("%02s", $seconds);
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

        $years      = floor($numberOfSeconds / $secondsInYear);
        $minusYears = $numberOfSeconds - $years * $secondsInYear;
        $days       = floor($minusYears / 86400);

        $minusDays = $numberOfSeconds - $days * 86400;
        $hours     = floor($minusDays / 3600);

        $minusDaysAndHours = $minusDays - $hours * 3600;
        $minutes = floor($minusDaysAndHours / 60);

        $seconds   = $minusDaysAndHours - $minutes * 60;
        $precision = ($seconds > 0 && $seconds < 0.01 ? 3 : 2);
        $seconds   = NumberFormatter::getInstance()->formatNumber(round($seconds, $precision), $precision);

        if ($years > 0) {
            $return = sprintf(Piwik::translate('General_YearsDays'), $years, $days);
        } elseif ($days > 0) {
            $return = sprintf(Piwik::translate('General_DaysHours'), $days, $hours);
        } elseif ($hours > 0) {
            $return = sprintf(Piwik::translate('General_HoursMinutes'), $hours, $minutes);
        } elseif ($minutes > 0) {
            $return = sprintf(Piwik::translate('General_MinutesSeconds'), $minutes, $seconds);
        } else {
            $return = sprintf(Piwik::translate('Intl_NSecondsShort'), $seconds);
        }

        if ($isNegative) {
            $return = '-' . $return;
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
     * @api
     */
    public function getPrettySizeFromBytes($size, $unit = null, $precision = 1)
    {
        if ($size == 0) {
            return '0 M';
        }

        list($size, $sizeUnit) = $this->getPrettySizeFromBytesWithUnit($size, $unit, $precision);
        return $size . " " . $sizeUnit;
    }

    /**
     * Returns a pretty formated monetary value using the currency associated with a site.
     *
     * @param int|string $value The monetary value to format.
     * @param int $idSite The ID of the site whose currency will be used.
     * @return string
     * @api
     */
    public function getPrettyMoney($value, $idSite)
    {
        $space = ' ';
        $currencySymbol = Site::getCurrencySymbolFor($idSite);
        $currencyBefore =  $currencySymbol . $space;
        $currencyAfter = '';
        // (maybe more currencies prefer this notation?)
        $currencySymbolToAppend = array('€', 'kr', 'zł');
        // manually put the currency symbol after the amount
        if (in_array($currencySymbol, $currencySymbolToAppend)) {
            $currencyAfter = $space . $currencySymbol;
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
        $prettyMoney = $currencyBefore . $value . $currencyAfter;
        return $prettyMoney;
    }

    /**
     * Returns a percent string from a quotient value. Forces the use of a '.'
     * decimal place.
     *
     * @param float $value
     * @return string
     * @api
     */
    public function getPrettyPercentFromQuotient($value)
    {
        $result = ($value * 100) . '%';
        return Common::forceDotAsSeparatorForDecimalPoint($result);
    }

    /**
     * Formats all metrics, including processed metrics, for a DataTable. Metrics to format
     * are found through report metadata and DataTable metadata.
     *
     * @param DataTable $dataTable The table to format metrics for.
     * @param Report|null $report The report the table belongs to.
     * @param string[]|null $metricsToFormat Whitelist of names of metrics to format.
     * @api
     */
    public function formatMetrics(DataTable $dataTable, Report $report = null, $metricsToFormat = null)
    {
        $metrics = $this->getMetricsToFormat($dataTable, $report);
        if (empty($metrics)
            || $dataTable->getMetadata(self::PROCESSED_METRICS_FORMATTED_FLAG)
        ) {
            return;
        }

        $dataTable->setMetadata(self::PROCESSED_METRICS_FORMATTED_FLAG, true);

        if ($metricsToFormat !== null) {
            $metricMatchRegex = $this->makeRegexToMatchMetrics($metricsToFormat);
            $metrics = array_filter($metrics, function (ProcessedMetric $metric) use ($metricMatchRegex) {
                return preg_match($metricMatchRegex, $metric->getName());
            });
        }

        foreach ($metrics as $name => $metric) {
            if (!$metric->beforeFormat($report, $dataTable)) {
                continue;
            }

            foreach ($dataTable->getRows() as $row) {
                $columnValue = $row->getColumn($name);
                if ($columnValue !== false) {
                    $row->setColumn($name, $metric->format($columnValue, $this));
                }

                $subtable = $row->getSubtable();
                if (!empty($subtable)) {
                    $this->formatMetrics($subtable, $report, $metricsToFormat);
                }
            }
        }
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

    private function makeRegexToMatchMetrics($metricsToFormat)
    {
        $metricsRegexParts = array();
        foreach ($metricsToFormat as $metricFilter) {
            if ($metricFilter[0] == '/') {
                $metricsRegexParts[] = '(?:' . substr($metricFilter, 1, strlen($metricFilter) - 2) . ')';
            } else {
                $metricsRegexParts[] = preg_quote($metricFilter);
            }
        }
        return '/^' . implode('|', $metricsRegexParts) . '$/';
    }

    /**
     * @param DataTable $dataTable
     * @param Report $report
     * @return Metric[]
     */
    private function getMetricsToFormat(DataTable $dataTable, Report $report = null)
    {
        return Report::getMetricsForTable($dataTable, $report, $baseType = 'Piwik\\Plugin\\Metric');
    }
}
