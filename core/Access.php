<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Exception;
use Piwik\Access\CapabilitiesProvider;
use Piwik\Access\Role\Admin;
use Piwik\Access\Role\View;
use Piwik\Access\Role\Write;
use Piwik\API\Request;
use Piwik\Access\RolesProvider;
use Piwik\Http\BadRequestException;
use Piwik\Request\AuthenticationToken;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
use Piwik\Plugins\SitesManager\Model as SitesManagerModel;
use Piwik\Plugins\UsersManager\Model as UsersModel;
use Piwik\Session\SessionAuth;

/**
 * Singleton that manages user access to Piwik resources.
 *
 * To check whether a user has access to a resource, use one of the {@link Piwik Piwik::checkUser...}
 * methods.
 *
 * In Piwik there are four different access levels:
 *
 * - **no access**: Users with this access level cannot view the resource.
 * - **view access**: Users with this access level can view the resource, but cannot modify it.
 * - **admin access**: Users with this access level can view and modify the resource.
 * - **Super User access**: Only the Super User has this access level. It means the user can do
 *                          whatever they want.
 *
 *                          Super user access is required to set some configuration options.
 *                          All other options are specific to the user or to a website.
 *
 * Access is granted per website. Users with access for a website can view all
 * data associated with that website.
 *
 */
class Access
{
    /**
     * Array of idsites available to the current user, indexed by permission level
     * @see getSitesIdWith*()
     *
     * @var array
     */
    protected $idsitesByAccess = null;

    /**
     * Login of the current user
     *
     * @var string|null
     */
    protected $login = null;

    /**
     * token_auth of the current user
     *
     * @var string|null
     */
    protected $token_auth = null;

    /**
     * Defines if the current user is the Super User
     * @see hasSuperUserAccess()
     *
     * @var bool
     */
    protected $hasSuperUserAccess = false;

    /**
     * Optional token-level access cap loaded from auth context.
     *
     * @var string|null
     */
    private $tokenAccessLevel = null;

    /**
     * Whether the current auth result had superuser access before token-level clamping.
     *
     * @var bool
     */
    private $isCurrentAuthSuperUser = false;

    /**
     * Authentication object (see Auth)
     *
     * @var Auth|null
     */
    private ?Auth $auth = null;

    private bool $sessionExpired = false;

    /**
     * Gets the singleton instance. Creates it if necessary.
     *
     * @return self
     */
    public static function getInstance()
    {
        return StaticContainer::get('Piwik\Access');
    }

    /**
     * @var CapabilitiesProvider
     */
    protected $capabilityProvider;

    /**
     * @var RolesProvider
     */
    private $roleProvider;

    public function __construct(?RolesProvider $roleProvider = null, ?CapabilitiesProvider $capabilityProvider = null)
    {
        if (!isset($roleProvider)) {
            $roleProvider = StaticContainer::get('Piwik\Access\RolesProvider');
        }
        if (!isset($capabilityProvider)) {
            $capabilityProvider = StaticContainer::get('Piwik\Access\CapabilitiesProvider');
        }
        $this->roleProvider = $roleProvider;
        $this->capabilityProvider = $capabilityProvider;

        $this->resetSites();
    }

    private function resetSites()
    {
        $this->idsitesByAccess = $this->getEmptyRoleSiteIds();
    }

