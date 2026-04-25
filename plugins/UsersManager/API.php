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
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\NoAccessException;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\ThemeStyles;
use Piwik\Plugins\CoreAdminHome\Emails\AnonymousAccessEnabledEmail;
use Piwik\Plugins\CoreAdminHome\Emails\UserDeletedEmail;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\UsersManager\Emails\UserInfoChangedEmail;
use Piwik\Plugins\UsersManager\Repository\UserRepository;
use Piwik\Plugins\UsersManager\Validators\AllowedEmailDomain;
use Piwik\Plugins\UsersManager\Validators\Email;
use Piwik\Request\AuthenticationToken;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Tracker\Cache;
use Piwik\Url;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\NotEmpty;

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
 * See also the documentation about <a href='https://matomo.org/docs/manage-users/' rel='noreferrer' target='_blank'>Managing Users</a> in Matomo.
 *
 * @phpstan-import-type UserRow from Model
 */
class API extends \Piwik\Plugin\API
{
    public const OPTION_NAME_PREFERENCE_SEPARATOR = '_';

    /**
     * @var bool
     */
    public static $UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION = true;

    /**
     * @var bool
     */
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
     * @var RolesProvider
     */
    private $roleProvider;

    /**
     * @var CapabilitiesProvider
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

    /**
     * @var UserRepository
     */
    private $userRepository;

    public const PREFERENCE_THEME_MODE = 'themeMode';
    public const PREFERENCE_DEFAULT_THEME_MODE = ThemeStyles::LIGHT_MODE;

    public const PREFERENCE_DEFAULT_REPORT = 'defaultReport';
    public const PREFERENCE_DEFAULT_REPORT_DATE = 'defaultReportDate';

    /**
     * @var API|null
     */
    private static $instance = null;

    public function __construct(
        Model $model,
        UserAccessFilter $filter,
        #[\SensitiveParameter]
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
     * @return API
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
     * It does not return the `superuser` or `noaccess` roles.
     *
     * @return list<array{id: string, name: string, description: string, helpUrl: string}> List of available roles.
     */
    public function getAvailableRoles(): array
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
     *
     * @return list<array{id: string, name: string, description: string, helpUrl: string, includedInRoles: list<string>, category: string}> List of available capabilities.
     */
    public function getAvailableCapabilities(): array
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
     * Sets a supported UsersManager preference for a user.
     *
     * Plugins can add custom preference names by declaring them in their `config/config.php` like this:
     *
     * ```php
     * return array('usersmanager.user_preference_names' => \Piwik\DI::add(array('preference_name_1', 'preference_name_2')));
     * ```
     *
     * @param string $userLogin Login of the user whose preference should be updated.
     * @param string $preferenceName Preference name registered by UsersManager or plugin configuration.
     * @param mixed $preferenceValue Value to store for the preference.
     */
    public function setUserPreference(string $userLogin, string $preferenceName, $preferenceValue): void
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);

        if (!$this->model->userExists($userLogin)) {
            throw new Exception('User does not exist: ' . $userLogin);
        }

        if (strtolower($userLogin) === 'anonymous') {
            Piwik::checkUserHasSuperUserAccess();
        }

        $this->assertPreferenceNameIsSupported($preferenceName);
        $this->getUserSettingsAccessManager()->set('UsersManager', $userLogin, $preferenceName, $preferenceValue);

