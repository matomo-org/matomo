<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Authorization\Authorization;

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
 * This class is a Singleton proxy to \Piwik\Authorization\Authorization
 *
 * @deprecated Use \Piwik\Authorization\Authorization instead
 *
 */
class Access
{
    /**
     * @var Authorization
     */
    private static $instance;

    /**
     * Gets the singleton instance. Creates it if necessary.
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Authorization();

            Piwik::postEvent('Access.createAccessSingleton', array(&self::$instance));
        }
        return self::$instance;
    }

    /**
     * Sets the singleton instance. For testing purposes.
     */
    public static function setSingletonInstance($instance)
    {
        self::$instance = $instance;
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
        return self::$instance->getListAccess();
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
        return self::$instance->reloadAccess($auth);
    }

    public function getRawSitesWithSomeViewAccess($login)
    {
        return self::$instance->reloadAccess($login);
    }

    /**
     * Returns the SQL query joining sites and access table for a given login
     *
     * @param string $select Columns or expression to SELECT FROM table, eg. "MIN(ts_created)"
     * @return string  SQL query
     */
    public static function getSqlAccessSite($select)
    {
        return self::$instance->getSqlAccessSite($select);
    }

    /**
     * We bypass the normal auth method and give the current user Super User rights.
     * This should be very carefully used.
     *
     * @param bool $bool
     */
    public function setSuperUserAccess($bool = true)
    {
        self::$instance->setSuperUserAccess($bool);
    }

    /**
     * Returns true if the current user is logged in as the Super User
     *
     * @return bool
     */
    public function hasSuperUserAccess()
    {
        return self::$instance->hasSuperUserAccess();
    }

    /**
     * Returns the current user login
     *
     * @return string|null
     */
    public function getLogin()
    {
        return self::$instance->getLogin();
    }

    /**
     * Returns the token_auth used to authenticate this user in the API
     *
     * @return string|null
     */
    public function getTokenAuth()
    {
        return self::$instance->getTokenAuth();
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
        return self::$instance->getSitesIdWithAtLeastViewAccess();
    }

    /**
     * Returns an array of ID sites for which the user has an ADMIN access.
     *
     * @return array  Example if the user is ADMIN for 4 and 8
     *                and has VIEW access for 1 and 7, it returns array(4, 8);
     */
    public function getSitesIdWithAdminAccess()
    {
        return self::$instance->getSitesIdWithAdminAccess();
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
        return self::$instance->getSitesIdWithViewAccess();
    }

    /**
     * Throws an exception if the user is not the SuperUser
     *
     * @throws \Piwik\NoAccessException
     */
    public function checkUserHasSuperUserAccess()
    {
        self::$instance->checkUserHasSuperUserAccess();
    }

    /**
     * If the user doesn't have an ADMIN access for at least one website, throws an exception
     *
     * @throws \Piwik\NoAccessException
     */
    public function checkUserHasSomeAdminAccess()
    {
        self::$instance->checkUserHasSomeAdminAccess();
    }

    /**
     * If the user doesn't have any view permission, throw exception
     *
     * @throws \Piwik\NoAccessException
     */
    public function checkUserHasSomeViewAccess()
    {
        self::$instance->checkUserHasSomeViewAccess();
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
        self::$instance->checkUserHasAdminAccess($idSites);
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
        self::$instance->checkUserHasViewAccess($idSites);
    }
}

/**
 * Exception thrown when a user doesn't have sufficient access to a resource.
 *
 * @deprecated Use \Piwik\Authorization\NoAccessException instead
 * @api
 */
class NoAccessException extends \Exception
{
}
