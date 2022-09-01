<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager;

use Piwik\Access;
use Piwik\Date;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var Model
     */
    private $usersModel;

    /**
     * @param API
     */
    private $usersManagerApi;

    public function __construct(Model $usersModel, API $usersManagerApi)
    {
        $this->usersModel = $usersModel;
        $this->usersManagerApi = $usersManagerApi;
    }

    public function schedule()
    {
        $this->daily("cleanupExpiredTokens");
        $this->daily("setUserDefaultReportPreference");
        $this->daily("cleanUpExpiredInvites");
    }

    public function cleanupExpiredTokens()
    {
        $this->usersModel->deleteExpiredTokens(Date::now()->getDatetime());
    }

    public function cleanUpExpiredInvites()
    {
        // Expired invites will be removed after 3 days, so there's a chance to resend an invite before it's removed.

        $expiredInvites = $this->usersModel->getExpiredInvites(Date::now()->subDay(3)->getDatetime());

        foreach ($expiredInvites as $expiredInvite) {
            try {
                $this->usersModel->deleteUser($expiredInvite['login']);
            } catch (\Exception $e) {
                // ignore possible errors thrown during delete user event
            }
        }
    }

    public function setUserDefaultReportPreference()
    {
        // We initialize the default report user preference for each user (if it hasn't been inited before) for performance,
        // doing this lets us avoid loading all siteIds (which can be 50k or more) when this preference is requested.
        // getting the user preference can be called quite often when generating links etc (to get defaultWebsiteId).
        $usersModel = $this->usersModel;
        $usersManagerApi = $this->usersManagerApi;
        Access::getInstance()->doAsSuperUser(function () use ($usersModel, $usersManagerApi) {
            $allUsers = $usersModel->getUsers([]);
            foreach ($allUsers as $user) {
                $usersManagerApi->initUserPreferenceWithDefault($user['login'], API::PREFERENCE_DEFAULT_REPORT);
            }
        });
    }
}