        /**
         * Keep legacy option key for compatibility with older LoginLdap versions.
         * @deprecated - This should be removed with Matomo 6, LoginLdap should be updated
         *               to not rely on Option storage for this setting
         */
        if ($preferenceName === 'isLDAPUser') {
            Option::set($userLogin . self::OPTION_NAME_PREFERENCE_SEPARATOR . $preferenceName, $preferenceValue);
        }
    }

    /**
     * Returns a supported UsersManager preference for a user.
     *
     * @param string $preferenceName Preference name registered by UsersManager or plugin configuration.
     * @param string|null|false $userLogin User login to read. Use `false` to read the current user.
     * @return mixed Stored preference value, or the default value when none was saved yet.
     */
    public function getUserPreference(string $preferenceName, $userLogin = false)
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
     * @ignore
     */
    public function initUserPreferenceWithDefault(string $userLogin, string $preferenceName): void
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
     * @param string[] $preferenceNames
     * @return array<string, array<string, mixed>>
     * @ignore
     */
    public function getAllUsersPreferences(array $preferenceNames): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $supportedPreferenceNames = [];
        foreach ($preferenceNames as $preferenceName) {
            $this->assertPreferenceNameIsSupported($preferenceName);
            $supportedPreferenceNames[] = $preferenceName;
        }

        $userPreferences = $this->getUserSettingsAccessManager()->getValuesForAllUsers('UsersManager', $supportedPreferenceNames);

        return $userPreferences;
    }

    private function assertPreferenceNameIsSupported(string $preference): void
    {
        if (false !== strpos($preference, self::OPTION_NAME_PREFERENCE_SEPARATOR)) {
            throw new Exception("Preference name cannot contain underscores.");
        }

        $names = [
          self::PREFERENCE_THEME_MODE,
          self::PREFERENCE_DEFAULT_REPORT,
          self::PREFERENCE_DEFAULT_REPORT_DATE,
          'isLDAPUser', // used in loginldap
          'hideSegmentDefinitionChangeMessage',// used in JS
        ];

        /** @var list<string> $customPreferences */
        $customPreferences = StaticContainer::get('usersmanager.user_preference_names');

        if (
            !in_array($preference, $names, true)
            && !in_array($preference, $customPreferences, true)
        ) {
            throw new Exception('Not supported preference name: ' . $preference);
        }
    }

    /**
     * @return mixed
     */
    private function getPreferenceValue(string $userLogin, string $preferenceName)
    {
        $this->assertPreferenceNameIsSupported($preferenceName);
        return $this->getUserSettingsAccessManager()->get('UsersManager', $userLogin, $preferenceName, false);
    }

    /**
     * @return int|string|false
     */
    private function getDefaultUserPreference(string $preferenceName, string $login)
    {
        switch ($preferenceName) {
            case self::PREFERENCE_THEME_MODE:
                return self::PREFERENCE_DEFAULT_THEME_MODE;
            case self::PREFERENCE_DEFAULT_REPORT:
                $viewableSiteIds = \Piwik\Plugins\SitesManager\API::getInstance()->getSitesIdWithAtLeastViewAccess($login);
                if (!empty($viewableSiteIds)) {
                    return reset($viewableSiteIds);
                }
                return false;
            case self::PREFERENCE_DEFAULT_REPORT_DATE:
                return (string)GeneralConfig::getConfigValue('default_day');
            default:
                return false;
        }
    }

    private function getUserSettingsAccessManager(): UserScopedSettingsAccessManager
    {
        return StaticContainer::get(UserScopedSettingsAccessManager::class);
    }

    /**
     * Returns all users with their role for $idSite.
     *
     * @param int $idSite The numeric ID of the website to inspect.
     * @param int|null $limit Maximum number of users to return.
     * @param int|null $offset Zero-based result offset.
     * @param string|null $filter_search Text to search for in user login or email.
     * @param string|null $filter_access Access filter for the site. Accepted values are `noaccess`, `some`, `view`,
     *                                   `write`, `admin`, and `superuser`. Filtering by `superuser` is only allowed
     *                                   for super users.
     * @param string|null $filter_status Invite status filter.
     * @return list<array<string, mixed>> Users visible to the current requester, enriched with role and capabilities
     *                                    for the site.
     */
    public function getUsersPlusRole(int $idSite, $limit = null, $offset = 0, $filter_search = null, $filter_access = null, $filter_status = null): array
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
                            $user['capabilities'],
                        ] = $this->getRoleAndCapabilitiesFromAccess($user['access']);
                        $user['role'] = empty($user['role']) ? 'noaccess' : reset($user['role']);
                    }

                    unset($user['access']);
                }
            }
        }

        $users = $this->userRepository->enrichUsers($users);

        foreach ($users as &$user) {
            unset($user['password']);
        }

        Common::sendHeader('X-Matomo-Total-Results: ' . $totalResults);
        return $users;
    }

    /**
     * Returns users visible to the current requester.
     *
     * @param string $userLogins Comma-separated list of user logins to fetch. Leave empty to return every visible user.
     * @return list<array<string, mixed>> Matching users enriched with invite and access metadata.
     */
    public function getUsers(string $userLogins = ''): array
    {
        Piwik::checkUserHasSomeAdminAccess();

        $logins = [];

        if (!empty($userLogins)) {
            $logins = explode(',', $userLogins);
        }

        $users = $this->model->getUsers($logins);
        $users = $this->userFilter->filterUsers($users);
        return $this->userRepository->enrichUsers($users);
    }

    /**
     * Returns the login names of all users visible to the current requester.
     *
     * @return string[] Matching user logins.
     */
    public function getUsersLogin(): array
    {
        Piwik::checkUserHasSomeAdminAccess();

        $logins = $this->model->getUsersLogin();
        $logins = $this->userFilter->filterLogins($logins);

        return $logins;
    }

    /**
     * Returns the site IDs where each user has the requested access entry.
     *
     * Users without that access entry are omitted from the result.
     *
     * @param string $access Access entry to match, for example a role or capability ID.
     * @return array<string, list<int|string>> Mapping of user login to site IDs where that access entry is assigned.
     */
    public function getUsersSitesFromAccess(string $access): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $this->checkAccessType($access);

        $userSites = $this->model->getUsersSitesFromAccess($access);
        $userSites = $this->userFilter->filterLoginIndexedArray($userSites);

        return $userSites;
    }

    /**
     * @param string|string[] $access
     */
    private function checkAccessType($access): void
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

    private function isValidAccessType(string $access): bool
    {
        return in_array($access, $this->getAllRolesAndCapabilities(), true);
    }

    /**
     * @return list<string>
     */
    private function getAllRolesAndCapabilities(): array
    {
        $roles = $this->roleProvider->getAllRoleIds();
        $capabilities = $this->capabilityProvider->getAllCapabilityIds();
        return array_merge($roles, $capabilities);
    }

    /**
     * Returns one access entry per visible user for the requested site.
     *
     * Users with no access to the site are omitted.
     *
     * @param int $idSite Numeric site ID.
     * @return array<string, string> Mapping of user login to access entry.
     */
    public function getUsersAccessFromSite(int $idSite): array
    {
        Piwik::checkUserHasAdminAccess($idSite);

        $usersAccess = $this->model->getUsersAccessFromSite($idSite);
        $usersAccess = $this->userFilter->filterLoginIndexedArray($usersAccess);

        return $usersAccess;
    }

    /**
     * Returns users who have the requested access entry for a website.
     *
     * @param int $idSite The numeric ID of the website to inspect.
     * @param string $access Access entry to match, for example a role or capability ID.
     * @return list<array<string, mixed>> Matching users enriched with user metadata.
     */
    public function getUsersWithSiteAccess(int $idSite, string $access): array
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
     * Returns the raw site access entries assigned to a user.
     *
     * Super users receive every existing site with `admin` access.
     *
     * @param string $userLogin Existing user login to inspect.
     * @return list<array{site: int|string, access: string}> Site access rows for the user.
     */
    public function getSitesAccessFromUser(string $userLogin): array
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
                  'access' => 'admin',
                ];
            }
            return $return;
        }
        return $this->model->getSitesAccessFromUser($userLogin);
    }

    /**
     * Returns site access rows for a non-superuser, with filtering and pagination.
     *
     * This method rejects super users. It sends `X-Matomo-Total-Results` and, when applicable, `X-Matomo-Has-Some`
     * response headers for pagination metadata.
     *
     * @param string $userLogin Existing non-superuser login to inspect.
     * @param int|null $limit Maximum number of sites to return.
     * @param int|null $offset Zero-based result offset.
     * @param string|null $filter_search Text to search in site names, URLs, or groups.
     * @param string|null $filter_access Access filter. Accepted values are `some`, `view`, `write`, or `admin`.
     * @return list<array{idsite: int|string, site_name: string, role: string, capabilities: list<string>}> Site access rows including role and explicit capabilities for each returned site.
     */
    public function getSitesAccessForUser(
        string $userLogin,
        $limit = null,
        $offset = 0,
        $filter_search = null,
        $filter_access = null
    ): array {
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
                $siteAccess['capabilities'],
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
     * Returns one user's metadata as visible to the requester.
     *
     * @param string $userLogin Existing user login to fetch.
     * @return array<string, mixed> Enriched user data, or an empty array when no user record is returned.
     */
    public function getUser(string $userLogin): array
    {
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);
        $this->checkUserExists($userLogin);

        $user = $this->model->getUser($userLogin);

        if (empty($user)) {
            return [];
        }

        $user = $this->userFilter->filterUser($user);
        return $this->userRepository->enrichUser($user);
    }

    /**
     * Returns one user's metadata for the given email address.
     *
     * @param string $userEmail Existing email address to look up.
     * @return array<string, mixed> Enriched user data, or an empty array when no user record is returned.
     */
    public function getUserByEmail(string $userEmail): array
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
     * Creates a new user account.
     *
     * @param string $userLogin Login name for the new user.
     * @param string $password Password for the new user.
     * @param string $email Email address for the new user.
     * @param bool $_isPasswordHashed `true` if `$password` is already pre-hashed for storage.
     * @param int|null $initialIdSite Initial site to grant `view` access to. Required for non-superusers.
     * @param string|null $passwordConfirmation Current user's password confirmation when required by session auth.
     */
    public function addUser(
        string $userLogin,
        #[\SensitiveParameter]
        string $password,
        string $email,
        $_isPasswordHashed = false,
        $initialIdSite = null,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): void {
        Piwik::checkUserHasSomeAdminAccess();
        UsersManager::dieIfUsersAdminIsDisabled();

        // check password confirmation only when using session auth
        if (StaticContainer::get(AuthenticationToken::class)->isSessionToken()) {
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
            $userLogin,
            $email,
            $initialIdSite,
            $password,
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
     * Invites a new user by email and grants initial access to a website.
     *
     * @param string $userLogin Login name for the invited user.
     * @param string $email Email address for the invited user.
     * @param int|null $initialIdSite Initial site to grant `view` access to.
     * @param int|null $expiryInDays Number of days before the invite expires. Uses the configured default when empty.
     * @param string|null $passwordConfirmation Current user's password confirmation when required by session auth.
     */
    public function inviteUser(
        string $userLogin,
        string $email,
        $initialIdSite = null,
        $expiryInDays = null,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): void {
        Piwik::checkUserHasSomeAdminAccess();
        UsersManager::dieIfUsersAdminIsDisabled();

        // check password confirmation only when using session auth
        if (StaticContainer::get(AuthenticationToken::class)->isSessionToken()) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        if (empty($expiryInDays)) {
            $expiryInDays = GeneralConfig::getConfigValue('default_invite_user_token_expiry_days');
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
     * @param string $userLogin User login to update.
     * @param bool|int|string $hasSuperUserAccess `true` or `1` to grant super user access, `false` or `0` to remove it.
     * @param string|null $passwordConfirmation Current user's password confirmation when required.
     */
    public function setSuperUserAccess(
        string $userLogin,
        $hasSuperUserAccess,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): void {
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
     * @return bool `true` if the current user has super user access.
     */
    public function hasSuperUserAccess(): bool
    {
        return Piwik::hasUserSuperUserAccess();
    }

    /**
     * Returns all users that currently have super user access.
     *
     * @return list<array<string, mixed>> Super user records enriched with invite metadata.
     */
    public function getUsersHavingSuperUserAccess(): array
    {
        Piwik::checkUserIsNotAnonymous();

        $users = $this->model->getUsersHavingSuperUserAccess();

        // we do not filter these users by access and return them all since we need to print this information in the
        // UI and they are allowed to see this.
        return $this->userRepository->enrichUsers($users);
    }


    /**
     * Updates a user in the database.
     *
     * If the password or email changes, the current user's password confirmation is required when that confirmation
     * check is enabled.
     *
     * @param string $userLogin Login name of the user to update.
     * @param string|false $password New password to set, or `false` to keep the current password.
     * @param string|false $email New email address to set, or `false` to keep the current email.
     * @param bool $_isPasswordHashed `true` if `$password` is already pre-hashed for storage.
     * @param string|false $passwordConfirmation Current user's password confirmation when required.
     */
    public function updateUser(
        string $userLogin,
        #[\SensitiveParameter]
        $password = false,
        $email = false,
        $_isPasswordHashed = false,
        #[\SensitiveParameter]
        $passwordConfirmation = false
    ): void {
        $email = Common::unsanitizeInputValue($email);
        $requirePasswordConfirmation = self::$UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION;
        self::$UPDATE_USER_REQUIRE_PASSWORD_CONFIRMATION = true;

        $isEmailNotificationOnInConfig = GeneralConfig::getConfigValue('enable_update_users_email');

        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);
        UsersManager::dieIfUsersAdminIsDisabled();
        $this->checkUserIsNotAnonymous($userLogin);
        $this->checkUserExists($userLogin);

        /** @phpstan-var UserRow $userInfo */
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
                (int)GeneralConfig::getConfigValue('default_invite_user_token_expiry_days')
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
     * Deletes a user account and all of its access assignments.
     *
     * @param string $userLogin Existing user login to delete.
     * @param string|null $passwordConfirmation Current user's password confirmation when required by session auth.
     * @return void
     */
    public function deleteUser(
        string $userLogin,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ) {
        Piwik::checkUserHasSomeAdminAccess();
        UsersManager::dieIfUsersAdminIsDisabled();
        $this->checkUserIsNotAnonymous($userLogin);

        if (StaticContainer::get(AuthenticationToken::class)->isSessionToken()) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        $this->checkUserExist($userLogin);

        /** @phpstan-var UserRow $user */
        $user = $this->model->getUser($userLogin);

        // If user is not a super user check if the user was invited by the current user
        if (!Piwik::hasUserSuperUserAccess()) {
            if ($user['invited_by'] !== Piwik::getCurrentUserLogin() || !$this->model->isPendingUser($userLogin)) {
                throw new NoAccessException(Piwik::translate('UsersManager_ExceptionUserDoesNotExist', $userLogin));
            }
        }

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
          'userLogin'    => $userLogin,
        ]);
        $email->safeSend();

        Cache::deleteTrackerCache();
    }

    /**
     * Signs a user out of all active sessions. Requires super user access.
     * Use this for security purposes, e.g., if a device was lost or compromised.
     *
     * @param string $userLogin Login of the user to sign out.
     * @param string|null $passwordConfirmation Current user's password confirmation when required by session auth.
     */
    public function logoutUser(
        string $userLogin,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): void {
        Piwik::checkUserHasSuperUserAccess();

        if (StaticContainer::get(AuthenticationToken::class)->isSessionToken()) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        BaseValidator::check('userlogin', $userLogin, [new NotEmpty()]);
        $this->checkUserIsNotAnonymous($userLogin);
        $this->checkUserExist($userLogin);

        $this->model->deleteUserSessions($userLogin);
    }

    /**
     * Returns whether the given login exists.
     *
     * The anonymous login is always treated as existing.
     *
     * @param string $userLogin Login to check.
     * @return bool `true` if the login exists.
     */
    public function userExists(string $userLogin): bool
    {
        if (strtolower($userLogin) === 'anonymous') {
            return true;
        }

        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        if ($userLogin === Piwik::getCurrentUserLogin()) {
            return true;
        }

        return $this->model->userExists($userLogin);
    }

    /**
     * Returns whether a user with the given email exists.
     *
     * @param string $userEmail Email address to check.
     * @return bool `true` if the email exists.
     */
    public function userEmailExists(string $userEmail): bool
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeViewAccess();

        return $this->model->userEmailExists($userEmail);
    }

    /**
     * Returns the login name for an existing user with the given email address.
     *
     * @param string $userEmail Email address to look up.
     * @return string Login name of the matched user.
     */
    public function getUserLoginFromUserEmail(string $userEmail): string
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeAdminAccess();

        $this->checkUserEmailExists($userEmail);

        /** @phpstan-var UserRow $user */
        $user = $this->model->getUserByEmail($userEmail);

        // any user with some admin access is allowed to find any user by email, no need to filter by access here

        return $user['login'];
    }

    /**
     * Sets access entries for a user across one or more websites.
     *
     * If `$access` is `noaccess`, any existing access entries for the selected sites are removed. Otherwise any
     * existing entries are replaced with one role plus optional capabilities.
     *
     * @param string $userLogin User login to update.
     * @param string|list<string> $access Access entries to grant. Use `noaccess` to remove access, or provide one
     *                                    role plus optional capabilities.
     * @param string|int|int[] $idSites Website ID(s) to update.
     *                                  - Single site ID (e.g. 1)
     *                                  - Multiple site IDs (e.g. [1, 4, 5])
     *                                  - Comma-separated list ("1,4,5") or "all"
     * @param string|null $passwordConfirmation Current user's password confirmation. Only required when granting
     *                                          anonymous `view` access through session auth.
     */
    public function setUserAccess(
        string $userLogin,
        $access,
        $idSites,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): void {
        UsersManager::dieIfUsersAdminIsDisabled();

        if ($access != 'noaccess') {
            $this->checkAccessType($access);
        }

        $idSites = $this->getIdSitesCheckAdminAccess($idSites);

        // check password confirmation only when using session auth and setting view access for anonymous user
        if (
            strtolower($userLogin) === 'anonymous'
            && StaticContainer::get(AuthenticationToken::class)->isSessionToken()
            && $access === 'view'
        ) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        if (
            strtolower($userLogin) === 'anonymous' &&
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
            if (strtolower($userLogin) === 'anonymous' && $access === 'view') {
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
                        'siteName' => implode(', ', $siteNames),
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
     *
     * Capabilities can only be added on sites where the user already has a role. You cannot add capabilities to the
     * anonymous user or to a super user. If the assigned role already includes a capability, no separate access row is
     * added for it.
     *
     * @param string $userLogin User login to update.
     * @param string|string[] $capabilities Capability IDs to add.
     * @param int|int[]|string $idSites Website ID or IDs to update.
     */
    public function addCapabilities(string $userLogin, $capabilities, $idSites): void
    {
        $this->executeConcurrencySafe($userLogin, function () use ($userLogin, $capabilities, $idSites) {
            $idSites = $this->getIdSitesCheckAdminAccess($idSites);

            if (strtolower($userLogin) === 'anonymous') {
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

    /**
     * @param list<string> $capabilities
     * @param list<int> $idSites
     */
    private function addCapabilitesToUser(string $userLogin, array $capabilities, array $idSites): void
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

            if ($cap === null) {
                continue;
            }

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

    /**
     * @return array{0: array<int, string>, 1: array<int, list<string>>}
     */
    private function getRolesAndCapabilitiesForLogin(string $userLogin): array
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
     *
     * Only separately granted capability rows are removed. Capabilities implied by an assigned role remain effective.
     *
     * @param string $userLogin User login to update.
     * @param string|string[] $capabilities Capability IDs to remove.
     * @param int|int[]|string $idSites Website ID or IDs to update.
     */
    public function removeCapabilities(string $userLogin, $capabilities, $idSites): void
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

    private function reloadPermissions(): void
    {
        Access::getInstance()->reloadAccess();
        Cache::deleteTrackerCache();
    }

    /**
     * @param int|list<int>|string $idSites
     * @return list<int>
     */
    private function getIdSitesCheckAdminAccess($idSites): array
    {
        // reload access to ensure we're not working with cached entries that might have been changed in between
        Access::getInstance()->reloadAccess();

        if ($idSites === 'all') {
            // in case idSites is all we grant access to all the websites on which the current connected user has an 'admin' access
            $idSites = \Piwik\Plugins\SitesManager\API::getInstance()->getSitesIdWithAdminAccess();
        } else {
            // in case the idSites is an integer we build an array
            $idSites = Site::getIdSitesFromIdSitesString($idSites, false, true);
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

    private function checkUserExists(string $userLogin): void
    {
        if (!$this->userExists($userLogin)) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionUserDoesNotExist", $userLogin));
        }
    }

    private function checkUserEmailExists(string $userEmail): void
    {
        if (!$this->userEmailExists($userEmail)) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionUserDoesNotExist", $userEmail));
        }
    }

    private function checkUserIsNotAnonymous(string $userLogin): void
    {
        if (strtolower($userLogin) === 'anonymous') {
            throw new Exception(Piwik::translate("UsersManager_ExceptionEditAnonymous"));
        }
    }

    /**
     * @param string|list<string> $userLogins
     */
    private function checkUsersHasNotSuperUserAccess($userLogins): void
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
     * @param string|list<string> $userLogin
     */
    private function isUserTheOnlyUserHavingSuperUserAccess($userLogin): bool
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
     * Generates a new app-specific API token for a user.
     *
     * @param string $userLogin Login name or email address for the user.
     * @param string $passwordConfirmation The user's current password.
     * @param string $description Description for the app-specific token, for example an app name.
     * @param string|null $expireDate Optional expiry date for the token.
     * @param int|string $expireHours Optional number of hours before the token expires. Ignored when `$expireDate` is
     *                                set.
     * @param bool $secureOnly `true` if the token must not be accepted in GET requests.
     * @return string Newly generated app-specific token.
     */
    public function createAppSpecificTokenAuth(
        string $userLogin,
        #[\SensitiveParameter]
        string $passwordConfirmation,
        string $description,
        $expireDate = null,
        $expireHours = 0,
        bool $secureOnly = false
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
        $this->model->addTokenAuth($userLogin, $generatedToken, $description, Date::now()->getDatetime(), $expireDate, false, $secureOnly);

        return $generatedToken;
    }

    /**
     * Signs the current user up for the Matomo newsletter.
     *
     * @return array{success?: true, error?: true} Signup result payload.
     */
    public function newsletterSignup(): array
    {
        Piwik::checkUserIsNotAnonymous();

        $userLogin = Piwik::getCurrentUserLogin();
        $email = Piwik::getCurrentUserEmail();

        $success = NewsletterSignup::signupForNewsletter($userLogin, $email, true);
        $result = $success ? ['success' => true] : ['error' => true];
        return $result;
    }

    private function isUserHasAdminAccessTo(int $idSite): bool
    {
        try {
            Piwik::checkUserHasAdminAccess([$idSite]);
            return true;
        } catch (NoAccessException $ex) {
            return false;
        }
    }

    private function checkUserExist(string $userLogin): void
    {
        $userExists = $this->model->userExists($userLogin);
        if (!$userExists) {
            throw new Exception(Piwik::translate("UsersManager_ExceptionUserDoesNotExist", $userLogin));
        }
    }

    /**
     * @param string[] $access
     * @return array{0: list<string>, 1: list<string>}
     */
    private function getRoleAndCapabilitiesFromAccess(array $access): array
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

    /**
     * @param array{email: string, login: string} $user
     */
    private function sendEmailChangedEmail(array $user, string $newEmail): void
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

    /**
     * @param array{login: string} $user
     */
    private function sendUserInfoChangedEmail(string $type, array $user, ?string $newValue, string $emailTo, string $subject): void
    {
        $deviceDescription = $this->getDeviceDescription();

        $mail = new UserInfoChangedEmail($type, $newValue, $deviceDescription, $user['login']);

        $mail->addTo($emailTo, $user['login']);
        $mail->setSubject(Piwik::translate($subject));

        $mail->send();
    }

    /**
     * @param array{email: string, login: string} $user
     */
    private function sendPasswordChangedEmail(array $user): void
    {
        $this->sendUserInfoChangedEmail(
            'password',
            $user,
            null,
            $user['email'],
            'UsersManager_PasswordChangeNotificationSubject'
        );
    }

    private function getDeviceDescription(): string
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
     * Resends an existing user invitation email.
     *
     * @param string $userLogin Login name of the invited user.
     * @param int $expiryInDays Number of days before the regenerated invite expires.
     * @param string|null $passwordConfirmation Current user's password confirmation when required by session auth.
     */
    public function resendInvite(
        string $userLogin,
        $expiryInDays = 7,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): void {
        Piwik::checkUserHasSomeAdminAccess();

        // check password confirmation only when using session auth
        if (StaticContainer::get(AuthenticationToken::class)->isSessionToken()) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        if (!$this->model->isPendingUser($userLogin)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionUserDoesNotExist', $userLogin));
        }

        /** @phpstan-var UserRow $user */
        $user = $this->model->getUser($userLogin);

        // If user is not a super user check if the user was invited by the current user
        if (!Piwik::hasUserSuperUserAccess()) {
            if ($user['invited_by'] !== Piwik::getCurrentUserLogin()) {
                throw new NoAccessException(Piwik::translate('UsersManager_ExceptionResendInviteDenied', $userLogin));
            }
        }

        $this->userRepository->reInviteUser($userLogin, (int)$expiryInDays);

        /**
         * Triggered after a new user was invited.
         *
         * @param string $userLogin The new user's login.
         */
        Piwik::postEvent('UsersManager.inviteUser.resendInvite', [$userLogin, $user['email']]);
    }

    /**
     * Generates a fresh invitation link for an existing pending user.
     *
     * @param string $userLogin Login name of the invited user.
     * @param int $expiryInDays Number of days before the generated invite expires.
     * @param string|null $passwordConfirmation Current user's password confirmation when required by session auth.
     * @return string Generated invitation URL.
     */
    public function generateInviteLink(
        string $userLogin,
        $expiryInDays = 7,
        #[\SensitiveParameter]
        ?string $passwordConfirmation = null
    ): string {
        Piwik::checkUserHasSomeAdminAccess();

        // check password confirmation only when using session auth
        if (StaticContainer::get(AuthenticationToken::class)->isSessionToken()) {
            $this->confirmCurrentUserPassword($passwordConfirmation);
        }

        if (!$this->model->isPendingUser($userLogin)) {
            throw new Exception(Piwik::translate('UsersManager_ExceptionUserDoesNotExist', $userLogin));
        }

        /** @phpstan-var UserRow $user */
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

    private function executeConcurrencySafe(string $userLogin, callable $callback): void
    {
        $lock = new Lock(StaticContainer::get(LockBackend::class), 'UsersManager.changePermissions');
        $lock->execute($userLogin, $callback);
    }
}