    /**
     * Loads the access levels for the current user.
     *
     * Calls the authentication method to try to log the user in the system.
     * If the user credentials are not correct we don't load anything.
     * If the login/password is correct the user is either the SuperUser or a normal user.
     * We load the access levels for this user for all the websites.
     *
     * @param null|Auth $auth Auth adapter
     * @return bool  true on success, false if reloading access failed (when auth object wasn't specified and user is not enforced to be Super User)
     */
    public function reloadAccess(?Auth $auth = null)
    {
        $this->resetSites();

        if (isset($auth)) {
            $this->auth = $auth;
        }

        if ($this->hasSuperUserAccess()) {
            $this->makeSureLoginNameIsSet();
            return true;
        }

        // Reset below the super-user short-circuit, together with the other per-authentication state. The
        // short-circuit returns without authenticating, so it leaves $login and $token_auth in place; the
        // token scope describes that same authentication and has to stay in place with them. Clearing it
        // above would drop the cap for every access load after super-user access is given back up again
        // (Access::doAsSuperUser() restores it to what it was), and the request would continue with the
        // user's unclamped role access.
        $this->tokenAccessLevel = null;
        $this->isCurrentAuthSuperUser = false;
        $this->token_auth = null;
        $this->login = null;

        // if the Auth wasn't set, we may be in the special case of setSuperUser(), otherwise we fail TODO: docs + review
        if (!isset($this->auth)) {
            return false;
        }

        $result = null;

        // Never on a tracker request. The session branch below is selected by `module`, `action` and
        // `force_api_session`, none of which are part of the tracker's request contract, so without this
        // guard an unauthenticated tracking request could hand itself an API session: since
        // Tracker\Request::authenticateSuperUserOrAdminOrWrite() reaches this method for every token that
        // misses the per-site tracking token cache, `matomo.php?...&module=API&action=index&force_api_session=1`
        // would start a session and write a row to the session table.
        $isApiRequest = !SettingsServer::isTrackerApiRequest()
            && Piwik::getModule() === 'API'
            && (Piwik::getAction() === 'index' || !Piwik::getAction());
        $apiMethod = Request::getMethodIfApiRequest(null);
        $isGetApiRequest = !empty($apiMethod) && 1 === substr_count($apiMethod, '.') && strpos($apiMethod, '.get') > 0;

        $token = StaticContainer::get(AuthenticationToken::class);

        if ($isApiRequest && $token->isSessionToken() && ($token->wasTokenAuthProvidedSecurely() || $isGetApiRequest)) {
            $tokenAuth = $token->getAuthToken();
            Session::start();
            $auth = StaticContainer::get(SessionAuth::class);
            $auth->setTokenAuth($tokenAuth);
            $result = $auth->authenticate();
            // Note: We do not post a failed login event at this point on purpose
            // If using the SessionAuth doesn't work, the FrontController will try to reload the Auth using
            // the token_auth only. If that works everything is "fine" and the `force_api_session` parameter was
            // unneeded. If that fails as well it will trigger the failed login event
            // See FrontController::init() or Request::reloadAuthUsingTokenAuth()
            Session::close();
            // if not successful, we will fallback to regular auth
        }

        // access = array ( idsite => accessIdSite, idsite2 => accessIdSite2)
        if (!$result || !$result->wasAuthenticationSuccessful()) {
            $result = $this->auth->authenticate();
        }

        if (!$result->wasAuthenticationSuccessful()) {
            return false;
        }

        $this->login = $result->getIdentity();
        $this->token_auth = $result->getTokenAuth();
        $this->tokenAccessLevel = $this->resolveTokenAccessLevelForResult($result);
        $this->isCurrentAuthSuperUser = $result->hasSuperUserAccess();

        // case the superUser is logged in
        if ($result->hasSuperUserAccess() && !$this->isSuperUserRestrictedByTokenAccessLevel()) {
            $this->setSuperUserAccess(true);
        }

        return true;
    }

    public function getRawSitesWithSomeViewAccess($login)
    {
        $sql = self::getSqlAccessSite("access, t2.idsite");

        return Db::fetchAll($sql, $login);
    }

    /**
     * Returns the SQL query joining sites and access table for a given login
     *
     * @param string $select Columns or expression to SELECT FROM table, eg. "MIN(ts_created)"
     * @return string  SQL query
     */
    public static function getSqlAccessSite($select)
    {
        $access    = Common::prefixTable('access');
        $siteTable = Common::prefixTable('site');

        return "SELECT " . $select . " FROM `" . $access . "` as t1
				JOIN `" . $siteTable . "` as t2 USING (idsite) WHERE login = ?";
    }

    /**
     * Make sure a login name is set
     */
    protected function makeSureLoginNameIsSet(): void
    {
        if (empty($this->login)) {
            // flag to force non empty login so Super User is not mistaken for anonymous
            $this->login = 'super user was set';
        }
    }

