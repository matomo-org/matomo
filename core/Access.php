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
use Piwik\API\Request;
use Piwik\Access\RolesProvider;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;
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
 * Access is granted per website. Uses with access for a website can view all
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
     * @var string
     */
    protected $login = null;

    /**
     * token_auth of the current user
     *
     * @var string
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
     * Authentification object (see Auth)
     *
     * @var Auth
     */
    private $auth = null;

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

    /**
     * Constructor
     */
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
        $this->idsitesByAccess = array(
            'view'      => array(),
            'write'     => array(),
            'admin'     => array(),
            'superuser' => array(),
        );
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

        $this->token_auth = null;
        $this->login = null;

        // if the Auth wasn't set, we may be in the special case of setSuperUser(), otherwise we fail TODO: docs + review
        if (!isset($this->auth)) {
            return false;
        }

        $result = null;

        $forceApiSessionPost = Common::getRequestVar('force_api_session', 0, 'int', $_POST);
        $forceApiSessionGet = Common::getRequestVar('force_api_session', 0, 'int', $_GET);
        $isApiRequest = Piwik::getModule() === 'API' && (Piwik::getAction() === 'index' || !Piwik::getAction());
        $apiMethod = Request::getMethodIfApiRequest(null);
        $isGetApiRequest = !empty($apiMethod) && 1 === substr_count($apiMethod, '.') && strpos($apiMethod, '.get') > 0;

        if (($forceApiSessionPost && $isApiRequest) || ($forceApiSessionGet && $isApiRequest && $isGetApiRequest)) {
            $request = ($forceApiSessionGet && $isApiRequest && $isGetApiRequest) ? $_GET : $_POST;
            $tokenAuth = Common::getRequestVar('token_auth', '', 'string', $request);
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

        // case the superUser is logged in
        if ($result->hasSuperUserAccess()) {
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

        return "SELECT " . $select . " FROM " . $access . " as t1
				JOIN " . $siteTable . " as t2 USING (idsite) WHERE login = ?";
    }

    /**
     * Make sure a login name is set
     *
     * @return true
     */
    protected function makeSureLoginNameIsSet()
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
            }
        }
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
     * Returns `true` if the current user has admin access to at least one site.
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
     * This method checks that the user has VIEW or ADMIN access for the given list of websites.
     * If the user doesn't have VIEW or ADMIN access for at least one website of the list, we throw an exception.
     *
     * @param int|array|string $idSites List of ID sites to check (integer, array of integers, string comma separated list of integers)
     * @throws \Piwik\NoAccessException  If for any of the websites the user doesn't have an VIEW or ADMIN access
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
     * @throws \Piwik\NoAccessException
     */
    protected function getIdSites($idSites)
    {
        if ($idSites === 'all') {
            $idSites = $this->getSitesIdWithAtLeastViewAccess();
        }

        $idSites = Site::getIdSitesFromIdSitesString($idSites);

        if (empty($idSites)) {
            $this->throwNoAccessException("The parameter 'idSite=' is missing from the request.");
        }

        return $idSites;
    }

    /**
     * Executes a callback with superuser privileges, making sure those privileges are rescinded
     * before this method exits. Privileges will be rescinded even if an exception is thrown.
     *
     * @param callback $function The callback to execute. Should accept no arguments.
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
     * @return string The access level, eg, 'view', 'admin', 'noaccess'.
     */
    public function getRoleForSite($idSite)
    {
        if (
            $this->hasSuperUserAccess
            || in_array($idSite, $this->getSitesIdWithAdminAccess())
        ) {
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
     * @param $message
     * @throws NoAccessException
     */
    private function throwNoAccessException($message)
    {
        if (Piwik::isUserIsAnonymous() && !Request::isRootRequestApiRequest()) {
            $message = Piwik::translate('General_YouMustBeLoggedIn');

            // Try to detect whether user was previously logged in so that we can display a different message
            $referrer = Url::getReferrer();
            $matomoUrl = SettingsPiwik::getPiwikUrl();
            if (
                $referrer && $matomoUrl && Url::isValidHost(Url::getHostFromUrl($referrer)) &&
                strpos($referrer, $matomoUrl) === 0
            ) {
                $message = Piwik::translate('General_YourSessionHasExpired');
            }
        }

        throw new NoAccessException($message);
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
