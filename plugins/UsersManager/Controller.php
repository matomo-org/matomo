<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Exception;
use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugins\LanguagesManager\API as APILanguagesManager;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\Login\SessionInitializer;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Tracker\IgnoreCookie;
use Piwik\Url;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    static function orderByName($a, $b)
    {
        return strcmp($a['name'], $b['name']);
    }

    /**
     * The "Manage Users and Permissions" Admin UI screen
     */
    function index()
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View('@UsersManager/index');

        $IdSitesAdmin = APISitesManager::getInstance()->getSitesIdWithAdminAccess();
        $idSiteSelected = 1;

        if (count($IdSitesAdmin) > 0) {
            $defaultWebsiteId = $IdSitesAdmin[0];
            $idSiteSelected = Common::getRequestVar('idSite', $defaultWebsiteId);
        }

        if ($idSiteSelected === 'all') {
            $usersAccessByWebsite = array();
            $defaultReportSiteName = Piwik::translate('UsersManager_ApplyToAllWebsites');
        } else {
            $usersAccessByWebsite = APIUsersManager::getInstance()->getUsersAccessFromSite($idSiteSelected);
            $defaultReportSiteName = Site::getNameFor($idSiteSelected);
        }

        // we dont want to display the user currently logged so that the user can't change his settings from admin to view...
        $currentlyLogged = Piwik::getCurrentUserLogin();
        $usersLogin = APIUsersManager::getInstance()->getUsersLogin();
        foreach ($usersLogin as $login) {
            if (!isset($usersAccessByWebsite[$login])) {
                $usersAccessByWebsite[$login] = 'noaccess';
            }
        }
        unset($usersAccessByWebsite[$currentlyLogged]);

        // $usersAccessByWebsite is not supposed to contain unexistant logins, but it does when upgrading from some old Piwik version
        foreach ($usersAccessByWebsite as $login => $access) {
            if (!in_array($login, $usersLogin)) {
                unset($usersAccessByWebsite[$login]);
                continue;
            }
        }

        ksort($usersAccessByWebsite);

        $users             = array();
        $superUsers        = array();
        $usersAliasByLogin = array();

        if (Piwik::isUserHasSomeAdminAccess()) {
            $view->showLastSeen = true;

            $users = APIUsersManager::getInstance()->getUsers();
            foreach ($users as $index => $user) {
                $usersAliasByLogin[$user['login']] = $user['alias'];

                $lastSeen = LastSeenTimeLogger::getLastSeenTimeForUser($user['login']);
                $users[$index]['last_seen'] = $lastSeen == 0
                                            ? false : MetricsFormatter::getPrettyTimeFromSeconds(time() - $lastSeen);
            }

            if (Piwik::hasUserSuperUserAccess()) {
                foreach ($users as $user) {
                    if ($user['superuser_access']) {
                        $superUsers[] = $user['login'];
                    }
                }
            }
        }

        $view->anonymousHasViewAccess = $this->hasAnonymousUserViewAccess($usersAccessByWebsite);
        $view->idSiteSelected = $idSiteSelected;
        $view->defaultReportSiteName = $defaultReportSiteName;
        $view->users = $users;
        $view->superUserLogins = $superUsers;
        $view->usersAliasByLogin = $usersAliasByLogin;
        $view->usersCount = count($users) - 1;
        $view->usersAccessByWebsite = $usersAccessByWebsite;
        $websites = APISitesManager::getInstance()->getSitesWithAdminAccess();
        uasort($websites, array('Piwik\Plugins\UsersManager\Controller', 'orderByName'));
        $view->websites = $websites;
        $this->setBasicVariablesView($view);

        return $view->render();
    }

    private function hasAnonymousUserViewAccess($usersAccessByWebsite)
    {
        $anonymousHasViewAccess = false;

        foreach ($usersAccessByWebsite as $login => $access) {
            if ($login == 'anonymous'
                && $access != 'noaccess'
            ) {
                $anonymousHasViewAccess = true;
            }
        }

        return $anonymousHasViewAccess;
    }

    /**
     * Returns default date for Piwik reports
     *
     * @param string $user
     * @return string today, yesterday, week, month, year
     */
    protected function getDefaultDateForUser($user)
    {
        return APIUsersManager::getInstance()->getUserPreference($user, APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE);
    }

    /**
     * Returns the enabled dates that users can select,
     * in their User Settings page "Report date to load by default"
     *
     * @throws
     * @return array
     */
    protected function getDefaultDates()
    {
        $dates = array(
            'today'      => Piwik::translate('General_Today'),
            'yesterday'  => Piwik::translate('General_Yesterday'),
            'previous7'  => Piwik::translate('General_PreviousDays', 7),
            'previous30' => Piwik::translate('General_PreviousDays', 30),
            'last7'      => Piwik::translate('General_LastDays', 7),
            'last30'     => Piwik::translate('General_LastDays', 30),
            'week'       => Piwik::translate('General_CurrentWeek'),
            'month'      => Piwik::translate('General_CurrentMonth'),
            'year'       => Piwik::translate('General_CurrentYear'),
        );

        $mappingDatesToPeriods = array(
            'today' => 'day',
            'yesterday' => 'day',
            'previous7' => 'range',
            'previous30' => 'range',
            'last7' => 'range',
            'last30' => 'range',
            'week' => 'week',
            'month' => 'month',
            'year' => 'year',
        );

        // assertion
        if (count($dates) != count($mappingDatesToPeriods)) {
            throw new Exception("some metadata is missing in getDefaultDates()");
        }

        $allowedPeriods = self::getEnabledPeriodsInUI();
        $allowedDates = array_intersect($mappingDatesToPeriods, $allowedPeriods);
        $dates = array_intersect_key($dates, $allowedDates);

        /**
         * Triggered when the list of available dates is requested, for example for the
         * User Settings > Report date to load by default.
         *
         * @param array &$dates Array of (date => translation)
         */
        Piwik::postEvent('UsersManager.getDefaultDates', array(&$dates));

        return $dates;
    }

    /**
     * The "User Settings" admin UI screen view
     */
    public function userSettings()
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View('@UsersManager/userSettings');

        $userLogin = Piwik::getCurrentUserLogin();
        $user = APIUsersManager::getInstance()->getUser($userLogin);
        $view->userAlias = $user['alias'];
        $view->userEmail = $user['email'];

        $view->ignoreSalt = $this->getIgnoreCookieSalt();

        $userPreferences = new UserPreferences();
        $defaultReport   = $userPreferences->getDefaultReport();

        if ($defaultReport === false) {
            $defaultReport = $userPreferences->getDefaultWebsiteId();
        }

        $view->defaultReport = $defaultReport;

        if ($defaultReport == 'MultiSites') {

            $defaultSiteId = $userPreferences->getDefaultWebsiteId();

            $view->defaultReportIdSite   = $defaultSiteId;
            $view->defaultReportSiteName = Site::getNameFor($defaultSiteId);
        } else {
            $view->defaultReportIdSite   = $defaultReport;
            $view->defaultReportSiteName = Site::getNameFor($defaultReport);
        }

        $view->defaultDate = $this->getDefaultDateForUser($userLogin);
        $view->availableDefaultDates = $this->getDefaultDates();

        $view->languages = APILanguagesManager::getInstance()->getAvailableLanguageNames();
        $view->currentLanguageCode = LanguagesManager::getLanguageCodeForCurrentUser();
        $view->ignoreCookieSet = IgnoreCookie::isIgnoreCookieFound();
        $this->initViewAnonymousUserSettings($view);
        $view->piwikHost = Url::getCurrentHost();
        $this->setBasicVariablesView($view);

        return $view->render();
    }

    public function setIgnoreCookie()
    {
        Piwik::checkUserHasSomeViewAccess();
        Piwik::checkUserIsNotAnonymous();

        $salt = Common::getRequestVar('ignoreSalt', false, 'string');
        if ($salt !== $this->getIgnoreCookieSalt()) {
            throw new Exception("Not authorized");
        }

        IgnoreCookie::setIgnoreCookie();
        Piwik::redirectToModule('UsersManager', 'userSettings', array('token_auth' => false));
    }

    /**
     * The Super User can modify Anonymous user settings
     * @param View $view
     */
    protected function initViewAnonymousUserSettings($view)
    {
        if (!Piwik::hasUserSuperUserAccess()) {
            return;
        }
        $userLogin = 'anonymous';

        // Which websites are available to the anonymous users?
        $anonymousSitesAccess = APIUsersManager::getInstance()->getSitesAccessFromUser($userLogin);
        $anonymousSites = array();
        foreach ($anonymousSitesAccess as $info) {
            $idSite = $info['site'];
            $site = APISitesManager::getInstance()->getSiteFromId($idSite);
            // Work around manual website deletion
            if (!empty($site)) {
                $anonymousSites[$idSite] = $site;
            }
        }
        $view->anonymousSites = $anonymousSites;

        // Which report is displayed by default to the anonymous user?
        $anonymousDefaultReport = APIUsersManager::getInstance()->getUserPreference($userLogin, APIUsersManager::PREFERENCE_DEFAULT_REPORT);
        if ($anonymousDefaultReport === false) {
            if (empty($anonymousSites)) {
                $anonymousDefaultReport = Piwik::getLoginPluginName();
            } else {
                // we manually imitate what would happen, in case the anonymous user logs in
                // and is redirected to the first website available to him in the list
                // @see getDefaultWebsiteId()
                reset($anonymousSites);
                $anonymousDefaultReport = key($anonymousSites);
            }
        }
        $view->anonymousDefaultReport = $anonymousDefaultReport;

        $view->anonymousDefaultDate = $this->getDefaultDateForUser($userLogin);
    }

    /**
     * Records settings for the anonymous users (default report, default date)
     */
    public function recordAnonymousUserSettings()
    {
        $response = new ResponseBuilder(Common::getRequestVar('format'));
        try {
            Piwik::checkUserHasSuperUserAccess();
            $this->checkTokenInUrl();

            $anonymousDefaultReport = Common::getRequestVar('anonymousDefaultReport');
            $anonymousDefaultDate = Common::getRequestVar('anonymousDefaultDate');
            $userLogin = 'anonymous';
            APIUsersManager::getInstance()->setUserPreference($userLogin,
                APIUsersManager::PREFERENCE_DEFAULT_REPORT,
                $anonymousDefaultReport);
            APIUsersManager::getInstance()->setUserPreference($userLogin,
                APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE,
                $anonymousDefaultDate);
            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }

        return $toReturn;
    }

    /**
     * Records settings from the "User Settings" page
     * @throws Exception
     */
    public function recordUserSettings()
    {
        $response = new ResponseBuilder(Common::getRequestVar('format'));
        try {
            $this->checkTokenInUrl();

            $defaultReport = Common::getRequestVar('defaultReport');
            $defaultDate = Common::getRequestVar('defaultDate');
            $language = Common::getRequestVar('language');
            $userLogin = Piwik::getCurrentUserLogin();

            $this->processPasswordChange($userLogin);

            LanguagesManager::setLanguageForSession($language);
            APILanguagesManager::getInstance()->setLanguageForUser($userLogin, $language);

            APIUsersManager::getInstance()->setUserPreference($userLogin,
                APIUsersManager::PREFERENCE_DEFAULT_REPORT,
                $defaultReport);
            APIUsersManager::getInstance()->setUserPreference($userLogin,
                APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE,
                $defaultDate);
            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }

        return $toReturn;
    }

    private function processPasswordChange($userLogin)
    {
        $alias = Common::getRequestVar('alias');
        $email = Common::getRequestVar('email');
        $newPassword = false;
        $password = Common::getRequestvar('password', false);
        $passwordBis = Common::getRequestvar('passwordBis', false);
        if (!empty($password)
            || !empty($passwordBis)
        ) {
            if ($password != $passwordBis) {
                throw new Exception(Piwik::translate('Login_PasswordsDoNotMatch'));
            }
            $newPassword = $password;
        }

        // UI disables password change on invalid host, but check here anyway
        if (!Url::isValidHost()
            && $newPassword !== false
        ) {
            throw new Exception("Cannot change password with untrusted hostname!");
        }

        APIUsersManager::getInstance()->updateUser($userLogin, $newPassword, $email, $alias);
        if ($newPassword !== false) {
            $newPassword = Common::unsanitizeInputValue($newPassword);
        }

        // logs the user in with the new password
        if ($newPassword !== false) {
            $sessionInitializer = new SessionInitializer();
            $auth = \Piwik\Registry::get('auth');
            $auth->setLogin($userLogin);
            $auth->setPassword($password);
            $sessionInitializer->initSession($auth, $rememberMe = false);
        }
    }

    /**
     * @return string
     */
    private function getIgnoreCookieSalt()
    {
        return md5(SettingsPiwik::getSalt());
    }
}
