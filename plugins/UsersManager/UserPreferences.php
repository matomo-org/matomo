<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UsersManager\API as APIUsersManager;

class UserPreferences
{
    /**
     * Returns default site ID that Piwik should load.
     *
     * _Note: This value is a Piwik setting set by each user._
     *
     * @return bool|int
     * @api
     */
    public function getDefaultWebsiteId()
    {
        $defaultReport = $this->getDefaultReport();

        if (is_numeric($defaultReport) && Piwik::isUserHasViewAccess($defaultReport)) {
            return $defaultReport;
        }

        $sitesId = APISitesManager::getInstance()->getSitesIdWithAtLeastViewAccess();

        if (!empty($sitesId)) {
            return $sitesId[0];
        }

        return false;
    }
    /**
     * Returns default site ID that Piwik should load.
     *
     * _Note: This value is a Piwik setting set by each user._
     *
     * @return bool|int
     * @api
     */
    public function getDefaultReport()
    {
        // User preference: default website ID to load
        $defaultReport = APIUsersManager::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), APIUsersManager::PREFERENCE_DEFAULT_REPORT);

        if (!is_numeric($defaultReport)) {
            return $defaultReport;
        }

        if ($defaultReport && Piwik::isUserHasViewAccess($defaultReport)) {
            return $defaultReport;
        }

        return false;
    }

    /**
     * Returns default date for Piwik reports.
     *
     * _Note: This value is a Piwik setting set by each user._
     *
     * @return string `'today'`, `'2010-01-01'`, etc.
     * @api
     */
    public function getDefaultDate()
    {
        // NOTE: a change in this function might mean a change in plugins/UsersManager/javascripts/usersSettings.js as well
        $userSettingsDate = APIUsersManager::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE);
        if ($userSettingsDate == 'yesterday') {
            return $userSettingsDate;
        }
        // if last7, last30, etc.
        if (strpos($userSettingsDate, 'last') === 0
            || strpos($userSettingsDate, 'previous') === 0
        ) {
            return $userSettingsDate;
        }

        return 'today';
    }

    /**
     * Returns default period type for Piwik reports.
     *
     * @return string `'day'`, `'week'`, `'month'`, `'year'` or `'range'`
     * @api
     */
    public function getDefaultPeriod()
    {
        $userSettingsDate = APIUsersManager::getInstance()->getUserPreference(Piwik::getCurrentUserLogin(), APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE);

        if ($userSettingsDate === false) {
            return Config::getInstance()->General['default_period'];
        }

        if (in_array($userSettingsDate, array('today', 'yesterday'))) {
            return 'day';
        }

        if (strpos($userSettingsDate, 'last') === 0
            || strpos($userSettingsDate, 'previous') === 0
        ) {
            return 'range';
        }

        return $userSettingsDate;
    }
}