    protected function loadSitesIfNeeded()
    {
        if ($this->hasSuperUserAccess) {
            if (empty($this->idsitesByAccess['superuser'])) {
                try {
                    $api = SitesManagerApi::getInstance();
                    $allSitesId = $api->getAllSitesId();
                } catch (\Exception $e) {
                    $allSitesId = array();
                }
                $this->idsitesByAccess['superuser'] = $allSitesId;
            }
        } elseif (isset($this->login)) {
            if (
                empty($this->idsitesByAccess['view'])
                && empty($this->idsitesByAccess['write'])
                && empty($this->idsitesByAccess['admin'])
            ) {
                // we join with site in case there are rows in access for an idsite that doesn't exist anymore
                // (backward compatibility ; before we deleted the site without deleting rows in _access table)
                $accessRaw = $this->getRawSitesWithSomeViewAccess($this->login);

                foreach ($accessRaw as $access) {
                    $accessType = $access['access'];
                    $this->idsitesByAccess[$accessType][] = $access['idsite'];

                    if ($this->roleProvider->isValidRole($accessType)) {
                        foreach ($this->capabilityProvider->getAllCapabilities() as $capability) {
                            if ($capability->hasRoleCapability($accessType)) {
                                // we automatically add this capability
                                if (!isset($this->idsitesByAccess[$capability->getId()])) {
                                    $this->idsitesByAccess[$capability->getId()] = array();
                                }
                                $this->idsitesByAccess[$capability->getId()][] = $access['idsite'];
                            }
                        }
                    }
                }

                /**
                 * Triggered after the initial access levels and permissions for the current user are loaded. Use this
                 * event to modify the current user's permissions (for example, making sure every user has view access
                 * to a specific site).
                 *
                 * **Example**
                 *
                 *     function (&$idsitesByAccess, $login) {
                 *         if ($login == 'somespecialuser') {
                 *             return;
                 *         }
                 *
                 *         $idsitesByAccess['view'][] = $mySpecialIdSite;
                 *     }
                 *
                 * @param array[] &$idsitesByAccess The current user's access levels for individual sites. Maps role and
                 *                                  capability IDs to list of site IDs, eg:
                 *
                 *                                  ```
                 *                                  [
                 *                                      'view' => [1, 2, 3],
                 *                                      'write' => [4, 5],
                 *                                      'admin' => [],
                 *                                  ]
                 *                                  ```
                 * @param string $login The current user's login.
                 */
                Piwik::postEvent('Access.modifyUserAccess', [&$this->idsitesByAccess, $this->login]);

                $this->applyTokenAccessLevelRestrictionToLoadedSites();
            }
        }
    }

    /**
     * Applies token-level role clamping after initial access + event modifications are loaded.
     *
     * Sites that the user only reaches via a manually-granted capability row (no role row) are
     * filtered out here, because such sites never enter {@see buildSiteRoleMap()}. A scoped token
     * therefore loses access to those sites entirely, even if the capability would otherwise be
     * compatible with the clamped role. Users that rely on capability-only access should issue
     * unscoped tokens.
     */
    private function applyTokenAccessLevelRestrictionToLoadedSites(): void
    {
        if (empty($this->tokenAccessLevel) || $this->tokenAccessLevel === 'superuser') {
            return;
        }

        if ($this->tokenAccessLevel === 'noaccess') {
            // No site survives this cap, so there is nothing to map and no reason to enumerate the site
            // table for a capped superuser token. This is reached when a token's scope could not be
            // established, which includes the token metadata lookup having failed - the last moment to
            // spend a query on a request that is being denied anyway.
            $this->replaceRoleSiteIds($this->getEmptyRoleSiteIds());
            $this->rebuildCapabilitySiteIdsFromRoles([]);
            return;
        }

        $siteRoleMap = $this->buildSiteRoleMap();

        if ($this->isSuperUserRestrictedByTokenAccessLevel()) {
            try {
                // A capped superuser token no longer has superuser privileges, so API-level
                // getAllSitesId() would fail permission checks. Fetch raw site IDs directly.
                $allSiteIds = $this->getSitesManagerModel()->getSitesId();
            } catch (\Exception $e) {
                StaticContainer::get(LoggerInterface::class)->debug(
                    'Could not enumerate sites while applying token-level access restriction; '
                    . 'capped superuser token will fall back to sites with explicit access only. {exception}',
                    ['exception' => $e]
                );
                $allSiteIds = [];
            }
            // A superuser's implicit role outranks any explicit access row, which is why getRoleForSite()
            // answers 'admin' for an uncapped superuser regardless of that user's rows. Seeding every site
            // unconditionally keeps that property under a cap, and keeps these lists in agreement with the
            // getRoleForSite() short-circuit; a leftover low-privilege row must not reduce a capped token
            // below what the same token would grant on a site with no row at all.
            foreach ($allSiteIds as $idSite) {
                $siteRoleMap[$idSite] = 'superuser';
            }
        }

        $restrictedByRole = $this->getEmptyRoleSiteIds();
        $restrictedRolesBySite = [];

        foreach ($siteRoleMap as $idSite => $currentRole) {
            $restrictedRole = $this->clampRoleByTokenAccessLevel($currentRole);
            if ($restrictedRole === 'noaccess') {
                continue;
            }

            $restrictedByRole[$restrictedRole][] = $idSite;
            $restrictedRolesBySite[$idSite] = $restrictedRole;
        }

        $this->replaceRoleSiteIds($restrictedByRole);
        $this->rebuildCapabilitySiteIdsFromRoles($restrictedRolesBySite);
    }

