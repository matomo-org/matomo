<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;

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
 */
class Model
{
    /**
     * Returns the list of all the users
     *
     * @param string[] $userLogins List of users to select. If empty, will return all users
     * @return array the list of all the users
     */
    public function getUsers(array $userLogins)
    {
        $where = '';
        $bind  = array();

        if (!empty($userLogins)) {
            $where = 'WHERE login IN (' . Common::getSqlStringFieldsArray($userLogins) . ')';
            $bind = $userLogins;
        }

        $users = Db::get()->fetchAll("SELECT *
                                      FROM " . Common::prefixTable("user") . "
                                      $where
                                      ORDER BY login ASC", $bind);

        return $users;
    }

    /**
     * Returns the list of all the users login
     *
     * @return array the list of all the users login
     */
    public function getUsersLogin()
    {
        $users = Db::get()->fetchAll("SELECT login
                                      FROM " . Common::prefixTable("user") . "
                                      ORDER BY login ASC");
        $return = array();
        foreach ($users as $login) {
            $return[] = $login['login'];
        }
        
        return $return;
    }

    public function getUsersSitesFromAccess($access)
    {
        $users = Db::get()->fetchAll("SELECT login,idsite
                                      FROM " . Common::prefixTable("access")
                                  . " WHERE access = ?
                                      ORDER BY login, idsite", $access);

        $return = array();
        foreach ($users as $user) {
            $return[$user['login']][] = $user['idsite'];
        }

        return $return;
    }

    public function getUsersAccessFromSite($idSite)
    {
        $users = Db::get()->fetchAll("SELECT login,access
                                      FROM " . Common::prefixTable("access")
                                  . " WHERE idsite = ?", $idSite);

        $return = array();
        foreach ($users as $user) {
            $return[$user['login']] = $user['access'];
        }

        return $return;
    }

    public function getUsersLoginWithSiteAccess($idSite, $access)
    {
        $users = Db::get()->fetchAll("SELECT login
                                      FROM " . Common::prefixTable("access")
                                  . " WHERE idsite = ? AND access = ?", array($idSite, $access));

        $logins = array();
        foreach ($users as $user) {
            $logins[] = $user['login'];
        }

        return $logins;
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
        $users = Db::get()->fetchAll("SELECT idsite,access
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

    public function getUser($userLogin)
    {
        return Db::get()->fetchRow("SELECT *
                                    FROM " . Common::prefixTable("user")
                                . " WHERE login = ?", $userLogin);
    }

    public function getUserByEmail($userEmail)
    {
        return Db::get()->fetchRow("SELECT *
                                    FROM " . Common::prefixTable("user")
                                . " WHERE email = ?", $userEmail);
    }

    public function getUserByTokenAuth($tokenAuth)
    {
        return Db::get()->fetchRow('SELECT *
                                    FROM ' . Common::prefixTable('user') . '
					                WHERE token_auth = ?', $tokenAuth);
    }

    public function addUser($userLogin, $passwordTransformed, $email, $alias, $tokenAuth, $dateRegistered)
    {
        $user = array(
            'login'            => $userLogin,
            'password'         => $passwordTransformed,
            'alias'            => $alias,
            'email'            => $email,
            'token_auth'       => $tokenAuth,
            'date_registered'  => $dateRegistered,
            'superuser_access' => 0
        );

        Db::get()->insert(Common::prefixTable("user"), $user);
    }

    public function setSuperUserAccess($userLogin, $hasSuperUserAccess)
    {
        Db::get()->update(Common::prefixTable("user"),
            array(
                'superuser_access' => $hasSuperUserAccess ? 1 : 0
            ),
            "login = '$userLogin'"
        );
    }

    public function getUsersHavingSuperUserAccess()
    {
        $users = Db::get()->fetchAll("SELECT login, email
                                      FROM " . Common::prefixTable("user") . "
                                      WHERE superuser_access = 1
                                      ORDER BY date_registered ASC");

        return $users;
    }

    public function updateUser($userLogin, $password, $email, $alias, $tokenAuth)
    {
        Db::get()->update(Common::prefixTable("user"),
            array(
                 'password'   => $password,
                 'alias'      => $alias,
                 'email'      => $email,
                 'token_auth' => $tokenAuth
            ),
            "login = '$userLogin'"
        );
    }

    public function userExists($userLogin)
    {
        $count = Db::get()->fetchOne("SELECT count(*)
                                      FROM " . Common::prefixTable("user") . "
                                      WHERE login = ?", $userLogin);
        return $count != 0;
    }

    public function userEmailExists($userEmail)
    {
        $count = Db::get()->fetchOne("SELECT count(*)
                                      FROM " . Common::prefixTable("user") . "
                                      WHERE email = ?", $userEmail);
        return $count != 0;
    }

    public function addUserAccess($userLogin, $access, $idSites)
    {
        foreach ($idSites as $idsite) {
            Db::get()->insert(Common::prefixTable("access"),
                array("idsite" => $idsite,
                      "login"  => $userLogin,
                      "access" => $access)
            );
        }
    }

    public function deleteUserOnly($userLogin)
    {
        Db::get()->query("DELETE FROM " . Common::prefixTable("user") . " WHERE login = ?", $userLogin);

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

    public function deleteUserAccess($userLogin, $idSites = null)
    {
        if (is_null($idSites)) {
            Db::get()->query("DELETE FROM " . Common::prefixTable("access") .
                " WHERE login = ?",
                array($userLogin));
        } else {
            foreach ($idSites as $idsite) {
                Db::get()->query("DELETE FROM " . Common::prefixTable("access") .
                    " WHERE idsite = ? AND login = ?",
                    array($idsite, $userLogin)
                );
            }
        }
    }

}
