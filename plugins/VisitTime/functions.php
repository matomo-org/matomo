<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\VisitTime;

use Piwik\Piwik;

function getTimeLabel($label)
{
    return sprintf(Piwik::translate('Intl_NHoursShort'), $label);
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