    /**
     * Returns a map of site ID to the highest role currently loaded for that site.
     *
     * Roles are checked from highest to lowest priority so the first assignment wins.
     *
     * @return array<int|string, string>
     */
    private function buildSiteRoleMap(): array
    {
        $map = [];
        foreach (self::getTokenAccessLevelsDescending() as $role) {
            foreach ($this->idsitesByAccess[$role] as $idSite) {
                if (!isset($map[$idSite])) {
                    $map[$idSite] = $role;
                }
            }
        }
        return $map;
    }

    /**
     * @return array<string,array<int,int|string>>
     */
    private function getEmptyRoleSiteIds(): array
    {
        return [
            'view' => [],
            'write' => [],
            'admin' => [],
            'superuser' => [],
        ];
    }

    /**
     * @param array<string,array<int,int|string>> $siteIdsByRole
     */
    private function replaceRoleSiteIds(array $siteIdsByRole): void
    {
        foreach ($this->getEmptyRoleSiteIds() as $role => $emptySiteIds) {
            $this->idsitesByAccess[$role] = array_values(array_unique($siteIdsByRole[$role] ?? $emptySiteIds));
        }
    }

    /**
     * Scoped tokens are role-only: capabilities are derived from the capped effective role,
     * and separately granted capability rows are ignored. A user with a manual capability grant on a
     * site (and no role on that site) loses the capability under any scoped token, even when the
     * capability's role tier would be compatible with the clamp; "Inherit user access" is the only
     * way to keep such grants.
     *
     * @param array<int|string,string> $rolesBySite
     */
    private function rebuildCapabilitySiteIdsFromRoles(array $rolesBySite): void
    {
        $capabilities = $this->capabilityProvider->getAllCapabilities();

        foreach ($capabilities as $capability) {
            $this->idsitesByAccess[$capability->getId()] = [];
        }

        foreach ($rolesBySite as $idSite => $role) {
            foreach ($capabilities as $capability) {
                if (!$capability->hasRoleCapability($role)) {
                    continue;
                }

                $this->idsitesByAccess[$capability->getId()][] = $idSite;
            }
        }

        foreach ($capabilities as $capability) {
            $capabilityId = $capability->getId();
            $this->idsitesByAccess[$capabilityId] = array_values(array_unique($this->idsitesByAccess[$capabilityId]));
        }
    }

    private function isSuperUserRestrictedByTokenAccessLevel(): bool
    {
        return $this->isCurrentAuthSuperUser
            && !empty($this->tokenAccessLevel)
            && $this->tokenAccessLevel !== 'superuser';
    }

    /**
     * Returns the canonical numeric ranking for each role/access level used for token clamping.
     * Lower numbers mean less access; 'noaccess' (0) is the floor.
     *
     * @return array<string,int>
     */
    public static function getTokenAccessLevelRankings(): array
    {
        return [
            'noaccess'  => 0,
            View::ID    => 1,
            Write::ID   => 2,
            Admin::ID   => 3,
            'superuser' => 4,
        ];
    }

    /**
     * Returns the user-assignable token access levels in ascending order of privilege.
     *
     * Derived from {@see getTokenAccessLevelRankings()} with the 'noaccess' floor removed,
     * so this is the canonical source of truth for the levels a token can be scoped to.
     *
     * @return string[]
     */
    public static function getTokenAccessLevels(): array
    {
        $levels = array_keys(self::getTokenAccessLevelRankings());
        return array_values(array_filter($levels, function (string $level) {
            return $level !== 'noaccess';
        }));
    }

    /**
     * Returns the user-assignable token access levels in descending order of privilege.
     *
     * Companion to {@see getTokenAccessLevels()} for callers that iterate from most to least privileged
     * (e.g. role-priority resolution and human-readable error messages).
     *
     * @return string[]
     */
    public static function getTokenAccessLevelsDescending(): array
    {
        return array_reverse(self::getTokenAccessLevels());
    }

    private function clampRoleByTokenAccessLevel(string $role): string
    {
        if (empty($this->tokenAccessLevel) || $this->tokenAccessLevel === 'superuser') {
            return $role;
        }

        $rankByRole = self::getTokenAccessLevelRankings();
        $roleByRank = array_flip($rankByRole);

        $roleRank = $rankByRole[$role] ?? null;
        $tokenRank = $rankByRole[$this->tokenAccessLevel] ?? null;
        if ($roleRank === null || $tokenRank === null) {
            return $role;
        }

        return $roleByRank[min($roleRank, $tokenRank)];
    }

