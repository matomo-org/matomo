<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\VisitTime;

use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Piwik;

function getTimeLabel($label)
{
    $time = mktime($label);
    if (empty($time)) {
        return Piwik::translate('General_Unknown');
    }

    $date             = Date::factory($time);
    $dateTimeProvider = StaticContainer::get('Piwik\Intl\Data\Provider\DateTimeFormatProvider');

    if ($dateTimeProvider->uses12HourClock()) {
        return $date->getLocalized(Piwik::translate('Intl_Format_Hour_12'));
    }

    return $date->getLocalized(Piwik::translate('Intl_Format_Hour_24'));
}

/**
 * Returns the day of the week for a date string, without creating a new
 * Date instance.
 *
 * @param string $dateStr
 * @return int The day of the week (1-7)
 */
function dayOfWeekFromDate($dateStr)
{
    return date('N', strtotime($dateStr));
}

/**
 * Returns translated long name of a day of the week.
 *
 * @param int $dayOfWeek 1-7, for Sunday-Saturday
 * @return string
 */
function translateDayOfWeek($dayOfWeek)
{
    return Piwik::translate('Intl_Day_Long_StandAlone_' . $dayOfWeek);
}

/**
 * Returns translated long name for month.
 *
 * @param int $month 1-12, for January-December
 * @return string
 */
function translateMonth($month)
{
    return Piwik::translate('Intl_Month_Long_StandAlone_' . $month);
}
