<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package UsersManager
 */
namespace Piwik\Plugins\UsersManager;

use Exception;
use Piwik\Access;
use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
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
 * See also the documentation about <a href='http://piwik.org/docs/manage-users/' target='_blank'>Managing Users</a> in Piwik.
 * @package UsersManager
 */
class API extends \Piwik\Plugin\API
{
    const PREFERENCE_DEFAULT_REPORT = 'defaultReport';
    const PREFERENCE_DEFAULT_REPORT_DATE = 'defaultReportDate';

    static private $instance = null;

    /**
     * You can create your own Users Plugin to override this class.
     * Example of how you would overwrite the UsersManager_API with your own class:
     * Call the following in your plugin __construct() for example:
     *
     * Registry::set('UsersManager_API',Piwik_MyCustomUsersManager_API::getInstance());
     *
     * @throws Exception
     * @return \Piwik\Plugins\UsersManager\API
     */
    static public function getInstance()
    {
        try {
            $instance = \Piwik\Registry::get('UsersManager_API');
            if (!($instance instanceof API)) {
                // Exception is caught below and corrected
                throw new Exception('UsersManager_API must inherit API');
            }
            self::$instance = $instance;
        } catch (Exception $e) {
            self::$instance = new self;
            \Piwik\Registry::set('UsersManager_API', self::$instance);
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
        Piwik::checkUserIsSuperUserOrTheUser($userLogin);
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
        Piwik::checkUserIsSuperUserOrTheUser($userLogin);

        $optionValue = Option::get($this->getPreferenceId($userLogin, $preferenceName));
        if ($optionValue !== false) {
            return $optionValue;
        }
        return $this->getDefaultUserPreference($preferenceName, $userLogin);
    }

    private function getPreferenceId($login, $preference)
    {
        return $login . '_' . $preference;
    }

    private function getDefaultUserPreference($preferenceName, $login)
    {
        switch ($preferenceName) {
            case self::PREFERENCE_DEFAULT_REPORT:
                $viewableSiteIds = \Piwik\Plugins\SitesManager\API::getInstance()->getSitesIdWithAtLeastViewAccess($login);
                return reset($viewableSiteIds);
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

        $where = '';
        $bind = array();
        if (!empty($userLogins)) {
            $userLogins = explode(',', $userLogins);
            $where = 'WHERE login IN (' . Common::getSqlStringFieldsArray($userLogins) . ')';
            $bind = $userLogins;
        }
        $db = Db::get();
        $users = $db->fetchAll("SELECT *
								FROM " . Common::prefixTable("user") . "
								$where
								ORDER BY login ASC", $bind);
        // Non Super user can only access login & alias
        if (!Piwik::isUserIsSuperUser()) {
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

        $db = Db::get();
        $users = $db->fetchAll("SELECT login
								FROM " . Common::prefixTable("user") . "
								ORDER BY login ASC");
        $return = array();
        foreach ($users as $login) {
            $return[] = $login['login'];
        }
        return $return;
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
        Piwik::checkUserIsSuperUser();

        $this->checkAccessType($access);

        $db = Db::get();
        $users = $db->fetchAll("SELECT login,idsite
								FROM " . Common::prefixTable("access")
            . " WHERE access = ?
								ORDER BY login, idsite", $access);
        $return = array();
        foreach ($users as $user) {
            $return[$user['login']][] = $user['idsite'];
        }
        return $return;
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

        $db = Db::get();
        $users = $db->fetchAll("SELECT login,access
								FROM " . Common::prefixTable("access")
            . " WHERE idsite = ?", $idSite);
        $return = array();
        foreach ($users as $user) {
            $return[$user['login']] = $user['access'];
        }
        return $return;
    }

    public function getUsersWithSiteAccess($idSite, $access)
    {
        Piwik::checkUserHasAdminAccess($idSite);
        $this->checkAccessType($access);

        $db = Db::get();
        $users = $db->fetchAll("SELECT login
								FROM " . Common::prefixTable("access")
            . " WHERE idsite = ? AND access = ?", array($idSite, $access));
        $logins = array();
        foreach ($users as $user) {
            $logins[] = $user['login'];
        }
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
        Piwik::checkUserIsSuperUser();
        $this->checkUserExists($userLogin);
        $this->checkUserIsNotSuperUser($userLogin);

        $db = Db::get();
        $users = $db->fetchAll("SELECT idsite,access
								FROM " . Common::prefixTable("access")
            . " WHERE login = ?", $userLogin);
        $return = array();
        foreach ($users as $user) {
            $return[] = array(
                'site'   => $user['idsite'],
                'access' => $user['access'],
            );
        }
        return $return;
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
        Piwik::checkUserIsSuperUserOrTheUser($userLogin);
        $this->checkUserExists($userLogin);
        $this->checkUserIsNotSuperUser($userLogin);

        $db = Db::get();
        $user = $db->fetchRow("SELECT *
								FROM " . Common::prefixTable("user")
            . " WHERE login = ?", $userLogin);
        return $user;
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
        Piwik::checkUserIsSuperUser();
        $this->checkUserEmailExists($userEmail);

        $db = Db::get();
        $user = $db->fetchRow("SELECT *
								FROM " . Common::prefixTable("user")
            . " WHERE email = ?", $userEmail);
        return $user;
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
    public function addUser($userLogin, $password, $email, $alias = false)
    {
        Piwik::checkUserIsSuperUser();

        $this->checkLogin($userLogin);
        $this->checkUserIsNotSuperUser($userLogin);
        $this->checkEmail($email);

        $password = Common::unsanitizeInputValue($password);
        UsersManager::checkPassword($password);

        $alias = $this->getCleanAlias($alias, $userLogin);
        $passwordTransformed = UsersManager::getPasswordHash($password);

        $token_auth = $this->getTokenAuth($userLogin, $passwordTransformed);

        $db = Db::get();

        $db->insert(Common::prefixTable("user"), array(
                                                      'login'           => $userLogin,
                                                      'password'        => $passwordTransformed,
                                                      'alias'           => $alias,
                                                      'email'           => $email,
                                                      'token_auth'      => $token_auth,
                                                      'date_registered' => Date::now()->getDatetime()
                                                 )
        );

        // we reload the access list which doesn't yet take in consideration this new user
        Access::getInstance()->reloadAccess();
        Cache::deleteTrackerCache();

        /**
         * Triggered after a new user is created.
         * 
         * @param string $userLogin The new user's login handle.
         */
        Piwik::postEvent('UsersManager.addUser.end', array($userLogin));
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
        Piwik::checkUserIsSuperUserOrTheUser($userLogin);
        $this->checkUserIsNotAnonymous($userLogin);
        $this->checkUserIsNotSuperUser($userLogin);
        $userInfo = $this->getUser($userLogin);

        if (empty($password)) {
            $password = $userInfo['password'];
        } else {
            $password = Common::unsanitizeInputValue($password);
            if (!$_isPasswordHashed) {
                UsersManager::checkPassword($password);
                $password = UsersManager::getPasswordHash($password);
            }
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

        $alias = $this->getCleanAlias($alias, $userLogin);
        $token_auth = $this->getTokenAuth($userLogin, $password);

        $db = Db::get();

        $db->update(Common::prefixTable("user"),
            array(
                 'password'   => $password,
                 'alias'      => $alias,
                 'email'      => $email,
                 'token_auth' => $token_auth,
            ),
            "login = '$userLogin'"
        );
        Cache::deleteTrackerCache();

        /**
         * Triggered after an existing user has been updated.
         * 
         * @param string $userLogin The user's login handle.
         */
        Piwik::postEvent('UsersManager.updateUser.end', array($userLogin));
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
        Piwik::checkUserIsSuperUser();
        $this->checkUserIsNotAnonymous($userLogin);
        $this->checkUserIsNotSuperUser($userLogin);
        if (!$this->userExists($userLogin)) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionDeleteDoesNotExist", $userLogin));
        }

        $this->deleteUserOnly($userLogin);
        $this->deleteUserAccess($userLogin);
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
        if($userLogin == 'anonymous') {
            return true;
        }
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        $count = Db::fetchOne("SELECT count(*)
													FROM " . Common::prefixTable("user") . "
													WHERE login = ?", $userLogin);
        return $count != 0;
    }

    /**
     * Returns true if user with given email (userEmail) is known in the database, or the super user
     *
     * @param string $userEmail
     * @return bool true if the user is known
     */
    public function userEmailExists($userEmail)
    {
        Piwik::checkUserIsNotAnonymous();
        $count = Db::fetchOne("SELECT count(*)
								FROM " . Common::prefixTable("user") . "
								WHERE email = ?", $userEmail);
        return $count != 0
        || Config::getInstance()->superuser['email'] == $userEmail;
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
        $this->checkUserIsNotSuperUser($userLogin);

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

        $this->deleteUserAccess($userLogin, $idSites);

        // delete UserAccess
        $db = Db::get();

        // if the access is noaccess then we don't save it as this is the default value
        // when no access are specified
        if ($access != 'noaccess') {
            foreach ($idSites as $idsite) {
                $db->insert(Common::prefixTable("access"),
                    array("idsite" => $idsite,
                          "login"  => $userLogin,
                          "access" => $access)
                );
            }
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

    private function checkUserIsNotSuperUser($userLogin)
    {
        if ($userLogin == Piwik::getSuperUserLogin()) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionSuperUser"));
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

    /**
     * Delete a user given its login.
     * The user's access are not deleted.
     *
     * @param string $userLogin the user login.
     */
    private function deleteUserOnly($userLogin)
    {
        $db = Db::get();
        $db->query("DELETE FROM " . Common::prefixTable("user") . " WHERE login = ?", $userLogin);

        /**
         * Triggered after a user has been deleted.
         * 
         * This event should be used to clean up any data that is related to the now deleted user.
         * The **Dashboard** plugin, for example, uses this event to remove the user's dashboards.
         * 
         * @param string $userLogin The login handle of the deleted user.
         */
        Piwik::postEvent('UsersManager.deleteUser', array($userLogin));
    }

    /**
     * Delete the user access for the given websites.
     * The array of idsite must be either null OR the values must have been checked before for their validity!
     *
     * @param string $userLogin the user login
     * @param array $idSites array of idsites on which to delete the access. If null then delete all the access for this user.
     *
     * @return bool true on success
     */
    private function deleteUserAccess($userLogin, $idSites = null)
    {
        $db = Db::get();

        if (is_null($idSites)) {
            $db->query("DELETE FROM " . Common::prefixTable("access") .
                " WHERE login = ?",
                array($userLogin));
        } else {
            foreach ($idSites as $idsite) {
                $db->query("DELETE FROM " . Common::prefixTable("access") .
                    " WHERE idsite = ? AND login = ?",
                    array($idsite, $userLogin)
                );
            }
        }
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