    /**
     * Normalises a declared token access level into the cap to apply.
     *
     * Null and the empty string both mean the token carries no scope. A level from
     * {@see getTokenAccessLevels()} is returned unchanged. Anything else resolves to the 'noaccess' floor
     * rather than to "no scope": an unrecognised non-empty value means the token's scope cannot be
     * established, and a token whose scope cannot be established must not be promoted to the user's full
     * access. That covers a truncated or corrupted column, a value written by a component that does not
     * share this list, and a downgrade to a Matomo version that knows fewer levels.
     *
     * @param mixed $tokenAccessLevel
     */
    private function normalizeTokenAccessLevel($tokenAccessLevel): ?string
    {
        if ($tokenAccessLevel === null || $tokenAccessLevel === '') {
            return null;
        }

        if (is_string($tokenAccessLevel) && in_array($tokenAccessLevel, self::getTokenAccessLevels(), true)) {
            return $tokenAccessLevel;
        }

        return 'noaccess';
    }

    /**
     * Resolves the token-level access cap for an authenticated request.
     *
     * Auth plugins that declare a `token_access_level` in {@see AuthResult::getAuthContext()} (core Login does
     * this) own the result, including a declared null, which states that the token carries no scope. The key
     * has to be declared to own it: gating on the context merely being present would let any plugin that
     * passes a context for unrelated reasons of its own switch scope clamping off wholesale.
     *
     * Without that key the cap is derived from the submitted token's user_token_auth row instead, so scope
     * clamping is enforced regardless of which Piwik\Auth implementation is active. The fallback is gated on
     * the AuthResult's own tokenAuth matching the request's submitted token so password/session login (no
     * submitted token) and bulk-API sub-requests authenticated against a different token than the outer
     * request never clamp from an unrelated token row.
     */
    private function resolveTokenAccessLevelForResult(AuthResult $result): ?string
    {
        $authContext = $result->getAuthContext();

        if (is_array($authContext) && array_key_exists('token_access_level', $authContext)) {
            return $this->normalizeTokenAccessLevel($authContext['token_access_level']);
        }

        $submittedToken = StaticContainer::get(AuthenticationToken::class)->getAuthToken();
        if ($submittedToken === '' || $submittedToken !== $result->getTokenAuth()) {
            return null;
        }

        return $this->resolveTokenAccessLevelFromSubmittedToken($submittedToken);
    }

    private function resolveTokenAccessLevelFromSubmittedToken(
        #[\SensitiveParameter]
        string $submittedToken
    ): ?string {
        try {
            // getTokenMetadataByTokenAuth() is cache-aware and serves the row from
            // AuthenticationToken's per-request cache when populated by an earlier lookup in this
            // request (e.g. Login\Auth::authenticateWithToken). The shared cache is intentional:
            // the access_level value is identical across callers in the same request, so reusing it
            // avoids re-querying user_token_auth on every reloadAccess() in bulk API and CliMulti
            // paths.
            $metadata = $this->getUsersModel()->getTokenMetadataByTokenAuth($submittedToken);
        } catch (\Exception $e) {
            StaticContainer::get(LoggerInterface::class)->debug(
                'Could not look up token metadata while resolving token access level; '
                . 'falling back to uncapped access. {exception}',
                ['exception' => $e]
            );
            return 'noaccess';
        }

        // A missing row, or a row from before the 6.0.0-b2 migration added the column, carries no scope
        // information at all. That is not the same as a value that could not be recognised: while the column
        // is still pending no token can be scoped, so there is nothing to fail closed on.
        if ($metadata === null || !array_key_exists('access_level', $metadata)) {
            return null;
        }

        return $this->normalizeTokenAccessLevel($metadata['access_level']);
    }

    protected function getUsersModel(): UsersModel
    {
        return new UsersModel();
    }

    protected function getSitesManagerModel(): SitesManagerModel
    {
        return new SitesManagerModel();
    }

    /**
     * We bypass the normal auth method and give the current user Super User rights.
     * This should be very carefully used.
     *
     * @param bool $bool
     */
    public function setSuperUserAccess($bool = true)
    {
        $this->hasSuperUserAccess = (bool) $bool;

        if ($bool) {
            $this->makeSureLoginNameIsSet();
        } else {
            $this->resetSites();
        }
    }

