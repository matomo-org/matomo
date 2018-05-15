<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\UsersManager;

use Piwik\Access;

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
        $this->daily("setUserDefaultReportPreference");
    }

    public function setUserDefaultReportPreference()
    {
        // We initialize the default report user preference for each user (if it hasn't been inited before) for performance,
        // doing this lets us avoid loading all siteIds (which can be 50k or more) when this preference is requested.
        // getting the user preference can be called quite often when generating links etc (to get defaultWebsiteId).
        $usersModel = $this->usersModel;
        $usersManagerApi = $this->usersManagerApi;
        Access::getInstance()->doAsSuperUser(function () use ($usersModel, $usersManagerApi) {
            $allUsers = $usersModel->getUsers(array());
            foreach ($allUsers as $user) {
                $usersManagerApi->initUserPreferenceWithDefault($user['login'], API::PREFERENCE_DEFAULT_REPORT);
            }
        });
    }
}
