<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Piwik\Auth\Password;
use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\SitesManager;

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
class Model
{
    private static $rawPrefix = 'user';
    private $table;

    /**
     * @var Password
     */
    private $passwordHelper;

    public function __construct()
    {
        $this->passwordHelper = new Password();
        $this->table = Common::prefixTable(self::$rawPrefix);
    }

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
            $bind  = $userLogins;
        }

        $db = $this->getDb();
        $users = $db->fetchAll("SELECT * FROM " . $this->table . "
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
        $db = $this->getDb();
        $users = $db->fetchAll("SELECT login FROM " . $this->table . " ORDER BY login ASC");

        $return = array();
        foreach ($users as $login) {
            $return[] = $login['login'];
        }

        return $return;
    }

    public function getUsersSitesFromAccess($access)
    {
        $db = $this->getDb();
        $users = $db->fetchAll("SELECT login,idsite FROM " . Common::prefixTable("access")
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
        $db = $this->getDb();
        $users = $db->fetchAll("SELECT login,access FROM " . Common::prefixTable("access")
                             . " WHERE idsite = ?", $idSite);

        $return = array();
        foreach ($users as $user) {
            $return[$user['login']] = $user['access'];
        }

        return $return;
    }

    public function getUsersLoginWithSiteAccess($idSite, $access)
    {
        $db = $this->getDb();
        $users = $db->fetchAll("SELECT login
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
        $db = $this->getDb();
        $users = $db->fetchAll("SELECT idsite,access FROM " . Common::prefixTable("access")
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

    public function getSitesAccessFromUserWithFilters($userLogin, $limit = null, $offset = 0, $pattern = null, $access = null)
    {
        $bind = [$userLogin];
        $where = 'u.login = ?';

        if ($pattern) {
            $bind = array_merge($bind, \Piwik\Plugins\SitesManager\Model::getPatternMatchSqlBind($pattern));
            $where .= ' AND ' . \Piwik\Plugins\SitesManager\Model::getPatternMatchSqlQuery('s');
        }

        if ($access) {
            $where .= ' AND a.access = ?';
            $bind[] = $access;
        }

        $limitSql = '';
        if ($limit) {
            $limitSql = "LIMIT " . (int)$limit;
        }

        $offsetSql = '';
        if ($offset) {
            $offsetSql = "OFFSET " . (int)$offset;
        }

        $sql = 'SELECT a.idsite as idsite, s.name as site_name, IF(u.superuser_access = 1, "admin", a.access) as access
                  FROM ' . Common::prefixTable('access') . ' a
            INNER JOIN ' . $this->table . ' u ON u.login = a.login
            INNER JOIN ' . Common::prefixTable('site') . ' s ON s.idsite = a.idsite
                 WHERE ' . $where . '
              ORDER BY s.name ASC, a.idsite ASC '. "
              $limitSql $offsetSql";

        $db = $this->getDb();
        $access = $db->fetchAll($sql, $bind);
        return $access;
    }

    public function getUser($userLogin)
    {
        $db = $this->getDb();

        $matchedUsers = $db->fetchAll("SELECT * FROM " . $this->table . " WHERE login = ?", $userLogin);

        // for BC in 2.15 LTS, if there is a user w/ an exact match to the requested login, return that user.
        // this is done since before this change, login was case sensitive. until 3.0, we want to maintain
        // this behavior.
        foreach ($matchedUsers as $user) {
            if ($user['login'] == $userLogin) {
                return $user;
            }
        }

        return reset($matchedUsers);
    }

    public function getUserByEmail($userEmail)
    {
        $db = $this->getDb();
        return $db->fetchRow("SELECT * FROM " . $this->table . " WHERE email = ?", $userEmail);
    }

    public function getUserByTokenAuth($tokenAuth)
    {
        $db = $this->getDb();
        return $db->fetchRow('SELECT * FROM ' . $this->table . ' WHERE token_auth = ?', $tokenAuth);
    }

    public function addUser($userLogin, $hashedPassword, $email, $alias, $tokenAuth, $dateRegistered)
    {
        $user = array(
            'login'            => $userLogin,
            'password'         => $hashedPassword,
            'alias'            => $alias,
            'email'            => $email,
            'token_auth'       => $tokenAuth,
            'date_registered'  => $dateRegistered,
            'superuser_access' => 0
        );

        $db = $this->getDb();
        $db->insert($this->table, $user);
    }

    public function setSuperUserAccess($userLogin, $hasSuperUserAccess)
    {
        $this->updateUserFields($userLogin, array(
            'superuser_access' => $hasSuperUserAccess ? 1 : 0
        ));
    }

    private function updateUserFields($userLogin, $fields)
    {
        $set  = array();
        $bind = array();

        foreach ($fields as $key => $val) {
            $set[]  = "`$key` = ?";
            $bind[] = $val;
        }

        $bind[] = $userLogin;

        $db = $this->getDb();
        $db->query(sprintf('UPDATE `%s` SET %s WHERE `login` = ?', $this->table, implode(', ', $set)), $bind);
    }

    /**
     * Note that this returns the token_auth which is as private as the password!
     *
     * @return array[] containing login, email and token_auth
     */
    public function getUsersHavingSuperUserAccess()
    {
        $db = $this->getDb();
        $users = $db->fetchAll("SELECT login, email, token_auth
                                FROM " . Common::prefixTable("user") . "
                                WHERE superuser_access = 1
                                ORDER BY date_registered ASC");

        return $users;
    }

    public function updateUser($userLogin, $hashedPassword, $email, $alias, $tokenAuth)
    {
        $this->updateUserFields($userLogin, array(
            'password'   => $hashedPassword,
            'alias'      => $alias,
            'email'      => $email,
            'token_auth' => $tokenAuth
        ));
    }

    public function updateUserTokenAuth($userLogin, $tokenAuth)
    {
        $this->updateUserFields($userLogin, array(
            'token_auth' => $tokenAuth
        ));
    }

    public function userExists($userLogin)
    {
        $db = $this->getDb();
        $count = $db->fetchOne("SELECT count(*) FROM " . $this->table . " WHERE login = ?", $userLogin);

        return $count != 0;
    }

    public function userEmailExists($userEmail)
    {
        $db = $this->getDb();
        $count = $db->fetchOne("SELECT count(*) FROM " . $this->table . " WHERE email = ?", $userEmail);

        return $count != 0;
    }

    public function addUserAccess($userLogin, $access, $idSites)
    {
        $db = $this->getDb();

        foreach ($idSites as $idsite) {
            $db->insert(Common::prefixTable("access"),
                array("idsite" => $idsite,
                      "login"  => $userLogin,
                      "access" => $access)
            );
        }
    }

    public function deleteUserOnly($userLogin)
    {
        $db = $this->getDb();
        $db->query("DELETE FROM " . $this->table . " WHERE login = ?", $userLogin);

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
        $db = $this->getDb();

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

    private function getDb()
    {
        return Db::get();
    }

    /**
     * Returns all users and their access to `$idSite`.
     *
     * @param int $idSite
     * @param int|null $limit
     * @param int|null $offset
     * @param string|null $pattern text to search for if any
     * @param string|null $access 'noaccess','some','view','admin' or 'superuser'
     * @return array
     */
    public function getUsersWithRole($idSite, $limit = null, $offset = null, $pattern = null, $access = null)
    {
        $where = '(a.idsite IS NULL OR a.idsite IN (?))';
        $bind = [$idSite];

        if ($pattern) {
            $where .= ' AND (u.login LIKE ? OR u.alias LIKE ? OR u.email LIKE ?)';
            $bind = array_merge($bind, ['%' . $pattern . '%', '%' . $pattern . '%', '%' . $pattern . '%']);
        }

        if ($access) {
            list($accessSql, $accessBind) = $this->getAccessSelectSqlCondition($access);

            $where .= ' AND ' . $accessSql;
            $bind = array_merge($bind, $accessBind);
        }

        $limitSql = '';
        if ($limit) {
            $limitSql = "LIMIT " . (int)$limit;
        }

        $offsetSql = '';
        if ($offset) {
            $offsetSql = "OFFSET " . (int)$offset;
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS u.*, IF(u.superuser_access = 1, "admin", IF(u.superuser_access = 1, "superuser", IFNULL(a.access, "noaccess"))) as role
                  FROM ' . $this->table . " u
             LEFT JOIN " . Common::prefixTable('access') . " a ON u.login = a.login
                 WHERE $where
              ORDER BY u.login ASC
                 $limitSql $offsetSql";

        $db = $this->getDb();
        $users = $db->fetchAll($sql, $bind);
        $count = $db->fetchOne("SELECT FOUND_ROWS()");

        foreach ($users as &$user) {
            $user['superuser_access'] = $user['superuser_access'] == 1;
        }

        return [$users, $count];
    }

    private function getAccessSelectSqlCondition($access)
    {
        $sql = '';
        $bind = [];

        switch ($access) {
            case 'noaccess':
                $sql = "a.access IS NULL";
                break;
            case 'some':
                $sql = "(a.access IS NOT NULL OR u.superuser_access = 1)";
                break;
            case 'view':
            case 'admin':
                $sql = "a.access = ?";
                $bind[] = $access;
                break;
            case 'superuser':
                $sql = "u.superuser_access = 1";
                break;
        }

        return [$sql, $bind];
    }

}