    /**
     * Returns true if the current user is logged in as the Super User
     *
     * @return bool
     */
    public function hasSuperUserAccess()
    {
        return $this->hasSuperUserAccess;
    }

    /**
     * Returns the current user login
     *
     * @return string|null
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Returns the token_auth used to authenticate this user in the API
     *
     * @return string|null
     */
    public function getTokenAuth()
    {
        return $this->token_auth;
    }

    /**
     * Returns an array of ID sites for which the user has at least a VIEW access.
     * Which means VIEW OR WRITE or ADMIN or SUPERUSER.
     *
     * @return array  Example if the user is ADMIN for 4
     *                and has VIEW access for 1 and 7, it returns array(1, 4, 7);
     */
    public function getSitesIdWithAtLeastViewAccess()
    {
        $this->loadSitesIfNeeded();

        return array_unique(array_merge(
            $this->idsitesByAccess['view'],
            $this->idsitesByAccess['write'],
            $this->idsitesByAccess['admin'],
            $this->idsitesByAccess['superuser']
        ));
    }

    /**
     * Returns an array of ID sites for which the user has at least a WRITE access.
     * Which means WRITE or ADMIN or SUPERUSER.
     *
     * @return array  Example if the user is WRITE for 4 and 8
     *                and has VIEW access for 1 and 7, it returns array(4, 8);
     */
    public function getSitesIdWithAtLeastWriteAccess()
    {
        $this->loadSitesIfNeeded();

        return array_unique(array_merge(
            $this->idsitesByAccess['write'],
            $this->idsitesByAccess['admin'],
            $this->idsitesByAccess['superuser']
        ));
    }

    /**
     * Returns an array of ID sites for which the user has an ADMIN access.
     *
     * @return array  Example if the user is ADMIN for 4 and 8
     *                and has VIEW access for 1 and 7, it returns array(4, 8);
     */
    public function getSitesIdWithAdminAccess()
    {
        $this->loadSitesIfNeeded();

        return array_unique(array_merge(
            $this->idsitesByAccess['admin'],
            $this->idsitesByAccess['superuser']
        ));
    }

    /**
     * Returns an array of ID sites for which the user has a VIEW access only.
     *
     * @return array  Example if the user is ADMIN for 4
     *                and has VIEW access for 1 and 7, it returns array(1, 7);
     * @see getSitesIdWithAtLeastViewAccess()
     */
    public function getSitesIdWithViewAccess()
    {
        $this->loadSitesIfNeeded();

        return $this->idsitesByAccess['view'];
    }

    /**
     * Returns an array of ID sites for which the user has a WRITE access only.
     *
     * @return array  Example if the user is ADMIN for 4
     *                and has WRITE access for 1 and 7, it returns array(1, 7);
     * @see getSitesIdWithAtLeastWriteAccess()
     */
    public function getSitesIdWithWriteAccess()
    {
        $this->loadSitesIfNeeded();

        return $this->idsitesByAccess['write'];
    }

    /**
     * Throws an exception if the user is not the SuperUser
     *
     * @throws \Piwik\NoAccessException
     */
    public function checkUserHasSuperUserAccess()
    {
        if (!$this->hasSuperUserAccess()) {
            $this->throwNoAccessException(Piwik::translate('General_ExceptionPrivilege', array("'superuser'")));
        }
    }

    /**
     * Returns `true` if the current user has write access to at least one site.
     *
     * @return bool
     */
    public function isUserHasSomeWriteAccess()
    {
        if ($this->hasSuperUserAccess()) {
            return true;
        }

        $idSitesAccessible = $this->getSitesIdWithAtLeastWriteAccess();

        return count($idSitesAccessible) > 0;
    }

    /**
     * Returns `true` if the current user has admin access to at least one site.
     *
     * @return bool
     */
    public function isUserHasSomeAdminAccess()
    {
        if ($this->hasSuperUserAccess()) {
            return true;
        }

        $idSitesAccessible = $this->getSitesIdWithAdminAccess();

        return count($idSitesAccessible) > 0;
    }

    /**
     * If the user doesn't have an WRITE access for at least one website, throws an exception
     *
     * @throws \Piwik\NoAccessException
     */
    public function checkUserHasSomeWriteAccess()
    {
        if (!$this->isUserHasSomeWriteAccess()) {
            $this->throwNoAccessException(Piwik::translate('General_ExceptionPrivilegeAtLeastOneWebsite', array('write')));
        }
    }

