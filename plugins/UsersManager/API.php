<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager;

use DeviceDetector\DeviceDetector;
use Exception;
use Piwik\Access;
use Piwik\Access\CapabilitiesProvider;
use Piwik\Access\RolesProvider;
use Piwik\Auth\Password;
use Piwik\Common;
use Piwik\Concurrency\Lock;
use Piwik\Concurrency\LockBackend;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\NoAccessException;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreAdminHome\Emails\AnonymousAccessEnabledEmail;
use Piwik\Plugins\CoreAdminHome\Emails\UserDeletedEmail;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\UsersManager\Emails\UserInfoChangedEmail;
use Piwik\Plugins\UsersManager\Repository\UserRepository;
use Piwik\Plugins\UsersManager\Validators\AllowedEmailDomain;
use Piwik\Plugins\UsersManager\Validators\Email;
use Piwik\Request;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Tracker\Cache;
use Piwik\Url;
use Piwik\Validators\BaseValidator;

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
 * See also the documentation about <a href='http://matomo.org/docs/manage-users/' rel='noreferrer' target='_blank'>Managing Users</a> in Matomo.
 */
class API extends \Piwik\Plugin\API
{
    public const OPTION_NAME_PREFERENCE_SEPARATOR = '_';

    public static $UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION = true;
    public static $SET_SUPERUSER_ACCESS_REQUIRE_PASSWORD_CONFIRMATION = true;

    /**
     * @var Model
     */
    private $model;

    /**
     * @var Password
     */
    private $password;

    /**
     * @var UserAccessFilter
     */
    private $userFilter;

    /**
     * @var Access
     */
    private $access;

    /**
     * @var Access\RolesProvider
     */
    private $roleProvider;

    /**
     * @var Access\CapabilitiesProvider
     */
    private $capabilityProvider;

    /**
     * @var PasswordVerifier
     */
    private $passwordVerifier;

    /**
     * @var AllowedEmailDomain
     */
    private $allowedEmailDomain;

    private $userRepository;

    public const PREFERENCE_DEFAULT_REPORT = 'defaultReport';
    public const PREFERENCE_DEFAULT_REPORT_DATE = 'defaultReportDate';

    private static $instance = null;

    public function __construct(
        Model $model,
        UserAccessFilter $filter,
        Password $password,
        ?Access $access = null,
        ?Access\RolesProvider $roleProvider = null,
        ?Access\CapabilitiesProvider $capabilityProvider = null,
        ?PasswordVerifier $passwordVerifier = null
    ) {
        $this->model = $model;
        $this->userFilter = $filter;
        $this->password = $password;
        $this->allowedEmailDomain = StaticContainer::get(AllowedEmailDomain::class);
        $this->userRepository = new UserRepository($model, $filter, $password, $this->allowedEmailDomain);
        $this->access = $access ?: StaticContainer::get(Access::class);
        $this->roleProvider = $roleProvider ?: StaticContainer::get(RolesProvider::class);
        $this->capabilityProvider = $capabilityProvider ?: StaticContainer::get(CapabilitiesProvider::class);
        $this->passwordVerifier = $passwordVerifier ?: StaticContainer::get(PasswordVerifier::class);
    }

    /**
     * You can create your own Users Plugin to override this class.
     * Example of how you would overwrite the UsersManager_API with your own class:
     * Call the following in your plugin __construct() for example:
     *
     * StaticContainer::getContainer()->set('UsersManager_API', \Piwik\Plugins\MyCustomUsersManager\API::getInstance());
     *
     * @return \Piwik\Plugins\UsersManager\API
     * @throws Exception
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
     * Get the list of all available roles.
     * It does not return the super user role, and neither the "noaccess" role.
     * @return array[]  Returns an array containing information about each role
     */
    public function getAvailableRoles()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $response = [];

        foreach ($this->roleProvider->getAllRoles() as $role) {
            $response[] = [
              'id'          => $role->getId(),
              'name'        => $role->getName(),
              'description' => $role->getDescription(),
              'helpUrl'     => $role->getHelpUrl(),
            ];
        }

