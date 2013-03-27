<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package SmartyPlugins
 */

/**
 * Returns a string that displays the number of days and hours from a number of seconds
 *
 * How to use:
 * {4200|sumtime} will display '1h 10min'
 *
 * Examples:
 * - 10 gives "10s"
 * - 4200 gives "1h 10min"
 * - 86400 gives "1 day"
 * - 90600 gives "1 day 1h" (it is exactly 1day 1h 10min but we truncate)
 *
 * @param int $numberOfSeconds
 * @return string
 */
function smarty_modifier_sumtime($numberOfSeconds)
{
    return Piwik::getPrettyTimeFromSeconds($numberOfSeconds);
}
