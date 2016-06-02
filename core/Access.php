<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\SitesManager\API as SitesManagerApi;

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
 *                          whatever he/she wants.
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
     * List of available permissions in Piwik
     *
     * @var array
     */
    private static $availableAccess = array('noaccess', 'view', 'admin', 'superuser');

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
     * Returns the list of the existing Access level.
     * Useful when a given API method requests a given acccess Level.
     * We first check that the required access level exists.
     *
     * @return array
     */
    public static function getListAccess()
    {
        return self::$availableAccess;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->resetSites();
    }

    private function resetSites()
    {
        $this->idsitesByAccess = array(
            'view'      => array(),
            'admin'     => array(),
            'superuser' => array()
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
    public function reloadAccess(Auth $auth = null)
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

        // access = array ( idsite => accessIdSite, idsite2 => accessIdSite2)
        $result = $this->auth->authenticate();

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
            if (empty($this->idsitesByAccess['view'])
                && empty($this->idsitesByAccess['admin'])) {

                // we join with site in case there are rows in access for an idsite that doesn't exist anymore
                // (backward compatibility ; before we deleted the site without deleting rows in _access table)
                $accessRaw = $this->getRawSitesWithSomeViewAccess($this->login);

                foreach ($accessRaw as $access) {
                    $this->idsitesByAccess[$access['access']][] = $access['idsite'];
                }
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
     * Which means VIEW or ADMIN or SUPERUSER.
     *
     * @return array  Example if the user is ADMIN for 4
     *                and has VIEW access for 1 and 7, it returns array(1, 4, 7);
     */
    public function getSitesIdWithAtLeastViewAccess()
    {
        $this->loadSitesIfNeeded();

        return array_unique(array_merge(
                $this->idsitesByAccess['view'],
                $this->idsitesByAccess['admin'],
                $this->idsitesByAccess['superuser'])
        );
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
                $this->idsitesByAccess['superuser'])
        );
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
     * Throws an exception if the user is not the SuperUser
     *
     * @throws \Piwik\NoAccessException
     */
    public function checkUserHasSuperUserAccess()
    {
        if (!$this->hasSuperUserAccess()) {
            throw new NoAccessException(Piwik::translate('General_ExceptionPrivilege', array("'superuser'")));
        }
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
     * If the user doesn't have an ADMIN access for at least one website, throws an exception
     *
     * @throws \Piwik\NoAccessException
     */
    public function checkUserHasSomeAdminAccess()
    {
        if (!$this->isUserHasSomeAdminAccess()) {
            throw new NoAccessException(Piwik::translate('General_ExceptionPrivilegeAtLeastOneWebsite', array('admin')));
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
            throw new NoAccessException(Piwik::translate('General_ExceptionPrivilegeAtLeastOneWebsite', array('view')));
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
                throw new NoAccessException(Piwik::translate('General_ExceptionPrivilegeAccessWebsite', array("'admin'", $idsite)));
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
                throw new NoAccessException(Piwik::translate('General_ExceptionPrivilegeAccessWebsite', array("'view'", $idsite)));
            }
        }
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
            throw new NoAccessException("The parameter 'idSite=' is missing from the request.");
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

        $access = self::getInstance();
        $access->setSuperUserAccess(true);

        try {
            $result = $function();
        } catch (Exception $ex) {
            $access->setSuperUserAccess($isSuperUser);

            throw $ex;
        }

        $access->setSuperUserAccess($isSuperUser);

        return $result;
    }
}

/**
 * Exception thrown when a user doesn't have sufficient access to a resource.
 *
 * @api
 */
class NoAccessException extends \Exception
{
}