        return $response;
    }

    /**
     * Get the list of all available capabilities.
     * @return array[]  Returns an array containing information about each capability
     */
    public function getAvailableCapabilities()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $response = [];

        foreach ($this->capabilityProvider->getAllCapabilities() as $capability) {
            $response[] = [
              'id'              => $capability->getId(),
              'name'            => $capability->getName(),
              'description'     => $capability->getDescription(),
              'helpUrl'         => $capability->getHelpUrl(),
              'includedInRoles' => $capability->getIncludedInRoles(),
              'category'        => $capability->getCategory(),
            ];
        }

        return $response;
    }

    /**
     * Sets a user preference. Plugins can add custom preference names by declaring them in their plugin config/config.php
     * like this:
     *
     * ```php
     * return array('usersmanager.user_preference_names' => \Piwik\DI::add(array('preference_name_1', 'preference_name_2')));
     * ```
     *
     * @param string $userLogin
     * @param string $preferenceName
     * @param string $preferenceValue
     * @return void
     */
    public function setUserPreference($userLogin, $preferenceName, $preferenceValue)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);

        if (!$this->model->userExists($userLogin)) {
            throw new Exception('User does not exist: ' . $userLogin);
        }

        if ($userLogin === 'anonymous') {
            Piwik::checkUserHasSuperUserAccess();
        }

        $nameIfSupported = $this->getPreferenceId($userLogin, $preferenceName);
        Option::set($nameIfSupported, $preferenceValue);
    }

    /**
     * Gets a user preference
     * @param string $preferenceName
     * @param string|bool $userLogin Optional, defaults to current user log in when set to false.
     * @return bool|string
     */
    public function getUserPreference($preferenceName, $userLogin = false)
    {
        if ($userLogin === false) {
            // the default value for first parameter is there to have it an optional parameter in the HTTP API
            // in PHP it won't be optional. Could move parameter to the end of the method but did not want to break
            // BC
            $userLogin = Piwik::getCurrentUserLogin();
        }
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

        $userPreferences = [];
        foreach ($preferenceNames as $preferenceName) {
            $optionNameMatchAllUsers = $this->getPreferenceId('%', $preferenceName);
            $preferences = Option::getLike($optionNameMatchAllUsers);

            foreach ($preferences as $optionName => $optionValue) {
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
        if (false !== strpos($preference, self::OPTION_NAME_PREFERENCE_SEPARATOR)) {
            throw new Exception("Preference name cannot contain underscores.");
        }
        $names = [
          self::PREFERENCE_DEFAULT_REPORT,
          self::PREFERENCE_DEFAULT_REPORT_DATE,
          'isLDAPUser', // used in loginldap
          'hideSegmentDefinitionChangeMessage',// used in JS
        ];
        $customPreferences = StaticContainer::get('usersmanager.user_preference_names');

        if (
            !in_array($preference, $names, true)
            && !in_array($preference, $customPreferences, true)
        ) {
            throw new Exception('Not supported preference name: ' . $preference);
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
     * Returns all users with their role for $idSite.
     *
     * @param int $idSite
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $filter_search text to search for in the user's login and email (if any)
     * @param string|null $filter_access only select users with this access to $idSite. can be 'noaccess', 'some', 'view', 'admin', 'superuser'
     *                                   Filtering by 'superuser' is only allowed for other superusers.
     * @return array
     */
    public function getUsersPlusRole($idSite, $limit = null, $offset = 0, $filter_search = null, $filter_access = null, $filter_status = null)
    {
        if (Piwik::isUserIsAnonymous()) {
            // anonymous user should never see any results.
            Common::sendHeader('X-Matomo-Total-Results: 0');
            return [];
        } elseif (!$this->isUserHasAdminAccessTo($idSite)) {
            // if the user is not an admin to $idSite, they can only see their own user
            if ($offset > 1) {
                Common::sendHeader('X-Matomo-Total-Results: 1');
                return [];
            }

            $users = [];
            $user = $this->model->getUser($this->access->getLogin());
            if ($user) {
                $user['role'] = $this->access->getRoleForSite($idSite);
                $user['capabilities'] = $this->access->getCapabilitiesForSite($idSite);
                $users = [$user];
            }
            $totalResults = count($users);
        } else {
            // if the current user is not the superuser, only select users that have access to a site this user
            // has admin access to
            $loginsToLimit = null;
            if (!Piwik::hasUserSuperUserAccess()) {
                $adminIdSites = Access::getInstance()->getSitesIdWithAdminAccess();
                if (empty($adminIdSites)) { // sanity check
                    throw new \Exception("The current admin user does not have access to any sites.");
                }

                $loginsToLimit = $this->model->getUsersWithAccessToSites($adminIdSites);
            }

            if ($loginsToLimit !== null && empty($loginsToLimit)) {
                // if the current user is not the superuser, and getUsersWithAccessToSites() returned an empty result,
                // access is managed by another plugin, and the current user cannot manage any user with UsersManager
                Common::sendHeader('X-Matomo-Total-Results: 0');
                return [];
            } else {
                [$users, $totalResults] = $this->model->getUsersWithRole(
                    $idSite,
                    $limit,
                    $offset,
                    $filter_search,
                    $filter_access,
                    $filter_status,
                    $loginsToLimit
                );

                foreach ($users as &$user) {
                    $user['superuser_access'] = $user['superuser_access'] == 1;
                    if ($user['superuser_access']) {
                        $user['role'] = 'superuser';
                        $user['capabilities'] = [];
                    } else {
                        [
                          $user['role'],
                          $user['capabilities']
                        ] = $this->getRoleAndCapabilitiesFromAccess($user['access']);
                        $user['role'] = empty($user['role']) ? 'noaccess' : reset($user['role']);
                    }

                    unset($user['access']);
                }
            }
        }

        $users = $this->userRepository->enrichUsers($users);
        $users = $this->userRepository->enrichUsersWithLastSeen($users);

        foreach ($users as &$user) {
            unset($user['password']);
        }

        Common::sendHeader('X-Matomo-Total-Results: ' . $totalResults);
        return $users;
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

        if (!is_string($userLogins)) {
            throw new \Exception('Parameter userLogins needs to be a string containing a comma separated list of users');
        }

        $logins = [];

        if (!empty($userLogins)) {
            $logins = explode(',', $userLogins);
        }

        $users = $this->model->getUsers($logins);
        $users = $this->userFilter->filterUsers($users);
        return $this->userRepository->enrichUsers($users);
    }

    /**
     * Returns the list of all the users login
     *
     * @return array the list of all the users login
     */
    public function getUsersLogin()
    {
        Piwik::checkUserHasSomeAdminAccess();

        $logins = $this->model->getUsersLogin();
        $logins = $this->userFilter->filterLogins($logins);

        return $logins;
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

        $userSites = $this->model->getUsersSitesFromAccess($access);
        $userSites = $this->userFilter->filterLoginIndexedArray($userSites);

        return $userSites;
    }

    /**
     * Throws an exception if one of the given access types does not exists.
     *
     * @param string|array $access
     * @throws Exception
     */
    private function checkAccessType($access)
    {
        $access = (array)$access;

        foreach ($access as $entry) {
            if (!$this->isValidAccessType($entry)) {
                throw new Exception(Piwik::translate(
                    "UsersManager_ExceptionAccessValues",
                    [implode(", ", $this->getAllRolesAndCapabilities()), $entry]
                ));
            }
        }
    }

    /**
     * returns if the given access type exists
     *
     * @param string $access
     * @return bool
     */
    private function isValidAccessType($access)
    {
        return in_array($access, $this->getAllRolesAndCapabilities(), true);
    }

    private function getAllRolesAndCapabilities()
    {
        $roles = $this->roleProvider->getAllRoleIds();
        $capabilities = $this->capabilityProvider->getAllCapabilityIds();
        return array_merge($roles, $capabilities);
    }

    /**
     * For each user, returns their access level for the given $idSite.
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

        $usersAccess = $this->model->getUsersAccessFromSite($idSite);
        $usersAccess = $this->userFilter->filterLoginIndexedArray($usersAccess);

        return $usersAccess;
    }

    public function getUsersWithSiteAccess($idSite, $access)
    {
        Piwik::checkUserHasAdminAccess($idSite);
        $this->checkAccessType($access);

        $logins = $this->model->getUsersLoginWithSiteAccess($idSite, $access);

        if (empty($logins)) {
            return [];
        }

        $logins = $this->userFilter->filterLogins($logins);
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
            $return = [];
            $siteManagerModel = new \Piwik\Plugins\SitesManager\Model();
            $sites = $siteManagerModel->getAllSites();
            foreach ($sites as $site) {
                $return[] = [
                  'site'   => $site['idsite'],
                  'access' => 'admin'
                ];
            }
            return $return;
        }
        return $this->model->getSitesAccessFromUser($userLogin);
    }

    /**
     * For each website ID, returns the access level of the given $userLogin (if the user is not a superuser).
     * If the user doesn't have any access to a website ('noaccess'),
     * this website will not be in the returned array.
     * If the user doesn't have any access, the returned array will be an empty array.
     *
     * @param string $userLogin User that has to be valid
     *
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $filter_search text to search for in site name, URLs, or group.
     * @param string|null $filter_access access level to select for, can be 'some', 'view' or 'admin' (by default 'some')
     * @return array    The returned array has the format
     *                    array(
     *                        ['idsite' => 1, 'site_name' => 'the site', 'access' => 'admin'],
     *                        ['idsite' => 2, 'site_name' => 'the other site', 'access' => 'view'],
     *                        ...
     *                    )
     * @throws Exception
     */
    public function getSitesAccessForUser(
        $userLogin,
        $limit = null,
        $offset = 0,
        $filter_search = null,
        $filter_access = null
    ) {
        Piwik::checkUserHasSomeAdminAccess();
        $this->checkUserExists($userLogin);

        if (Piwik::hasTheUserSuperUserAccess($userLogin)) {
            throw new \Exception("This method should not be used with superusers.");
        }

        $idSites = null;
        if (!Piwik::hasUserSuperUserAccess()) {
            $idSites = $this->access->getSitesIdWithAdminAccess();
            if (empty($idSites)) { // sanity check
                throw new \Exception("The current admin user does not have access to any sites.");
            }
        }

        [$sites, $totalResults] = $this->model->getSitesAccessFromUserWithFilters(
            $userLogin,
            $limit,
            $offset,
            $filter_search,
            $filter_access,
            $idSites
        );
        foreach ($sites as &$siteAccess) {
            [
              $siteAccess['role'],
              $siteAccess['capabilities']
            ] = $this->getRoleAndCapabilitiesFromAccess($siteAccess['access']);
            $siteAccess['role'] = empty($siteAccess['role']) ? 'noaccess' : reset($siteAccess['role']);
            unset($siteAccess['access']);
        }

        $hasAccessToAny = $this->model->getSiteAccessCount($userLogin) > 0;

        Common::sendHeader('X-Matomo-Total-Results: ' . $totalResults);
        if ($hasAccessToAny) {
            Common::sendHeader('X-Matomo-Has-Some: 1');
        }
        return $sites;
    }

    /**
     * Returns the user information (login, password hash, email, date_registered, etc.)
     *
     * @param string $userLogin the user login
     *
     * @return array the user information
     */
    public function getUser($userLogin)
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);
        $this->checkUserExists($userLogin);

        $user = $this->model->getUser($userLogin);

        if (empty($user) || !is_array($user)) {
            return [];
        }

        $user = $this->userFilter->filterUser($user);
        return $this->userRepository->enrichUser($user);
    }

    /**
     * Returns the user information (login, password hash, email, date_registered, etc.)
     *
     * @param string $userEmail the user email
     *
     * @return array the user information
     */
    public function getUserByEmail($userEmail)
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkUserEmailExists($userEmail);

        $user = $this->model->getUserByEmail($userEmail);

        if (empty($user) || !is_array($user)) {
            return [];
        }

        $user = $this->userFilter->filterUser($user);
        return $this->userRepository->enrichUser($user);
    }

    /**
     * Add a user in the database.
     * A user is defined by
     * - a login that has to be unique and valid
     * - a password that has to be valid
     * - an email that has to be in a correct format
     *
     * @throws Exception in case of an invalid parameter
     * @see isValidLoginString()
     * @see isValidPasswordString()
     * @see isValidEmailString()
     *
     * @see userExists()
     */
    public function addUser($userLogin, $password, $email, $_isPasswordHashed = false, $initialIdSite = null, $passwordConfirmation = null)
    {
        Piwik::checkUserHasSomeAdminAccess();
        UsersManager::dieIfUsersAdminIsDisabled();

        // check password confirmation only when using session auth
        if (Common::getRequestVar('force_api_session', 0)) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        $password = Common::unsanitizeInputValue($password);
        UsersManager::checkPassword($password);


        $initialIdSite = $initialIdSite === null ? null : intval($initialIdSite);

        if (!Piwik::hasUserSuperUserAccess()) {
            if (empty($initialIdSite)) {
                throw new \Exception(Piwik::translate("UsersManager_AddUserNoInitialAccessError"));
            }
        }

        $this->userRepository->create(
            (string) $userLogin,
            (string) $email,
            $initialIdSite,
            (string) $password,
            (bool) $_isPasswordHashed
        );

        /**
         * Triggered after a new user is created.
         *
         * @param string $userLogin The new user's login.
         * @param string $email The new user's e-mail.
         * @param string $inviterLogin The login of the user who created the new user
         */
        Piwik::postEvent('UsersManager.addUser.end', [$userLogin, $email, Piwik::getCurrentUserLogin()]);
    }

    /**
     * @throws Exception
     */
    public function inviteUser($userLogin, $email, $initialIdSite = null, $expiryInDays = null, $passwordConfirmation = null)
    {
        Piwik::checkUserHasSomeAdminAccess();
        UsersManager::dieIfUsersAdminIsDisabled();

        // check password confirmation only when using session auth
        if (Common::getRequestVar('force_api_session', 0)) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        if (empty($expiryInDays)) {
            $expiryInDays = Config\GeneralConfig::getConfigValue('default_invite_user_token_expiry_days');
        }

        if (empty($initialIdSite)) {
            throw new \Exception(Piwik::translate("UsersManager_AddUserNoInitialAccessError"));
        } else {
            // check if the site exists
            new Site($initialIdSite);
        }

        $this->userRepository->inviteUser((string) $userLogin, (string) $email, intval($initialIdSite), (int) $expiryInDays);

        /**
         * Triggered after a new user was invited.
         *
         * @param string $userLogin The new user's login.
         * @param string $email The new user's e-mail.
         */
        Piwik::postEvent('UsersManager.inviteUser.end', [$userLogin, $email]);
    }

    /**
     * Enable or disable Super user access to the given user login. Note: When granting Super User access all previous
     * permissions of the user will be removed as the user gains access to everything.
     *
     * @param string $userLogin the user login.
     * @param bool|int $hasSuperUserAccess true or '1' to grant Super User access, false or '0' to remove Super User
     *                                     access.
     * @param string $passwordConfirmation the current user's password. For security purposes, this value should be
     *                                     sent as a POST parameter.
     * @throws \Exception
     */
    public function setSuperUserAccess($userLogin, $hasSuperUserAccess, $passwordConfirmation = null)
    {
        $this->executeConcurrencySafe($userLogin, function () use ($userLogin, $hasSuperUserAccess, $passwordConfirmation) {
            Piwik::checkUserHasSuperUserAccess();
            $this->checkUserIsNotAnonymous($userLogin);
            UsersManager::dieIfUsersAdminIsDisabled();

            $requirePasswordConfirmation = self::$SET_SUPERUSER_ACCESS_REQUIRE_PASSWORD_CONFIRMATION;
            self::$SET_SUPERUSER_ACCESS_REQUIRE_PASSWORD_CONFIRMATION = true;

            $isCliMode = Common::isPhpCliMode() && !(defined('PIWIK_TEST_MODE') && PIWIK_TEST_MODE);
            if (
                !$isCliMode
                && $requirePasswordConfirmation
            ) {
                $this->confirmCurrentUserPassword($passwordConfirmation);
            }
            $this->checkUserExists($userLogin);

            if (!$hasSuperUserAccess && $this->isUserTheOnlyUserHavingSuperUserAccess($userLogin)) {
                $message = Piwik::translate("UsersManager_ExceptionRemoveSuperUserAccessOnlySuperUser", $userLogin)
                    . " "
                    . Piwik::translate("UsersManager_ExceptionYouMustGrantSuperUserAccessFirst");
                throw new Exception($message);
            }

            $this->model->deleteUserAccess($userLogin);
            $this->model->setSuperUserAccess($userLogin, $hasSuperUserAccess);

            Cache::deleteTrackerCache();
        });
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

        // we do not filter these users by access and return them all since we need to print this information in the
        // UI and they are allowed to see this.
        return $this->userRepository->enrichUsers($users);
    }


    /**
     * Updates a user in the database.
     * Only login and password are required (case when we update the password).
     *
     * If password or email changes, it is required to also specify the password of the current user needs to be specified
     * to confirm this change.
     *
     * @see addUser() for all the parameters
     */
    public function updateUser(
        $userLogin,
        $password = false,
        $email = false,
        $_isPasswordHashed = false,
        $passwordConfirmation = false
    ) {
        $email = Common::unsanitizeInputValue($email);
        $requirePasswordConfirmation = self::$UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION;
        self::$UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION = true;

        $isEmailNotificationOnInConfig = Config::getInstance()->General['enable_update_users_email'];

        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);
        UsersManager::dieIfUsersAdminIsDisabled();
        $this->checkUserIsNotAnonymous($userLogin);
        $this->checkUserExists($userLogin);

        $userInfo = $this->model->getUser($userLogin);
        $changeShouldRequirePasswordConfirmation = false;

        $passwordHasBeenUpdated = false;

        if (empty($password)) {
            $password = false;
        } else {
            $changeShouldRequirePasswordConfirmation = true;
            $password = Common::unsanitizeInputValue($password);

            if (!$_isPasswordHashed) {
                UsersManager::checkPassword($password);
                $password = UsersManager::getPasswordHash($password);
            }

            $passwordInfo = $this->password->info($password);

            if (!isset($passwordInfo['algo']) || 0 >= $passwordInfo['algo']) {
                // password may have already been fully hashed
                $password = $this->password->hash($password);
            }

            $passwordHasBeenUpdated = true;
        }

        if (empty($email)) {
            $email = $userInfo['email'];
        }

        $hasEmailChanged = mb_strtolower($email) !== mb_strtolower($userInfo['email']);

        if ($hasEmailChanged) {
            BaseValidator::check('email', $email, [new Email(true, $userLogin), $this->allowedEmailDomain]);
            $changeShouldRequirePasswordConfirmation = true;
        }

        if ($changeShouldRequirePasswordConfirmation && $requirePasswordConfirmation) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        $this->model->updateUser($userLogin, $password, $email);

        Cache::deleteTrackerCache();

        if ($hasEmailChanged && $this->model->isPendingUser($userLogin)) {
            // If the email of a user is changed, who was invited and did not yet accept the invitation
            // we send a new invite to the new address.
            // this will indirectly invalidate the invitation sent to the previous address
            $this->userRepository->reInviteUser(
                $userLogin,
                (int) Config\GeneralConfig::getConfigValue('default_invite_user_token_expiry_days')
            );
        } elseif ($hasEmailChanged && $isEmailNotificationOnInConfig) {
            $this->sendEmailChangedEmail($userInfo, $email);
        }

        if ($passwordHasBeenUpdated && $requirePasswordConfirmation && $isEmailNotificationOnInConfig) {
            $this->sendPasswordChangedEmail($userInfo);
        }

        /**
         * Triggered after an existing user has been updated.
         * Event notify about password change.
         *
         * @param string $userLogin The user's login handle.
         * @param boolean $passwordHasBeenUpdated Flag containing information about password change.
         */
        Piwik::postEvent('UsersManager.updateUser.end', [$userLogin, $passwordHasBeenUpdated, $email, $password]);
    }

    /**
     * Delete one or more users and all its access, given its login.
     *
     * @param string $userLogin the user login(s).
     * @param string $passwordConfirmation the currents users password, only required when request is authenticated with session token auth
     *
     * @throws Exception if the user doesn't exist or if deleting the users would leave no superusers.
     *
     */
    public function deleteUser($userLogin, $passwordConfirmation = null)
    {
        Piwik::checkUserHasSomeAdminAccess();
        UsersManager::dieIfUsersAdminIsDisabled();
        $this->checkUserIsNotAnonymous($userLogin);

        if (Common::getRequestVar('force_api_session', 0)) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        $user = $this->model->getUser($userLogin);

        // If user is not a super user check if the user was invited by the current user
        if (!Piwik::hasUserSuperUserAccess()) {
            if ($user['invited_by'] !== Piwik::getCurrentUserLogin() || !$this->model->isPendingUser($userLogin)) {
                throw new NoAccessException(Piwik::translate('UsersManager_ExceptionUserDoesNotExist', $userLogin));
            }
        }

        $this->checkUserExist($userLogin);

        if ($this->isUserTheOnlyUserHavingSuperUserAccess($userLogin)) {
            $message = Piwik::translate("UsersManager_ExceptionDeleteOnlyUserWithSuperUserAccess", $userLogin)
              . " "
              . Piwik::translate("UsersManager_ExceptionYouMustGrantSuperUserAccessFirst");
            throw new Exception($message);
        }

        $this->model->deleteUser($userLogin);

        $container = StaticContainer::getContainer();
        $email = $container->make(UserDeletedEmail::class, [
          'login'        => Piwik::getCurrentUserLogin(),
          'emailAddress' => Piwik::getCurrentUserEmail(),
          'userLogin'    => $userLogin
        ]);
        $email->safeSend();

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
        Piwik::checkUserHasSomeViewAccess();

        return $this->model->userEmailExists($userEmail);
    }

    /**
     * Returns the first login name of an existing user that has the given email address. If no user can be found for
     * this user an error will be returned.
     *
     * @param string $userEmail
     * @return bool true if the user is known
     */
    public function getUserLoginFromUserEmail($userEmail)
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkUserEmailExists($userEmail);

        $user = $this->model->getUserByEmail($userEmail);

        // any user with some admin access is allowed to find any user by email, no need to filter by access here

        return $user['login'];
    }

    /**
     * Set an access level to a given user for a list of websites ID.
     *
     * If access = 'noaccess' the current access (if any) will be deleted.
     * If access = 'view' or 'admin' the current access level is deleted and updated with the new value.
     *
     * @param string $userLogin The user login
     * @param string|array $access Access to grant. Must have one of the following value : noaccess, view, write, admin.
     *                              May also be an array to sent additional capabilities
     * @param int|array $idSites The array of idSites on which to apply the access level for the user.
     *       If the value is "all" then we apply the access level to all the websites ID for which the current authentificated user has an 'admin' access.
     * @param string $passwordConfirmation password confirmation. only required when setting view access for anonymous user through session auth
     * @throws Exception if the user doesn't exist
     * @throws Exception if the access parameter doesn't have a correct value
     * @throws Exception if any of the given website ID doesn't exist
     */
    public function setUserAccess($userLogin, $access, $idSites, $passwordConfirmation = null)
    {
        UsersManager::dieIfUsersAdminIsDisabled();

        if ($access != 'noaccess') {
            $this->checkAccessType($access);
        }

        $idSites = $this->getIdSitesCheckAdminAccess($idSites);

        // check password confirmation only when using session auth and setting view access for anonymous user
        if ($userLogin === 'anonymous' && Request::fromRequest()->getBoolParameter('force_api_session', false) && $access === 'view') {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        if (
            $userLogin === 'anonymous' &&
            (is_array($access) || !in_array($access, ['view', 'noaccess'], true))
        ) {
            throw new Exception(Piwik::translate(
                "UsersManager_ExceptionAnonymousAccessNotPossible",
                ['noaccess', 'view']
            ));
        }

        $roles = [];
        $capabilities = [];

        if (is_array($access)) {
            // we require one role, and optionally multiple capabilities
            [$roles, $capabilities] = $this->getRoleAndCapabilitiesFromAccess($access);

            if (count($roles) < 1) {
                $ids = implode(', ', $this->roleProvider->getAllRoleIds());
                throw new Exception(Piwik::translate('UsersManager_ExceptionNoRoleSet', $ids));
            }

            if (count($roles) > 1) {
                $ids = implode(', ', $this->roleProvider->getAllRoleIds());
                throw new Exception(Piwik::translate('UsersManager_ExceptionMultipleRoleSet', $ids));
            }
        } else {
            // as only one access is set, we require it to be a role or "noaccess"...
            if ($access !== 'noaccess') {
                $this->roleProvider->checkValidRole($access);
                $roles[] = $access;
            }
        }

        $this->checkUserExist($userLogin);

        $this->executeConcurrencySafe($userLogin, function () use ($userLogin, $access, $idSites, $roles, $capabilities) {
            $idSites = $this->getIdSitesCheckAdminAccess($idSites);
            $this->checkUsersHasNotSuperUserAccess($userLogin);

            $this->model->deleteUserAccess($userLogin, $idSites);

            if ($access === 'noaccess') {
                // if the access is noaccess then we don't save it as this is the default value
                // when no access are specified
                Piwik::postEvent('UsersManager.removeSiteAccess', [$userLogin, $idSites]);
            } else {
                $role = array_shift($roles);
                $this->model->addUserAccess($userLogin, $role, $idSites);
            }

            if (!empty($capabilities)) {
                $this->addCapabilitesToUser($userLogin, $capabilities, $idSites);
            }

            // Send notification to all super users if anonymous access is set for a site
            if ($userLogin === 'anonymous' && $access === 'view') {
                $container = StaticContainer::getContainer();

                $siteNames = [];

                foreach ($idSites as $idSite) {
                    $siteNames[] = Site::getNameFor($idSite);
                }

                $superUsers = Piwik::getAllSuperUserAccessEmailAddresses();
                foreach ($superUsers as $login => $email) {
                    $email = $container->make(AnonymousAccessEnabledEmail::class, array(
                        'login' => $login,
                        'emailAddress' => $email,
                        'siteName' => implode(', ', $siteNames)
                    ));
                    $email->safeSend();
                }
            }

            // we reload the access list which doesn't yet take in consideration this new user access
            $this->reloadPermissions();
        });
    }

    /**
     * Adds the given capabilities to the given user for the given sites.
     * The capability will be added only when the user also has access to a site, for example View, Write, or Admin.
     * Note: You can neither add any capability to a super user, nor to the anonymous user.
     * Note: If the user has assigned a role which already grants the given capability, the capability will not be added in
     * the backend.
     *
     * @param string $userLogin The user login
     * @param string|string[] $capabilities To fetch a list of available capabilities call "UsersManager.getAvailableCapabilities".
     * @param int|int[] $idSites
     * @throws Exception
     */
    public function addCapabilities($userLogin, $capabilities, $idSites)
    {
        $this->executeConcurrencySafe($userLogin, function () use ($userLogin, $capabilities, $idSites) {
            $idSites = $this->getIdSitesCheckAdminAccess($idSites);

            if ($userLogin == 'anonymous') {
                throw new Exception(Piwik::translate("UsersManager_ExceptionAnonymousNoCapabilities"));
            }

            $this->checkUserExists($userLogin);
            $this->checkUsersHasNotSuperUserAccess([$userLogin]);

            if (!is_array($capabilities)) {
                $capabilities = [$capabilities];
            }

            foreach ($capabilities as $entry) {
                $this->capabilityProvider->checkValidCapability($entry);
            }

            $this->addCapabilitesToUser($userLogin, $capabilities, $idSites);

            // we reload the access list which doesn't yet take in consideration this new user access
            $this->reloadPermissions();
        });
    }

    private function addCapabilitesToUser(string $userLogin, array $capabilities, $idSites)
    {
        [$sitesIdWithRole, $sitesIdWithCapability] = $this->getRolesAndCapabilitiesForLogin($userLogin);

        foreach ($idSites as $idSite) {
            if (!array_key_exists($idSite, $sitesIdWithRole)) {
                throw new Exception(
                    Piwik::translate('UsersManager_ExceptionNoCapabilitiesWithoutRole', [$userLogin, $idSite])
                );
            }
        }

        foreach ($capabilities as $entry) {
            $cap = $this->capabilityProvider->getCapability($entry);

            foreach ($idSites as $idSite) {
                $hasCapabilityAlready = array_key_exists($idSite, $sitesIdWithCapability)
                    && in_array($entry, $sitesIdWithCapability[$idSite], true);

                if (!$hasCapabilityAlready) {
                    $theRole = $sitesIdWithRole[$idSite];
                    if ($cap->hasRoleCapability($theRole)) {
                        // todo this behaviour needs to be defined...
                        // when the role already supports this capability we do not add it again
                        continue;
                    }

                    $this->model->addUserAccess($userLogin, $entry, [$idSite]);
                }
            }
        }
    }

    private function getRolesAndCapabilitiesForLogin($userLogin)
    {
        $sites = $this->model->getSitesAccessFromUser($userLogin);
        $roleIds = $this->roleProvider->getAllRoleIds();

        $sitesIdWithRole = [];
        $sitesIdWithCapability = [];
        foreach ($sites as $site) {
            if (in_array($site['access'], $roleIds, true)) {
                $sitesIdWithRole[(int)$site['site']] = $site['access'];
            } else {
                if (!isset($sitesIdWithCapability[(int)$site['site']])) {
                    $sitesIdWithCapability[(int)$site['site']] = [];
                }
                $sitesIdWithCapability[(int)$site['site']][] = $site['access'];
            }
        }
        return [$sitesIdWithRole, $sitesIdWithCapability];
    }

    /**
     * Removes the given capabilities from the given user for the given sites.
     * The capability will be only removed if it is actually granted as a separate capability. If the user has a role
     * that includes a specific capability, for example "Admin", then the capability will not be removed because the
     * assigned role will always include this capability.
     *
     * @param string $userLogin The user login
     * @param string|string[] $capabilities To fetch a list of available capabilities call "UsersManager.getAvailableCapabilities".
     * @param int|int[] $idSites
     * @throws Exception
     */
    public function removeCapabilities($userLogin, $capabilities, $idSites)
    {
        $this->executeConcurrencySafe($userLogin, function () use ($userLogin, $capabilities, $idSites) {
            $idSites = $this->getIdSitesCheckAdminAccess($idSites);

            $this->checkUserExists($userLogin);

            if (!is_array($capabilities)) {
                $capabilities = [$capabilities];
            }

            foreach ($capabilities as $capability) {
                $this->capabilityProvider->checkValidCapability($capability);
            }

            foreach ($capabilities as $capability) {
                $this->model->removeUserAccess($userLogin, $capability, $idSites);
            }

            // we reload the access list which doesn't yet take in consideration this removed capability
            $this->reloadPermissions();
        });
    }

    private function reloadPermissions()
    {
        Access::getInstance()->reloadAccess();
        Cache::deleteTrackerCache();
    }

    private function getIdSitesCheckAdminAccess($idSites)
    {
        // reload access to ensure we're not working with cached entries that might have been changed in between
        Access::getInstance()->reloadAccess();

        if ($idSites === 'all') {
            // in case idSites is all we grant access to all the websites on which the current connected user has an 'admin' access
            $idSites = \Piwik\Plugins\SitesManager\API::getInstance()->getSitesIdWithAdminAccess();
        } else {
            // in case the idSites is an integer we build an array
            $idSites = Site::getIdSitesFromIdSitesString($idSites);
        }

        if (empty($idSites)) {
            throw new Exception('Specify at least one website ID in &idSites=');
        }

        // it is possible to set user access on websites only for the websites admin
        // basically an admin can give the view or the admin access to any user for the websites they manage
        Piwik::checkUserHasAdminAccess($idSites);

        if (!is_array($idSites)) {
            $idSites = [$idSites];
        }

        return $idSites;
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

    private function checkUsersHasNotSuperUserAccess($userLogins)
    {
        $userLogins = (array)$userLogins;
        $superusers = $this->getUsersHavingSuperUserAccess();
        $superusers = array_column($superusers, null, 'login');

        foreach ($userLogins as $userLogin) {
            if (isset($superusers[$userLogin])) {
                throw new Exception(Piwik::translate("UsersManager_ExceptionUserHasSuperUserAccess", $userLogin));
            }
        }
    }

    /**
     * @param string|string[] $userLogin
     * @return bool
     */
    private function isUserTheOnlyUserHavingSuperUserAccess($userLogin)
    {
        if (!is_array($userLogin)) {
            $userLogin = [$userLogin];
        }

        $superusers = $this->getUsersHavingSuperUserAccess();
        $superusersByLogin = array_column($superusers, null, 'login');

        foreach ($userLogin as $login) {
            unset($superusersByLogin[$login]);
        }

        return empty($superusersByLogin);
    }

    /**
     * Generates an app specific API token every time you call this method. You should ideally store this token securely
     * in your app and not generate a new token every time.
     *
     * If the username/password combination is incorrect an invalid token will be returned.
     *
     * @param string $userLogin Login or Email address
     * @param string $passwordConfirmation the current user's password. For security purposes, this value should be
     *                                     sent as a POST parameter.
     * @param string $description The description for this app specific password, for example your app name. Max 100 characters are allowed
     * @param string $expireDate Optionally a date when the token should expire
     * @param string $expireHours Optionally number of hours for how long the token should be valid before it expires.
     *                            If expireDate is set and expireHours, then expireDate will be used.
     *                            If expireDate is set and expireHours, then expireDate will be used.
     * @return string
     */
    public function createAppSpecificTokenAuth(
        $userLogin,
        $passwordConfirmation,
        $description,
        $expireDate = null,
        $expireHours = 0
    ) {
        $user = $this->model->getUser($userLogin);
        if (empty($user) && Piwik::isValidEmailString($userLogin)) {
            $user = $this->model->getUserByEmail($userLogin);
            if (!empty($user['login'])) {
                $userLogin = $user['login'];
            }
        }

        if (empty($user) || !$this->passwordVerifier->isPasswordCorrect($userLogin, $passwordConfirmation)) {
            if (empty($user)) {
                /**
                 * @ignore
                 * @internal
                 */
                Piwik::postEvent('Login.authenticate.failed', [$userLogin]);
            }

            throw new \Exception(Piwik::translate('UsersManager_CurrentPasswordNotCorrect'));
        }

        if (empty($expireDate) && !empty($expireHours) && is_numeric($expireHours)) {
            $expireDate = Date::now()->addHour($expireHours)->getDatetime();
        } elseif (!empty($expireDate)) {
            $expireDate = Date::factory($expireDate)->getDatetime();
        }

        $generatedToken = $this->model->generateRandomTokenAuth();
        $this->model->addTokenAuth($userLogin, $generatedToken, $description, Date::now()->getDatetime(), $expireDate);

        return $generatedToken;
    }

    public function newsletterSignup()
    {
        Piwik::checkUserIsNotAnonymous();

        $userLogin = Piwik::getCurrentUserLogin();
        $email = Piwik::getCurrentUserEmail();

        $success = NewsletterSignup::signupForNewsletter($userLogin, $email, true);
        $result = $success ? ['success' => true] : ['error' => true];
        return $result;
    }

    private function isUserHasAdminAccessTo($idSite)
    {
        try {
            Piwik::checkUserHasAdminAccess([$idSite]);
            return true;
        } catch (NoAccessException $ex) {
            return false;
        }
    }

    private function checkUserExist($userLogin)
    {
        $userExists = $this->model->userExists($userLogin);
        if (!$userExists) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionUserDoesNotExist", $userLogin));
        }
    }

    private function getRoleAndCapabilitiesFromAccess($access)
    {
        $roles = [];
        $capabilities = [];

        foreach ($access as $entry) {
            if (empty($entry)) {
                continue;
            }

            if ($this->roleProvider->isValidRole($entry)) {
                $roles[] = $entry;
            } elseif ($this->isValidAccessType($entry)) {
                $capabilities[] = $entry;
            }
        }
        return [$roles, $capabilities];
    }

    private function sendEmailChangedEmail($user, $newEmail)
    {
        // send the mail to both the old email and the new email
        foreach ([$newEmail, $user['email']] as $emailTo) {
            $this->sendUserInfoChangedEmail(
                'email',
                $user,
                $newEmail,
                $emailTo,
                'UsersManager_EmailChangeNotificationSubject'
            );
        }
    }

    private function sendUserInfoChangedEmail($type, $user, $newValue, $emailTo, $subject)
    {
        $deviceDescription = $this->getDeviceDescription();

        $mail = new UserInfoChangedEmail($type, $newValue, $deviceDescription, $user['login']);

        $mail->addTo($emailTo, $user['login']);
        $mail->setSubject(Piwik::translate($subject));

        $mail->send();
    }

    private function sendPasswordChangedEmail($user)
    {
        $this->sendUserInfoChangedEmail(
            'password',
            $user,
            null,
            $user['email'],
            'UsersManager_PasswordChangeNotificationSubject'
        );
    }

    private function getDeviceDescription()
    {
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

        $uaParser = new DeviceDetector($userAgent);
        $uaParser->parse();

        $deviceName = ucfirst($uaParser->getDeviceName());
        if (!empty($deviceName)) {
            $description = $deviceName;
        } else {
            $description = Piwik::translate('General_Unknown');
        }

        $deviceBrand = $uaParser->getBrandName();
        $deviceModel = $uaParser->getModel();
        if (!empty($deviceBrand) || !empty($deviceModel)) {
            $parts = array_filter([$deviceBrand, $deviceModel]);
            $description .= ' (' . implode(' ', $parts) . ')';
        }

        return $description;
    }

    /**
     * resend the invite email to user
     *
     * @param string $userLogin
     * @param int $expiryInDays
     * @param string | null $passwordConfirmation
     * @throws NoAccessException
     */
    public function resendInvite($userLogin, $expiryInDays = 7, $passwordConfirmation = null)
    {
        Piwik::checkUserHasSomeAdminAccess();

        // check password confirmation only when using session auth
        if (Common::getRequestVar('force_api_session', 0)) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        if (!$this->model->isPendingUser($userLogin)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionUserDoesNotExist', $userLogin));
        }

        $user = $this->model->getUser($userLogin);

        // If user is not a super user check if the user was invited by the current user
        if (!Piwik::hasUserSuperUserAccess()) {
            if ($user['invited_by'] !== Piwik::getCurrentUserLogin()) {
                throw new NoAccessException(Piwik::translate('UsersManager_ExceptionResendInviteDenied', $userLogin));
            }
        }

        $token = $this->userRepository->reInviteUser($userLogin, (int)$expiryInDays);

        /**
         * Triggered after a new user was invited.
         *
         * @param string $userLogin The new user's login.
         */
        Piwik::postEvent('UsersManager.inviteUser.resendInvite', [$userLogin, $user['email']]);

        return $token;
    }

    /**
     * @param $userLogin
     * @param int $expiryInDays
     * @param string | null $passwordConfirmation
     * @return string
     * @throws NoAccessException
     */
    public function generateInviteLink($userLogin, $expiryInDays = 7, $passwordConfirmation = null)
    {
        Piwik::checkUserHasSomeAdminAccess();

        // check password confirmation only when using session auth
        if (Common::getRequestVar('force_api_session', 0)) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        if (!$this->model->isPendingUser($userLogin)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionUserDoesNotExist', $userLogin));
        }

        $user = $this->model->getUser($userLogin);

        // If user is not a super user check if the user was invited by the current user
        if (!Piwik::hasUserSuperUserAccess()) {
            if ($user['invited_by'] !== Piwik::getCurrentUserLogin()) {
                throw new NoAccessException(Piwik::translate('UsersManager_ExceptionResendInviteDenied', $userLogin));
            }
        }

        $token = $this->userRepository->generateInviteToken($userLogin, (int)$expiryInDays);

        /**
         * Triggered after a new user invite token was generate.
         *
         * @param string $userLogin The new user's login.
         */
        Piwik::postEvent('UsersManager.inviteUser.generateInviteLinkToken', [$userLogin, $user['email']]);

        return SettingsPiwik::getPiwikUrl() . 'index.php?' . Url::getQueryStringFromParameters([
                'module' => Piwik::getLoginPluginName(),
                'action' => 'acceptInvitation',
                'token'  => $token,
            ]);
    }

    private function executeConcurrencySafe(string $userLogin, callable $callback = null)
    {
        $lock = new Lock(StaticContainer::get(LockBackend::class), 'UsersManager.changePermissions');
        $lock->execute($userLogin, $callback);
    }
}
