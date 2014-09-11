<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Piwik\Common;
use Piwik\Option;
use Piwik\Piwik;

/**
 * Class that logs the time the current user is accessing the current resource (which
 * is 'now') so it can be retrieved later.
 */
class LastSeenTimeLogger
{
    const OPTION_PREFIX = 'UsersManager.lastSeen.';

    /**
     * The amount of time in seconds that a last seen value is considered valid. We don't want
     * to update the database for every request made by every user, so we only do it if the time
     * has been at least this many seconds from the last known time.
     */
    const LAST_TIME_SAVE_DELTA = 300;

    /**
     * Saves the current time for a user as an option if the current request is for something
     * in the reporting UI, the current user is not anonymous and the time hasn't been saved
     * in the last 5 minutes.
     */
    public function logCurrentUserLastSeenTime()
    {
        $module = Common::getRequestVar('module', false);
        $currentUserLogin = Piwik::getCurrentUserLogin();

        // only log time for non-anonymous visits to the reporting UI
        if ($module == 'API'
            || $module == 'Proxy'
            || $currentUserLogin == 'anonymous'
        ) {
            return;
        }

        // get the last known time
        $optionName = self::OPTION_PREFIX . $currentUserLogin;
        $lastSeen = Option::get($optionName);

        // do not log if last known time is less than N minutes from now (so we don't make too many
        // queries)
        if (time() - $lastSeen <= self::LAST_TIME_SAVE_DELTA) {
            return;
        }

        // log last seen time (Note: autoload is important so the Option::get above does not result in
        // a separate query)
        Option::set($optionName, time(), $autoload = 1);
    }

    /**
     * Returns the time a user was last seen or `false` if the user has never logged in.
     */
    public static function getLastSeenTimeForUser($userName)
    {
        $optionName = self::OPTION_PREFIX . $userName;
        return Option::get($optionName);
    }
}