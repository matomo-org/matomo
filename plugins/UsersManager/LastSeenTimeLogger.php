<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager;

use Piwik\Common;
use Piwik\Date;
use Piwik\Piwik;

/**
 * Class that logs the time the current user is accessing the current resource (which is 'now')
 * so it can be retrieved later.
 */
class LastSeenTimeLogger
{
    /**
     * The amount of time in seconds that a last seen value is considered valid. We don't want
     * to update the database for every request made by every user, so we only do it if the time
     * has been at least this many seconds from the last known time.
     */
    public const LAST_TIME_SAVE_DELTA = 300;

    /**
     * Returns the time a user was last seen or `null` if the user has never logged in
     */
    public static function getLastSeenTimeForUser($userName): ?int
    {
        try {
            $userModel = new Model();
            return $userModel->getLastSeenTimestamp($userName);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Returns the last seen time for all users if it has been set
     */
    public static function getLastSeenTimesForAllUsers(): array
    {
        try {
            $userModel = new Model();
            return $userModel->getLastSeenTimestampForAllSeenUsers();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Saves the current time for a user as an option if the current request is for something
     * in the reporting UI, the current user is not anonymous, and the time hasn't been saved
     * in the last 5 minutes.
     */
    public static function logCurrentUserLastSeenTime()
    {
        $module = Common::getRequestVar('module', '');
        $currentUserLogin = Piwik::getCurrentUserLogin();

        // only log time for non-anonymous visits to the reporting UI
        if (
            $module == 'API'
            || $module == 'Proxy'
            || Piwik::isUserIsAnonymous()
        ) {
            return;
        }


        try {
            // get the last known time
            $lastSeen = self::getLastSeenTimeForUser($currentUserLogin);

            // do not log if the last known time is less than N seconds from now (so we don't make too many queries)
            if ($lastSeen && (time() - $lastSeen <= self::LAST_TIME_SAVE_DELTA)) {
                return;
            }

            // log last seen time
            $userModel = new Model();
            $userModel->setLastSeenDatetime($currentUserLogin, Date::factory('now')->getDatetime());
        } catch (\Exception $e) {
            // do nothing if getting or setting the timestamp fails, e.g. during an upgrade
        }
    }
}
