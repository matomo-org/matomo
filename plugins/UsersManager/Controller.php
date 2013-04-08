<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UsersManager
 */

/**
 *
 * @package Piwik_UsersManager
 */
class Piwik_UsersManager_Controller extends Piwik_Controller_Admin
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

        $view = Piwik_View::factory('UsersManager');

        $IdSitesAdmin = Piwik_SitesManager_API::getInstance()->getSitesIdWithAdminAccess();
        $idSiteSelected = 1;

        if (count($IdSitesAdmin) > 0) {
            $defaultWebsiteId = $IdSitesAdmin[0];
            $idSiteSelected = Piwik_Common::getRequestVar('idSite', $defaultWebsiteId);
        }

        if ($idSiteSelected === 'all') {
            $usersAccessByWebsite = array();
            $defaultReportSiteName = Piwik_Translate('UsersManager_ApplyToAllWebsites');
        } else {
            $usersAccessByWebsite = Piwik_UsersManager_API::getInstance()->getUsersAccessFromSite($idSiteSelected);
            $defaultReportSiteName = Piwik_Site::getNameFor($idSiteSelected);
        }

        // we dont want to display the user currently logged so that the user can't change his settings from admin to view...
        $currentlyLogged = Piwik::getCurrentUserLogin();
        $usersLogin = Piwik_UsersManager_API::getInstance()->getUsersLogin();
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

        $users = array();
        $usersAliasByLogin = array();
        if (Piwik::isUserHasSomeAdminAccess()) {
            $users = Piwik_UsersManager_API::getInstance()->getUsers();
            foreach ($users as $user) {
                $usersAliasByLogin[$user['login']] = $user['alias'];
            }
        }
        $view->anonymousHasViewAccess = $this->hasAnonymousUserViewAccess($usersAccessByWebsite);
        $view->idSiteSelected = $idSiteSelected;
        $view->defaultReportSiteName = $defaultReportSiteName;
        $view->users = $users;
        $view->usersAliasByLogin = $usersAliasByLogin;
        $view->usersCount = count($users) - 1;
        $view->usersAccessByWebsite = $usersAccessByWebsite;
        $websites = Piwik_SitesManager_API::getInstance()->getSitesWithAdminAccess();
        uasort($websites, array('Piwik_UsersManager_Controller', 'orderByName'));
        $view->websites = $websites;
        $this->setBasicVariablesView($view);
        $view->menu = Piwik_GetAdminMenu();
        echo $view->render();
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
        return Piwik_UsersManager_API::getInstance()->getUserPreference($user, Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE);
    }

    /**
     * The "User Settings" admin UI screen view
     */
    public function userSettings()
    {
        Piwik::checkUserIsNotAnonymous();

        $view = Piwik_View::factory('userSettings');

        $userLogin = Piwik::getCurrentUserLogin();
        if (Piwik::isUserIsSuperUser()) {
            $view->userAlias = $userLogin;
            $view->userEmail = Piwik::getSuperUserEmail();
            if (!Piwik_Config::getInstance()->isFileWritable()) {
                $view->configFileNotWritable = true;
            }
        } else {
            $user = Piwik_UsersManager_API::getInstance()->getUser($userLogin);
            $view->userAlias = $user['alias'];
            $view->userEmail = $user['email'];
        }

        $defaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference($userLogin, Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
        if ($defaultReport === false) {
            $defaultReport = $this->getDefaultWebsiteId();
        }
        $view->defaultReport = $defaultReport;

        if ($defaultReport == 'MultiSites') {
            $view->defaultReportSiteName = Piwik_Site::getNameFor($this->getDefaultWebsiteId());
        } else {
            $view->defaultReportSiteName = Piwik_Site::getNameFor($defaultReport);
        }

        $view->defaultDate = $this->getDefaultDateForUser($userLogin);
        $view->availableDefaultDates = array(
            'today'      => Piwik_Translate('General_Today'),
            'yesterday'  => Piwik_Translate('General_Yesterday'),
            'previous7'  => Piwik_Translate('General_PreviousDays', 7),
            'previous30' => Piwik_Translate('General_PreviousDays', 30),
            'last7'      => Piwik_Translate('General_LastDays', 7),
            'last30'     => Piwik_Translate('General_LastDays', 30),
            'week'       => Piwik_Translate('General_CurrentWeek'),
            'month'      => Piwik_Translate('General_CurrentMonth'),
            'year'       => Piwik_Translate('General_CurrentYear'),
        );

        $view->ignoreCookieSet = Piwik_Tracker_IgnoreCookie::isIgnoreCookieFound();
        $this->initViewAnonymousUserSettings($view);
        $view->piwikHost = Piwik_Url::getCurrentHost();
        $this->setBasicVariablesView($view);
        $view->menu = Piwik_GetAdminMenu();
        echo $view->render();
    }

    public function setIgnoreCookie()
    {
        Piwik::checkUserHasSomeViewAccess();
        Piwik::checkUserIsNotAnonymous();
        $this->checkTokenInUrl();

        Piwik_Tracker_IgnoreCookie::setIgnoreCookie();
        Piwik::redirectToModule('UsersManager', 'userSettings', array('token_auth' => false));
    }

    /**
     * The Super User can modify Anonymous user settings
     * @param Piwik_View $view
     */
    protected function initViewAnonymousUserSettings($view)
    {
        if (!Piwik::isUserIsSuperUser()) {
            return;
        }
        $userLogin = 'anonymous';

        // Which websites are available to the anonymous users?
        $anonymousSitesAccess = Piwik_UsersManager_API::getInstance()->getSitesAccessFromUser($userLogin);
        $anonymousSites = array();
        foreach ($anonymousSitesAccess as $info) {
            $idSite = $info['site'];
            $site = Piwik_SitesManager_API::getInstance()->getSiteFromId($idSite);
            // Work around manual website deletion
            if (!empty($site)) {
                $anonymousSites[$idSite] = $site;
            }
        }
        $view->anonymousSites = $anonymousSites;

        // Which report is displayed by default to the anonymous user?
        $anonymousDefaultReport = Piwik_UsersManager_API::getInstance()->getUserPreference($userLogin, Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT);
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
        $response = new Piwik_API_ResponseBuilder(Piwik_Common::getRequestVar('format'));
        try {
            Piwik::checkUserIsSuperUser();
            $this->checkTokenInUrl();

            $anonymousDefaultReport = Piwik_Common::getRequestVar('anonymousDefaultReport');
            $anonymousDefaultDate = Piwik_Common::getRequestVar('anonymousDefaultDate');
            $userLogin = 'anonymous';
            Piwik_UsersManager_API::getInstance()->setUserPreference($userLogin,
                Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT,
                $anonymousDefaultReport);
            Piwik_UsersManager_API::getInstance()->setUserPreference($userLogin,
                Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE,
                $anonymousDefaultDate);
            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }
        echo $toReturn;
    }

    /**
     * Records settings from the "User Settings" page
     * @throws Exception
     */
    public function recordUserSettings()
    {
        $response = new Piwik_API_ResponseBuilder(Piwik_Common::getRequestVar('format'));
        try {
            $this->checkTokenInUrl();

            $alias = Piwik_Common::getRequestVar('alias');
            $email = Piwik_Common::getRequestVar('email');
            $defaultReport = Piwik_Common::getRequestVar('defaultReport');
            $defaultDate = Piwik_Common::getRequestVar('defaultDate');

            $newPassword = false;
            $password = Piwik_Common::getRequestvar('password', false);
            $passwordBis = Piwik_Common::getRequestvar('passwordBis', false);
            if (!empty($password)
                || !empty($passwordBis)
            ) {
                if ($password != $passwordBis) {
                    throw new Exception(Piwik_Translate('Login_PasswordsDoNotMatch'));
                }
                $newPassword = $password;
            }

            // UI disables password change on invalid host, but check here anyway
            if (!Piwik_Url::isValidHost()
                && $newPassword !== false
            ) {
                throw new Exception("Cannot change password with untrusted hostname!");
            }

            $userLogin = Piwik::getCurrentUserLogin();
            if (Piwik::isUserIsSuperUser()) {
                $superUser = Piwik_Config::getInstance()->superuser;
                $updatedSuperUser = false;

                if ($newPassword !== false) {
                    $newPassword = Piwik_Common::unsanitizeInputValue($newPassword);
                    $md5PasswordSuperUser = md5($newPassword);
                    $superUser['password'] = $md5PasswordSuperUser;
                    $updatedSuperUser = true;
                }
                if ($superUser['email'] != $email) {
                    $superUser['email'] = $email;
                    $updatedSuperUser = true;
                }
                if ($updatedSuperUser) {
                    Piwik_Config::getInstance()->superuser = $superUser;
                    Piwik_Config::getInstance()->forceSave();
                }
            } else {
                Piwik_UsersManager_API::getInstance()->updateUser($userLogin, $newPassword, $email, $alias);
                if ($newPassword !== false) {
                    $newPassword = Piwik_Common::unsanitizeInputValue($newPassword);
                }
            }

            // logs the user in with the new password
            if ($newPassword !== false) {
                $info = array(
                    'login'       => $userLogin,
                    'md5Password' => md5($newPassword),
                    'rememberMe'  => false,
                );
                Piwik_PostEvent('Login.initSession', $info);
            }

            Piwik_UsersManager_API::getInstance()->setUserPreference($userLogin,
                Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT,
                $defaultReport);
            Piwik_UsersManager_API::getInstance()->setUserPreference($userLogin,
                Piwik_UsersManager_API::PREFERENCE_DEFAULT_REPORT_DATE,
                $defaultDate);
            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }
        echo $toReturn;
    }
}
