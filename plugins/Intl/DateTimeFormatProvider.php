<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Intl;

use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Translation\Translator;

/**
 * Provides date and time formats.
 */
class DateTimeFormatProvider extends \Piwik\Intl\Data\Provider\DateTimeFormatProvider
{
    protected $use12HourClock;

    /**
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

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
                $pattern = $this->translator->translate('Intl_Format_DateTime_Long');
                break;

            case self::DATETIME_FORMAT_SHORT:
                $pattern = $this->translator->translate('Intl_Format_DateTime_Short');
                break;

            case self::DATE_FORMAT_LONG:
                $pattern = $this->translator->translate('Intl_Format_Date_Long');
                break;

            case self::DATE_FORMAT_DAY_MONTH:
                $pattern = $this->translator->translate('Intl_Format_Date_Day_Month');
                break;

            case self::DATE_FORMAT_SHORT:
                $pattern = $this->translator->translate('Intl_Format_Date_Short');
                break;

            case self::DATE_FORMAT_MONTH_SHORT:
                $pattern = $this->translator->translate('Intl_Format_Month_Short');
                break;

            case self::DATE_FORMAT_MONTH_LONG:
                $pattern = $this->translator->translate('Intl_Format_Month_Long');
                break;

            case self::DATE_FORMAT_YEAR:
                $pattern = $this->translator->translate('Intl_Format_Year');
                break;

            case self::TIME_FORMAT:
                $pattern = $this->translator->translate('Intl_Format_Time');
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
        return $this->translator->translate(
            sprintf(
                'Intl_Format_Interval_%s_%s',
                $short ? 'Short' : 'Long',
                $maxDifference
            ));
    }

    protected function getTimeFormat()
    {
        $timeFormat = 'Intl_Format_Time_24';

        if ($this->uses12HourClock()) {
            $timeFormat = 'Intl_Format_Time_12';
        }

        $template = $this->translator->translate($timeFormat);

        return $template;
    }

    /**
     * Returns if time is present as 12 hour clock (eg am/pm)
     *
     * @return bool
     */
    public function uses12HourClock()
    {
        if (is_null($this->use12HourClock)) {
            $this->use12HourClock = LanguagesManager::uses12HourClockForCurrentUser();
        }

        return $this->use12HourClock;
    }

    /**
     * For testing purpose only: Overwrites time format
     *
     * @param bool $use12HourClock
     */
    public function forceTimeFormat($use12HourClock = false)
    {
        $this->use12HourClock = $use12HourClock;
    }
}