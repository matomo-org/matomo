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
use Piwik\Access;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Tracker\Cache;

/**
 * The UsersManager API lets you Manage Users and their permissions to access specific websites.
 *
 * You can create users via "addUser", update existing users via "updateUser" and delete users via "deleteUser".
 * There are many ways to list users based on their login "getUser" and "getUsers", their email "getUserByEmail",
 * or which users have permission (view or admin) to access the specified websites "getUsersWithSiteAccess".
 *
 * Existing Permissions are listed given a login via "getSitesAccessFromUser", or a website ID via "getUsersAccessFromSite",
 * or you can list all users and websites for a given permission via "getUsersSitesFromAccess". Permissions are set and updated
 * via the method "setUserAccess".
 * See also the documentation about <a href='http://piwik.org/docs/manage-users/' rel='noreferrer' target='_blank'>Managing Users</a> in Piwik.
 */
class API extends \Piwik\Plugin\API
{
    const OPTION_NAME_PREFERENCE_SEPARATOR = '_';

    /**
     * @var Model
     */
    private $model;

    const PREFERENCE_DEFAULT_REPORT = 'defaultReport';
    const PREFERENCE_DEFAULT_REPORT_DATE = 'defaultReportDate';

    private static $instance = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * You can create your own Users Plugin to override this class.
     * Example of how you would overwrite the UsersManager_API with your own class:
     * Call the following in your plugin __construct() for example:
     *
     * StaticContainer::getContainer()->set('UsersManager_API', \Piwik\Plugins\MyCustomUsersManager\API::getInstance());
     *
     * @throws Exception
     * @return \Piwik\Plugins\UsersManager\API
     */
    public static function getInstance()
    {
        try {
            $instance = StaticContainer::get('UsersManager_API');
            if (!($instance instanceof API)) {
                // Exception is caught below and corrected
                throw new Exception('UsersManager_API must inherit API');
            }
            self::$instance = $instance;
            
        } catch (Exception $e) {
            self::$instance = StaticContainer::get('Piwik\Plugins\UsersManager\API');
            StaticContainer::getContainer()->set('UsersManager_API', self::$instance);
        }

        return self::$instance;
    }

