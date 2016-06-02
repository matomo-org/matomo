<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Intl\Data\Provider;

/**
 * Provides date and time formats.
 */
class DateTimeFormatProvider
{
    const DATETIME_FORMAT_LONG    = 1;
    const DATETIME_FORMAT_SHORT   = 2;
    const DATE_FORMAT_LONG        = 10;
    const DATE_FORMAT_DAY_MONTH   = 11;
    const DATE_FORMAT_SHORT       = 12;
    const DATE_FORMAT_MONTH_SHORT = 13;
    const DATE_FORMAT_MONTH_LONG  = 14;
    const DATE_FORMAT_YEAR        = 15;
    const TIME_FORMAT             = 20;

    /**
     * Returns the format pattern for the given format type
     *
     * @param int $format  one of the format constants
     *
     * @return string
     */
    public function getFormatPattern($format)
    {
        switch ($format) {
            case self::DATETIME_FORMAT_LONG:
                return 'EEEE, MMMM d, y HH:mm:ss';

            case self::DATETIME_FORMAT_SHORT:
                return 'MMM d, y HH:mm:ss';

            case self::DATE_FORMAT_LONG:
                return 'EEEE, MMMM d, y';

            case self::DATE_FORMAT_DAY_MONTH:
                return 'E, MMM d';

            case self::DATE_FORMAT_SHORT:
                return 'MMM d, y';

            case self::DATE_FORMAT_MONTH_SHORT:
                return 'MMM y';

            case self::DATE_FORMAT_MONTH_LONG:
                return 'MMMM y';

            case self::DATE_FORMAT_YEAR:
                return 'y';

            case self::TIME_FORMAT:
                return 'HH:mm:ss';
        }

        return $format;
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
        if ($short) {
            return 'MMM d, y – MMM d, y';
        }

        return 'MMMM d, y – MMMM d, y';
    }
}