    /**
     * If the user doesn't have an ADMIN access for at least one website, throws an exception
     *
     * @throws \Piwik\NoAccessException
     */
    public function checkUserHasSomeAdminAccess()
    {
        if (!$this->isUserHasSomeAdminAccess()) {
            $this->throwNoAccessException(Piwik::translate('General_ExceptionPrivilegeAtLeastOneWebsite', array('admin')));
        }
    }

    /**
     * If the user doesn't have any view permission, throw exception
     *
     * @throws \Piwik\NoAccessException
     */
    public function checkUserHasSomeViewAccess()
    {
        if ($this->hasSuperUserAccess()) {
            return;
        }

        $idSitesAccessible = $this->getSitesIdWithAtLeastViewAccess();

        if (count($idSitesAccessible) == 0) {
            $this->throwNoAccessException(Piwik::translate('General_ExceptionPrivilegeAtLeastOneWebsite', array('view')));
        }
    }

    /**
     * This method checks that the user has ADMIN access for the given list of websites.
     * If the user doesn't have ADMIN access for at least one website of the list, we throw an exception.
     *
     * @param int|array $idSites List of ID sites to check
     * @throws \Piwik\NoAccessException If for any of the websites the user doesn't have an ADMIN access
     */
    public function checkUserHasAdminAccess($idSites)
    {
        if ($this->hasSuperUserAccess()) {
            return;
        }

        $idSites = $this->getIdSites($idSites);
        $idSitesAccessible = $this->getSitesIdWithAdminAccess();

        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $idSitesAccessible)) {
                $this->throwNoAccessException(Piwik::translate('General_ExceptionPrivilegeAccessWebsite', array("'admin'", $idsite)));
            }
        }
    }

    /**
     * This method checks that the user has VIEW or ADMIN access for the given list of websites.
     * If the user doesn't have VIEW or ADMIN access for at least one website of the list, we throw an exception.
     *
     * @param int|array|string $idSites List of ID sites to check (integer, array of integers, string comma separated list of integers)
     * @throws \Piwik\NoAccessException  If for any of the websites the user doesn't have an VIEW or ADMIN access
     */
    public function checkUserHasViewAccess($idSites)
    {
        if ($this->hasSuperUserAccess()) {
            return;
        }

        $idSites = $this->getIdSites($idSites);
        $idSitesAccessible = $this->getSitesIdWithAtLeastViewAccess();

        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $idSitesAccessible)) {
                $this->throwNoAccessException(Piwik::translate('General_ExceptionPrivilegeAccessWebsite', array("'view'", $idsite)));
            }
        }
    }

    /**
     * This method checks that the user has WRITE access for the given list of websites.
     * If the user doesn't have WRITE access for at least one website of the list, we throw an exception.
     *
     * @param int|array|string $idSites List of ID sites to check (integer, array of integers, string comma separated list of integers)
     * @throws \Piwik\NoAccessException  If for any of the websites the user doesn't have a WRITE access
     */
    public function checkUserHasWriteAccess($idSites)
    {
        if ($this->hasSuperUserAccess()) {
            return;
        }

        $idSites = $this->getIdSites($idSites);
        $idSitesAccessible = $this->getSitesIdWithAtLeastWriteAccess();

        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $idSitesAccessible)) {
                $this->throwNoAccessException(Piwik::translate('General_ExceptionPrivilegeAccessWebsite', array("'write'", $idsite)));
            }
        }
    }

    public function checkUserIsNotAnonymous()
    {
        if ($this->hasSuperUserAccess()) {
            return;
        }
        if (Piwik::isUserIsAnonymous()) {
            $this->throwNoAccessException(Piwik::translate('General_YouMustBeLoggedIn'));
        }
    }

    private function getSitesIdWithCapability($capability)
    {
        // Capability site ids are derived from the loaded roles, so they are only present once sites have
        // been loaded. Every other reader of $idsitesByAccess loads first; this one used to rely on an
        // earlier call having done it, which silently answers "no capability" when nothing has.
        $this->loadSitesIfNeeded();

        if (!empty($this->idsitesByAccess[$capability])) {
            return $this->idsitesByAccess[$capability];
        }
        return array();
    }

    public function checkUserHasCapability($idSites, $capability)
    {
        if ($this->hasSuperUserAccess()) {
            return;
        }

        $idSites = $this->getIdSites($idSites);
        $idSitesAccessible = $this->getSitesIdWithCapability($capability);

        foreach ($idSites as $idsite) {
            if (!in_array($idsite, $idSitesAccessible)) {
                $this->throwNoAccessException(Piwik::translate('General_ExceptionCapabilityAccessWebsite', array("'" . $capability . "'", $idsite)));
            }
        }

        // a capability applies only when the user also has at least view access
        $this->checkUserHasViewAccess($idSites);
    }

    /**
     * @param int|array|string $idSites
     * @return array
     * @throws BadRequestException
     */
    protected function getIdSites($idSites)
    {
        if ($idSites === 'all' || $idSites === ['all']) {
            $idSites = $this->getSitesIdWithAtLeastViewAccess();
        }

        $idSites = Site::getIdSitesFromIdSitesString($idSites, false, true);

        if (empty($idSites)) {
            throw new BadRequestException("The parameter 'idSite=' is missing from the request.");
        }

        return $idSites;
    }

    /**
     * Executes a callback with superuser privileges, making sure those privileges are rescinded
     * before this method exits. Privileges will be rescinded even if an exception is thrown.
     *
     * Use this method with care, as it might open up attack vectors
     *
     * @param callable $function The callback to execute. Should accept no arguments.
     * @return mixed The result of `$function`.
     * @throws Exception rethrows any exceptions thrown by `$function`.
     * @api
     */
    public static function doAsSuperUser($function)
    {
        $isSuperUser = self::getInstance()->hasSuperUserAccess();

        if ($isSuperUser) {
            return $function();
        }

        $access = self::getInstance();
        $login = $access->getLogin();
        $shouldResetLogin = empty($login); // make sure to reset login if a login was set by "makeSureLoginNameIsSet()"
        $access->setSuperUserAccess(true);

        try {
            $result = $function();
        } catch (\Throwable $ex) {
            $access->setSuperUserAccess($isSuperUser);
            if ($shouldResetLogin) {
                $access->login = null;
            }

            throw $ex;
        }

        if ($shouldResetLogin) {
            $access->login = null;
        }
        $access->setSuperUserAccess($isSuperUser);

        return $result;
    }

    /**
     * Returns the level of access the current user has to the given site.
     *
     * @param int $idSite The site to check.
     * @return string The access level, eg, 'view', 'write', 'admin', 'noaccess'.
     */
    public function getRoleForSite($idSite)
    {
        if ($this->hasSuperUserAccess) {
            return 'admin';
        }

        // A superuser token capped to a lower role holds that role on every site, so the answer does not
        // depend on which sites exist. Returning it directly keeps a single-site check from enumerating the
        // whole site table, which matters on the tracker authentication path where this runs per request.
        if ($this->isSuperUserRestrictedByTokenAccessLevel()) {
            return $this->clampRoleByTokenAccessLevel('superuser');
        }

        if (in_array($idSite, $this->getSitesIdWithAdminAccess())) {
            return 'admin';
        }

        if (in_array($idSite, $this->getSitesIdWithWriteAccess())) {
            return 'write';
        }

        if (in_array($idSite, $this->getSitesIdWithViewAccess())) {
            return 'view';
        }

        return 'noaccess';
    }

    /**
     * Returns the capabilities the current user has for a given site.
     *
     * @param int $idSite The site to check.
     * @return string[] The capabilities the user has.
     */
    public function getCapabilitiesForSite($idSite)
    {
        $this->loadSitesIfNeeded();

        $result = [];
        foreach ($this->capabilityProvider->getAllCapabilityIds() as $capabilityId) {
            if (empty($this->idsitesByAccess[$capabilityId])) {
                continue;
            }

            if (in_array($idSite, $this->idsitesByAccess[$capabilityId])) {
                $result[] = $capabilityId;
            }
        }
        return $result;
    }

    /**
     * Throw a NoAccessException with the given message, or a more generic 'You need to log in' message if the
     * user is not currently logged in (e.g. if session has expired).
     *
     * @param string $message
     * @throws NoAccessException
     */
    private function throwNoAccessException($message)
    {
        if (Piwik::isUserIsAnonymous() && !Request::isRootRequestApiRequest()) {
            $message = Piwik::translate('General_YouMustBeLoggedIn');
            if ($this->sessionExpired) {
                $message = Piwik::translate('General_YourSessionHasExpired');
            }
        }

        throw new NoAccessException($message);
    }

    public function setSessionExpired(bool $sessionExpired): void
    {
        $this->sessionExpired = $sessionExpired;
    }

    public function wasSessionExpired(): bool
    {
        return $this->sessionExpired;
    }

    /**
     * Returns true if the current user is logged in or not.
     *
     * @return bool
     */
    public function isUserLoggedIn()
    {
        return !empty($this->login);
    }
}