    /**
     * Sets a user preference
     * @param string $userLogin
     * @param string $preferenceName
     * @param string $preferenceValue
     * @return void
     */
    public function setUserPreference($userLogin, $preferenceName, $preferenceValue)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);
        Option::set($this->getPreferenceId($userLogin, $preferenceName), $preferenceValue);
    }

    /**
     * Gets a user preference
     * @param string $userLogin
     * @param string $preferenceName
     * @return bool|string
     */
    public function getUserPreference($userLogin, $preferenceName)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);

        $optionValue = $this->getPreferenceValue($userLogin, $preferenceName);

        if ($optionValue !== false) {
            return $optionValue;
        }

        return $this->getDefaultUserPreference($preferenceName, $userLogin);
    }

    /**
     * Sets a user preference in the DB using the preference's default value.
     * @param string $userLogin
     * @param string $preferenceName
     * @ignore
     */
    public function initUserPreferenceWithDefault($userLogin, $preferenceName)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);

        $optionValue = $this->getPreferenceValue($userLogin, $preferenceName);

        if ($optionValue === false) {
            $defaultValue = $this->getDefaultUserPreference($preferenceName, $userLogin);

            if ($defaultValue !== false) {
                $this->setUserPreference($userLogin, $preferenceName, $defaultValue);
            }
        }
    }

    /**
     * Returns an array of Preferences
     * @param $preferenceNames array of preference names
     * @return array
     * @ignore
     */
    public function getAllUsersPreferences(array $preferenceNames)
    {
        Piwik::checkUserHasSuperUserAccess();

        $userPreferences = array();
        foreach($preferenceNames as $preferenceName) {
            $optionNameMatchAllUsers = $this->getPreferenceId('%', $preferenceName);
            $preferences = Option::getLike($optionNameMatchAllUsers);

            foreach($preferences as $optionName => $optionValue) {
                $lastUnderscore = strrpos($optionName, self::OPTION_NAME_PREFERENCE_SEPARATOR);
                $userName = substr($optionName, 0, $lastUnderscore);
                $preference = substr($optionName, $lastUnderscore + 1);
                $userPreferences[$userName][$preference] = $optionValue;
            }
        }
        return $userPreferences;
    }

    private function getPreferenceId($login, $preference)
    {
        if(false !== strpos($preference, self::OPTION_NAME_PREFERENCE_SEPARATOR)) {
            throw new Exception("Preference name cannot contain underscores.");
        }
        return $login . self::OPTION_NAME_PREFERENCE_SEPARATOR . $preference;
    }

    private function getPreferenceValue($userLogin, $preferenceName)
    {
        return Option::get($this->getPreferenceId($userLogin, $preferenceName));
    }

    private function getDefaultUserPreference($preferenceName, $login)
    {
        switch ($preferenceName) {
            case self::PREFERENCE_DEFAULT_REPORT:
                $viewableSiteIds = \Piwik\Plugins\SitesManager\API::getInstance()->getSitesIdWithAtLeastViewAccess($login);
                if (!empty($viewableSiteIds)) {
                    return reset($viewableSiteIds);
                }
                return false;
            case self::PREFERENCE_DEFAULT_REPORT_DATE:
                return Config::getInstance()->General['default_day'];
            default:
                return false;
        }
    }

    /**
     * Returns the list of all the users
     *
     * @param string $userLogins Comma separated list of users to select. If not specified, will return all users
     * @return array the list of all the users
     */
    public function getUsers($userLogins = '')
    {
        Piwik::checkUserHasSomeAdminAccess();

        $logins = array();
        if (!empty($userLogins)) {
            $logins = explode(',', $userLogins);
        }

        $users = $this->model->getUsers($logins);

        // Non Super user can only access login & alias
        if (!Piwik::hasUserSuperUserAccess()) {
            foreach ($users as &$user) {
                $user = array('login' => $user['login'], 'alias' => $user['alias']);
            }
        }

        return $users;
    }

    /**
     * Returns the list of all the users login
     *
     * @return array the list of all the users login
     */
    public function getUsersLogin()
    {
        Piwik::checkUserHasSomeAdminAccess();

        return $this->model->getUsersLogin();
    }

    /**
     * For each user, returns the list of website IDs where the user has the supplied $access level.
     * If a user doesn't have the given $access to any website IDs,
     * the user will not be in the returned array.
     *
     * @param string Access can have the following values : 'view' or 'admin'
     *
     * @return array    The returned array has the format
     *                    array(
     *                        login1 => array ( idsite1,idsite2),
     *                        login2 => array(idsite2),
     *                        ...
     *                    )
     */
    public function getUsersSitesFromAccess($access)
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkAccessType($access);

        return $this->model->getUsersSitesFromAccess($access);
    }

    /**
     * For each user, returns his access level for the given $idSite.
     * If a user doesn't have any access to the $idSite ('noaccess'),
     * the user will not be in the returned array.
     *
     * @param int $idSite website ID
     *
     * @return array    The returned array has the format
     *                    array(
     *                        login1 => 'view',
     *                        login2 => 'admin',
     *                        login3 => 'view',
     *                        ...
     *                    )
     */
    public function getUsersAccessFromSite($idSite)
    {
        Piwik::checkUserHasAdminAccess($idSite);

        return $this->model->getUsersAccessFromSite($idSite);
    }

    public function getUsersWithSiteAccess($idSite, $access)
    {
        Piwik::checkUserHasAdminAccess($idSite);
        $this->checkAccessType($access);

        $logins = $this->model->getUsersLoginWithSiteAccess($idSite, $access);

        if (empty($logins)) {
            return array();
        }

        $logins = implode(',', $logins);

        return $this->getUsers($logins);
    }

    /**
     * For each website ID, returns the access level of the given $userLogin.
     * If the user doesn't have any access to a website ('noaccess'),
     * this website will not be in the returned array.
     * If the user doesn't have any access, the returned array will be an empty array.
     *
     * @param string $userLogin User that has to be valid
     *
     * @return array    The returned array has the format
     *                    array(
     *                        idsite1 => 'view',
     *                        idsite2 => 'admin',
     *                        idsite3 => 'view',
     *                        ...
     *                    )
     */
    public function getSitesAccessFromUser($userLogin)
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkUserExists($userLogin);

        // Super users have 'admin' access for every site
        if (Piwik::hasTheUserSuperUserAccess($userLogin)) {
            $return = array();
            $siteManagerModel = new \Piwik\Plugins\SitesManager\Model();
            $sites = $siteManagerModel->getAllSites();
            foreach ($sites as $site) {
                $return[] = array(
                    'site' => $site['idsite'],
                    'access' => 'admin'
                );

            }
            return $return;
        }

        return $this->model->getSitesAccessFromUser($userLogin);
    }

    /**
     * Returns the user information (login, password md5, alias, email, date_registered, etc.)
     *
     * @param string $userLogin the user login
     *
     * @return array the user information
     */
    public function getUser($userLogin)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);
        $this->checkUserExists($userLogin);

        return $this->model->getUser($userLogin);
    }

    /**
     * Returns the user information (login, password md5, alias, email, date_registered, etc.)
     *
     * @param string $userEmail the user email
     *
     * @return array the user information
     */
    public function getUserByEmail($userEmail)
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkUserEmailExists($userEmail);

        return $this->model->getUserByEmail($userEmail);
    }

    private function checkLogin($userLogin)
    {
        if ($this->userExists($userLogin)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionLoginExists', $userLogin));
        }

        Piwik::checkValidLoginString($userLogin);
    }

    private function checkEmail($email)
    {
        if ($this->userEmailExists($email)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionEmailExists', $email));
        }

        if (!Piwik::isValidEmailString($email)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionInvalidEmail'));
        }
    }

    private function getCleanAlias($alias, $userLogin)
    {
        if (empty($alias)) {
            $alias = $userLogin;
        }

        return $alias;
    }

    /**
     * Add a user in the database.
     * A user is defined by
     * - a login that has to be unique and valid
     * - a password that has to be valid
     * - an alias
     * - an email that has to be in a correct format
     *
     * @see userExists()
     * @see isValidLoginString()
     * @see isValidPasswordString()
     * @see isValidEmailString()
     *
     * @exception in case of an invalid parameter
     */
    public function addUser($userLogin, $password, $email, $alias = false, $_isPasswordHashed = false)
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkLogin($userLogin);
        $this->checkEmail($email);

        $password = Common::unsanitizeInputValue($password);

        if (!$_isPasswordHashed) {
            UsersManager::checkPassword($password);

            $passwordTransformed = UsersManager::getPasswordHash($password);
        } else {
            $passwordTransformed = $password;
        }

        $alias = $this->getCleanAlias($alias, $userLogin);

        $token_auth = $this->getTokenAuth($userLogin, $passwordTransformed);

        $this->model->addUser($userLogin, $passwordTransformed, $email, $alias, $token_auth, Date::now()->getDatetime());

        // we reload the access list which doesn't yet take in consideration this new user
        Access::getInstance()->reloadAccess();
        Cache::deleteTrackerCache();

        /**
         * Triggered after a new user is created.
         *
         * @param string $userLogin The new user's login handle.
         */
        Piwik::postEvent('UsersManager.addUser.end', array($userLogin, $email, $password, $alias));
    }

    /**
     * Enable or disable Super user access to the given user login. Note: When granting Super User access all previous
     * permissions of the user will be removed as the user gains access to everything.
     *
     * @param string   $userLogin          the user login.
     * @param bool|int $hasSuperUserAccess true or '1' to grant Super User access, false or '0' to remove Super User
     *                                     access.
     * @throws \Exception
     */
    public function setSuperUserAccess($userLogin, $hasSuperUserAccess)
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkUserIsNotAnonymous($userLogin);
        $this->checkUserExists($userLogin);

        if (!$hasSuperUserAccess && $this->isUserTheOnlyUserHavingSuperUserAccess($userLogin)) {
            $message = Piwik::translate("UsersManager_ExceptionRemoveSuperUserAccessOnlySuperUser", $userLogin)
                        . " "
                        . Piwik::translate("UsersManager_ExceptionYouMustGrantSuperUserAccessFirst");
            throw new Exception($message);
        }

        $this->model->deleteUserAccess($userLogin);
        $this->model->setSuperUserAccess($userLogin, $hasSuperUserAccess);
    }

    /**
     * Detect whether the current user has super user access or not.
     *
     * @return bool
     */
    public function hasSuperUserAccess()
    {
        return Piwik::hasUserSuperUserAccess();
    }

    /**
     * Returns a list of all Super Users containing there userLogin and email address.
     *
     * @return array
     */
    public function getUsersHavingSuperUserAccess()
    {
        Piwik::checkUserIsNotAnonymous();

        $users = $this->model->getUsersHavingSuperUserAccess();

        foreach($users as &$user) {
            // remove token_auth in API response
            unset($user['token_auth']);
        }

        return $users;
    }

    /**
     * Updates a user in the database.
     * Only login and password are required (case when we update the password).
     * When the password changes, the key token for this user will change, which could break
     * its API calls.
     *
     * @see addUser() for all the parameters
     */
    public function updateUser($userLogin, $password = false, $email = false, $alias = false,
                               $_isPasswordHashed = false)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);
        $this->checkUserIsNotAnonymous($userLogin);
        $userInfo = $this->getUser($userLogin);
        $passwordHasBeenUpdated = false;

        if (empty($password)) {
            $password = $userInfo['password'];
        } else {
            $password = Common::unsanitizeInputValue($password);
            if (!$_isPasswordHashed) {
                UsersManager::checkPassword($password);
                $password = UsersManager::getPasswordHash($password);
            }

            $passwordHasBeenUpdated = true;
        }

        if (empty($alias)) {
            $alias = $userInfo['alias'];
        }

        if (empty($email)) {
            $email = $userInfo['email'];
        }

        if ($email != $userInfo['email']) {
            $this->checkEmail($email);
        }

        $alias      = $this->getCleanAlias($alias, $userLogin);
        $token_auth = $this->getTokenAuth($userLogin, $password);

        $this->model->updateUser($userLogin, $password, $email, $alias, $token_auth);

        Cache::deleteTrackerCache();

        /**
         * Triggered after an existing user has been updated.
         * Event notify about password change.
         *
         * @param string $userLogin The user's login handle.
         * @param boolean $passwordHasBeenUpdated Flag containing information about password change.
         */
        Piwik::postEvent('UsersManager.updateUser.end', array($userLogin, $passwordHasBeenUpdated, $email, $password, $alias));
    }

    /**
     * Delete a user and all its access, given its login.
     *
     * @param string $userLogin the user login.
     *
     * @throws Exception if the user doesn't exist
     *
     * @return bool true on success
     */
    public function deleteUser($userLogin)
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkUserIsNotAnonymous($userLogin);

        if (!$this->userExists($userLogin)) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionDeleteDoesNotExist", $userLogin));
        }

        if ($this->isUserTheOnlyUserHavingSuperUserAccess($userLogin)) {
            $message = Piwik::translate("UsersManager_ExceptionDeleteOnlyUserWithSuperUserAccess", $userLogin)
                        . " "
                        . Piwik::translate("UsersManager_ExceptionYouMustGrantSuperUserAccessFirst");
            throw new Exception($message);
        }

        $this->model->deleteUserOnly($userLogin);
        $this->model->deleteUserAccess($userLogin);

        Cache::deleteTrackerCache();
    }

    /**
     * Returns true if the given userLogin is known in the database
     *
     * @param string $userLogin
     * @return bool true if the user is known
     */
    public function userExists($userLogin)
    {
        if ($userLogin == 'anonymous') {
            return true;
        }

        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        if ($userLogin == Piwik::getCurrentUserLogin()) {
            return true;
        }

        return $this->model->userExists($userLogin);
    }

    /**
     * Returns true if user with given email (userEmail) is known in the database, or the Super User
     *
     * @param string $userEmail
     * @return bool true if the user is known
     */
    public function userEmailExists($userEmail)
    {
        Piwik::checkUserIsNotAnonymous();

        return $this->model->userEmailExists($userEmail);
    }

    /**
     * Set an access level to a given user for a list of websites ID.
     *
     * If access = 'noaccess' the current access (if any) will be deleted.
     * If access = 'view' or 'admin' the current access level is deleted and updated with the new value.
     *
     * @param string $userLogin The user login
     * @param string $access Access to grant. Must have one of the following value : noaccess, view, admin
     * @param int|array $idSites The array of idSites on which to apply the access level for the user.
     *       If the value is "all" then we apply the access level to all the websites ID for which the current authentificated user has an 'admin' access.
     *
     * @throws Exception if the user doesn't exist
     * @throws Exception if the access parameter doesn't have a correct value
     * @throws Exception if any of the given website ID doesn't exist
     *
     * @return bool true on success
     */
    public function setUserAccess($userLogin, $access, $idSites)
    {
        $this->checkAccessType($access);
        $this->checkUserExists($userLogin);
        $this->checkUserHasNotSuperUserAccess($userLogin);

        if ($userLogin == 'anonymous'
            && $access == 'admin'
        ) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionAdminAnonymous"));
        }

        // in case idSites is all we grant access to all the websites on which the current connected user has an 'admin' access
        if ($idSites === 'all') {
            $idSites = \Piwik\Plugins\SitesManager\API::getInstance()->getSitesIdWithAdminAccess();
        } // in case the idSites is an integer we build an array
        else {
            $idSites = Site::getIdSitesFromIdSitesString($idSites);
        }

        if (empty($idSites)) {
            throw new Exception('Specify at least one website ID in &idSites=');
        }
        // it is possible to set user access on websites only for the websites admin
        // basically an admin can give the view or the admin access to any user for the websites he manages
        Piwik::checkUserHasAdminAccess($idSites);

        $this->model->deleteUserAccess($userLogin, $idSites);

        // if the access is noaccess then we don't save it as this is the default value
        // when no access are specified
        if ($access != 'noaccess') {
            $this->model->addUserAccess($userLogin, $access, $idSites);
        } else {
            if (!empty($idSites) && !is_array($idSites)) {
                $idSites = array($idSites);
            }

            Piwik::postEvent('UsersManager.removeSiteAccess', array($userLogin, $idSites));
        }

        // we reload the access list which doesn't yet take in consideration this new user access
        Access::getInstance()->reloadAccess();
        Cache::deleteTrackerCache();
    }

    /**
     * Throws an exception is the user login doesn't exist
     *
     * @param string $userLogin user login
     * @throws Exception if the user doesn't exist
     */
    private function checkUserExists($userLogin)
    {
        if (!$this->userExists($userLogin)) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionUserDoesNotExist", $userLogin));
        }
    }

    /**
     * Throws an exception is the user email cannot be found
     *
     * @param string $userEmail user email
     * @throws Exception if the user doesn't exist
     */
    private function checkUserEmailExists($userEmail)
    {
        if (!$this->userEmailExists($userEmail)) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionUserDoesNotExist", $userEmail));
        }
    }

    private function checkUserIsNotAnonymous($userLogin)
    {
        if ($userLogin == 'anonymous') {
            throw new Exception(Piwik::translate("UsersManager_ExceptionEditAnonymous"));
        }
    }

    private function checkUserHasNotSuperUserAccess($userLogin)
    {
        if (Piwik::hasTheUserSuperUserAccess($userLogin)) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionSuperUserAccess"));
        }
    }

    private function checkAccessType($access)
    {
        $accessList = Access::getListAccess();

        // do not allow to set the superUser access
        unset($accessList[array_search("superuser", $accessList)]);

        if (!in_array($access, $accessList)) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionAccessValues", implode(", ", $accessList)));
        }
    }

    private function isUserTheOnlyUserHavingSuperUserAccess($userLogin)
    {
        $superUsers = $this->getUsersHavingSuperUserAccess();

        return 1 >= count($superUsers) && Piwik::hasTheUserSuperUserAccess($userLogin);
    }

    /**
     * Generates a unique MD5 for the given login & password
     *
     * @param string $userLogin Login
     * @param string $md5Password MD5ied string of the password
     * @throws Exception
     * @return string
     */
    public function getTokenAuth($userLogin, $md5Password)
    {
        if (strlen($md5Password) != 32) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionPasswordMD5HashExpected'));
        }

        return md5($userLogin . $md5Password);
    }
}
