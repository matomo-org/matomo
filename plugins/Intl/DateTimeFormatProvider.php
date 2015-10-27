<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Intl;

use Piwik\Container\StaticContainer;
use Piwik\Plugins\LanguagesManager\LanguagesManager;

/**
 * Provides date and time formats.
 */
class DateTimeFormatProvider extends \Piwik\Intl\Data\Provider\DateTimeFormatProvider
{
    protected static $use12HourClock;

    /**
     * Returns the format pattern for the given format type
     *
     * @param int $format  one of the format constants
     *
     * @return string
     */
    public function getFormatPattern($format)
    {
        $translator = StaticContainer::get('Piwik\Translation\Translator');

        switch ($format) {
            case self::DATETIME_FORMAT_LONG:
                $pattern = $translator->translate('Intl_Format_DateTime_Long');
                break;

            case self::DATETIME_FORMAT_SHORT:
                $pattern = $translator->translate('Intl_Format_DateTime_Short');
                break;

            case self::DATE_FORMAT_LONG:
                $pattern = $translator->translate('Intl_Format_Date_Long');
                break;

            case self::DATE_FORMAT_DAY_MONTH:
                $pattern = $translator->translate('Intl_Format_Date_Day_Month');
                break;

            case self::DATE_FORMAT_SHORT:
                $pattern = $translator->translate('Intl_Format_Date_Short');
                break;

            case self::DATE_FORMAT_MONTH_SHORT:
                $pattern = $translator->translate('Intl_Format_Month_Short');
                break;

            case self::DATE_FORMAT_MONTH_LONG:
                $pattern = $translator->translate('Intl_Format_Month_Long');
                break;

            case self::DATE_FORMAT_YEAR:
                $pattern = $translator->translate('Intl_Format_Year');
                break;

            case self::TIME_FORMAT:
                $pattern = $translator->translate('Intl_Format_Time');
                break;

            default:
                $pattern = $format;
        }

        if (strpos($pattern, '{time}') !== false) {
            $pattern = str_replace('{time}', $this->getTimeFormat(), $pattern);
        }

        return $pattern;
    }

    /**
     * Returns interval format pattern for the given format type
     *
     * @param bool $short  whether to return short or long format pattern
     * @param string $maxDifference  maximal difference in interval dates (Y, M or D)
     *
     * @return string
     */
    public function getRangeFormatPattern($short=false, $maxDifference='Y')
    {
        return StaticContainer::get('Piwik\Translation\Translator')->translate(
            sprintf(
                'Intl_Format_Interval_%s_%s',
                $short ? 'Short' : 'Long',
                $maxDifference
            ));
    }

    protected function getTimeFormat()
    {
        if (is_null(self::$use12HourClock)) {
            self::$use12HourClock = LanguagesManager::uses12HourClockForCurrentUser();
        }

        $timeFormat = 'Intl_Format_Time_24';

        if (self::$use12HourClock) {
            $timeFormat = 'Intl_Format_Time_12';
        }

        $translator = StaticContainer::get('Piwik\Translation\Translator');
        $template = $translator->translate($timeFormat);

        return $template;
    }

    /**
     * For testing purpose only: Overwrites time format
     *
     * @param bool $use12HourClock
     */
    public function forceTimeFormat($use12HourClock = false)
    {
        self::$use12HourClock = $use12HourClock;
    }
}