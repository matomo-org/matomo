<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager;

use Piwik\Auth\Password;
use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\Sql\SiteAccessFilter;
use Piwik\Plugins\UsersManager\Sql\UserTableFilter;
use Piwik\SettingsPiwik;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\CharacterLength;
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
 * See also the documentation about <a href='http://piwik.org/docs/manage-users/' rel='noreferrer' target='_blank'>Managing Users</a> in Piwik.
 */
class Model
{
    public const MAX_LENGTH_TOKEN_DESCRIPTION = 100;
    public const TOKEN_HASH_ALGO = 'sha512';

    private static $rawPrefix = 'user';
    private $userTable;
    private $tokenTable;

    /**
     * @var Password
     */
    private $passwordHelper;

    public function __construct()
    {
        $this->passwordHelper = new Password();
        $this->userTable = Common::prefixTable(self::$rawPrefix);
        $this->tokenTable = Common::prefixTable('user_token_auth');
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
        $bind = array();

        if (!empty($userLogins)) {
            $where = 'WHERE login IN (' . Common::getSqlStringFieldsArray($userLogins) . ')';
            $bind = $userLogins;
        }

        $db = $this->getDb();
        $users = $db->fetchAll("SELECT * FROM " . $this->userTable . "
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
        $users = $db->fetchAll("SELECT login FROM " . $this->userTable . " ORDER BY login ASC");

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
        $users = $db->fetchAll("SELECT login FROM " . Common::prefixTable("access")
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
        $accessTable = Common::prefixTable('access');
        $siteTable = Common::prefixTable('site');

        $sql = sprintf("SELECT access.idsite, access.access 
    FROM %s access 
    LEFT JOIN %s site 
    ON access.idsite=site.idsite
     WHERE access.login = ? and site.idsite is not null", $accessTable, $siteTable);
        $db = $this->getDb();
        $users = $db->fetchAll($sql, $userLogin);
        $return = array();
        foreach ($users as $user) {
            $return[] = array(
              'site'   => $user['idsite'],
              'access' => $user['access'],
            );
        }
        return $return;
    }

    public function getSitesAccessFromUserWithFilters(
        $userLogin,
        $limit = null,
        $offset = 0,
        $pattern = null,
        $access = null,
        $idSites = null
    ) {
        $siteAccessFilter = new SiteAccessFilter($userLogin, $pattern, $access, $idSites);

        list($joins, $bind) = $siteAccessFilter->getJoins('a');

        list($where, $whereBind) = $siteAccessFilter->getWhere();
        $bind = array_merge($bind, $whereBind);

        $limitSql = '';
        $offsetSql = '';
        if ($limit) {
            $limitSql = "LIMIT " . (int)$limit;

            if ($offset) {
                $offsetSql = "OFFSET " . (int)$offset;
            }
        }

        $selector = "a.access";
        if ($access) {
            $selector = 'b.access';
            $joins .= " LEFT JOIN " . Common::prefixTable('access') . " b on a.idsite = b.idsite AND a.login = b.login";
        }

        $sql = 'SELECT s.idsite as idsite, s.name as site_name, GROUP_CONCAT(' . $selector . ' SEPARATOR "|") as access
                  FROM ' . Common::prefixTable('access') . " a
                $joins
                $where
              GROUP BY s.idsite
              ORDER BY s.name ASC, s.idsite ASC
              $limitSql $offsetSql";
        $db = $this->getDb();

        $access = $db->fetchAll($sql, $bind);
        foreach ($access as &$entry) {
            $entry['access'] = explode('|', $entry['access'] ?? '');
        }

        $sql = 'SELECT COUNT(DISTINCT s.idsite)
                 FROM ' . Common::prefixTable('access') . " a
                $joins
                $where";

        $count = $db->fetchOne($sql, $bind);

        return [$access, $count];
    }

    public function getIdSitesAccessMatching($userLogin, $filter_search = null, $filter_access = null, $idSites = null)
    {
        $siteAccessFilter = new SiteAccessFilter($userLogin, $filter_search, $filter_access, $idSites);

        list($joins, $bind) = $siteAccessFilter->getJoins('a');

        list($where, $whereBind) = $siteAccessFilter->getWhere();
        $bind = array_merge($bind, $whereBind);

        $sql = 'SELECT s.idsite FROM ' . Common::prefixTable('access') . " a $joins $where";

        $db = $this->getDb();

        $sites = $db->fetchAll($sql, $bind);
        $sites = array_column($sites, 'idsite');
        return $sites;
    }

    public function getUser($userLogin)
    {
        $db = $this->getDb();


        $matchedUsers = $db->fetchAll("SELECT * FROM {$this->userTable} WHERE login = ?", $userLogin);

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

    public function hashTokenAuth($tokenAuth)
    {
        $salt = SettingsPiwik::getSalt();
        return hash(self::TOKEN_HASH_ALGO, $tokenAuth . $salt);
    }

    public function generateRandomInviteToken(): string
    {
        $count = 0;

        do {
            $token = $this->generateTokenAuth();

            $count++;
            if ($count > 20) {
                // something seems wrong as the odds of that happening is basically 0. Only catching it to prevent
                // endless loop in case there is some bug somewhere
                throw new \Exception('Failed to generate token');
            }
        } while ($this->getUserByInviteToken($token));

        return $token;
    }

    public function generateRandomTokenAuth()
    {
        $count = 0;

        do {
            $token = $this->generateTokenAuth();

            $count++;
            if ($count > 20) {
                // something seems wrong as the odds of that happening is basically 0. Only catching it to prevent
                // endless loop in case there is some bug somewhere
                throw new \Exception('Failed to generate token');
            }
        } while ($this->getUserByTokenAuth($token));

        return $token;
    }

    private function generateTokenAuth()
    {
        return md5(Common::getRandomString(
            32,
            'abcdef1234567890'
        ) . microtime(true) . Common::generateUniqId() . SettingsPiwik::getSalt());
    }

    /**
     * Add a new token auth record to the database
     *
     * @param       $login
     * @param       $tokenAuth
     * @param       $description
     * @param       $dateCreated
     * @param null  $dateExpired
     * @param false $isSystemToken
     * @param bool  $secureOnly     True if this token can only be used in a secure way (e.g. POST requests), default false
     *
     * @return int                  Primary key of the new token auth
     * @throws \Piwik\Tracker\Db\DbException
     */
    public function addTokenAuth(
        $login,
        $tokenAuth,
        $description,
        $dateCreated,
        $dateExpired = null,
        $isSystemToken = false,
        bool $secureOnly = false
    ) {
        if (!$this->getUser($login)) {
            throw new \Exception('User ' . $login . ' does not exist');
        }

        BaseValidator::check(
            'Description',
            $description,
            [new NotEmpty(), new CharacterLength(1, self::MAX_LENGTH_TOKEN_DESCRIPTION)]
        );

        if (empty($dateExpired)) {
            $dateExpired = null;
        }

        $isSystemToken = (int)$isSystemToken;

        $insertSql = "INSERT INTO " . $this->tokenTable . ' (login, description, password, date_created, date_expired, system_token, hash_algo, secure_only) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

        $tokenAuth = $this->hashTokenAuth($tokenAuth);

        $db = $this->getDb();
        $db->query(
            $insertSql,
            [$login, $description, $tokenAuth, $dateCreated, $dateExpired, $isSystemToken, self::TOKEN_HASH_ALGO, (int) $secureOnly]
        );

        return $db->lastInsertId();
    }

    private function getTokenByTokenAuth($tokenAuth)
    {
        $tokenAuth = $this->hashTokenAuth($tokenAuth);
        $db = $this->getDb();

        return $db->fetchRow("SELECT * FROM " . $this->tokenTable . " WHERE `password` = ?", $tokenAuth);
    }

    public function getUserTokenDescriptionByIdTokenAuth($idTokenAuth, $login)
    {
        $db = $this->getDb();

        $token = $db->fetchRow(
            "SELECT description FROM " . $this->tokenTable . " WHERE `idusertokenauth` = ? and login = ? LIMIT 1",
            array($idTokenAuth, $login)
        );

        return $token ? $token['description'] : '';
    }

    private function getQueryNotExpiredToken()
    {
        return array(
          'sql'  => ' (date_expired is null or date_expired > ?)',
          'bind' => array(Date::now()->getDatetime())
        );
    }

    /**
     * Attempt to load a valid auth token
     *
     * @param string|null $tokenAuth    The token auth string
     * @param bool $isTokenSecured      True if the token was sent via a secure mechanism (POST request, Auth header)
     *
     * @return array|bool               An array representing the token record, or null if not found
     * @throws \Exception
     */
    private function getTokenByTokenAuthIfNotExpired(?string $tokenAuth, bool $isTokenSecured)
    {
        // If the token wasn't provided via a secure mechanism and use of secure tokens is enforced globally
        // then don't attempt to find the token
        if (GeneralConfig::getConfigValue('only_allow_secure_auth_tokens') && !$isTokenSecured) {
            return false;
        }

        $tokenAuth = $this->hashTokenAuth($tokenAuth);
        $db = $this->getDb();

        $expired = $this->getQueryNotExpiredToken();
        $bind = array_merge(array($tokenAuth), $expired['bind']);

        $sql = "SELECT * FROM " . $this->tokenTable . " WHERE `password` = ? AND " . $expired['sql'];

        // If the token was not send via a secure mechanism then exclude secure_only tokens
        if (!$isTokenSecured) {
            $sql .= " AND secure_only = 0";
        }

        $token = $db->fetchRow($sql, $bind);

        return $token;
    }

    public function deleteExpiredTokens($expiredSince)
    {
        $db = $this->getDb();

        return $db->query(
            "DELETE FROM " . $this->tokenTable . " WHERE `date_expired` is not null and date_expired < ?",
            $expiredSince
        );
    }

    public function getExpiredInvites($expiredSince)
    {
        $db = $this->getDb();

        return $db->fetchAll(
            "SELECT * FROM " . $this->userTable . " WHERE `invite_expired_at` is not null and invite_expired_at < ?",
            $expiredSince
        );
    }

    public function checkUserHasUnexpiredToken($login)
    {
        $db = $this->getDb();
        $expired = $this->getQueryNotExpiredToken();
        $bind = array_merge(array($login), $expired['bind']);
        return $db->fetchOne(
            "SELECT idusertokenauth FROM " . $this->tokenTable . " WHERE `login` = ? and " . $expired['sql'],
            $bind
        );
    }

    public function deleteAllTokensForUser($login)
    {
        $db = $this->getDb();

        return $db->query("DELETE FROM " . $this->tokenTable . " WHERE `login` = ?", $login);
    }

    public function getAllNonSystemTokensForLogin($login)
    {
        $db = $this->getDb();


        $expired = $this->getQueryNotExpiredToken();
        $bind = array_merge(array($login), $expired['bind']);

        return $db->fetchAll(
            "SELECT * FROM " . $this->tokenTable . " WHERE `login` = ? and system_token = 0 and " . $expired['sql'] . ' order by idusertokenauth ASC',
            $bind
        );
    }

    public function getAllHashedTokensForLogins($logins)
    {
        if (empty($logins)) {
            return array();
        }

        $db = $this->getDb();
        $placeholder = Common::getSqlStringFieldsArray($logins);

        $expired = $this->getQueryNotExpiredToken();
        $bind = array_merge($logins, $expired['bind']);

        $tokens = $db->fetchAll(
            "SELECT password FROM " . $this->tokenTable . " WHERE `login` IN (" . $placeholder . ") and " . $expired['sql'],
            $bind
        );
        return array_column($tokens, 'password');
    }

    public function deleteToken($idTokenAuth, $login)
    {
        $db = $this->getDb();

        return $db->query(
            "DELETE FROM " . $this->tokenTable . " WHERE `idusertokenauth` = ? and login = ?",
            array($idTokenAuth, $login)
        );
    }

    public function setTokenAuthWasUsed($tokenAuth, $dateLastUsed)
    {
        $token = $this->getTokenByTokenAuth($tokenAuth);
        if (!empty($token)) {
            $lastUsage = !empty($token['last_used']) ? strtotime($token['last_used']) : 0;
            $newUsage = strtotime($dateLastUsed);

            // update token usage only every 10 minutes to avoid table locks when multiple requests with the same token are made
            // see https://github.com/matomo-org/matomo/issues/16924
            if ($lastUsage > $newUsage - 600) {
                return;
            }

            $this->updateTokenAuthTable($token['idusertokenauth'], array(
              'last_used' => $dateLastUsed
            ));
        }
    }

    private function updateTokenAuthTable($idTokenAuth, $fields)
    {
        $set = array();
        $bind = array();
        foreach ($fields as $key => $val) {
            $set[] = "`$key` = ?";
            $bind[] = $val;
        }

        $bind[] = $idTokenAuth;

        $db = $this->getDb();
        $db->query(
            sprintf('UPDATE `%s` SET %s WHERE `idusertokenauth` = ?', $this->tokenTable, implode(', ', $set)),
            $bind
        );
    }

    public function getUserByEmail($userEmail)
    {
        $db = $this->getDb();
        return $db->fetchRow("SELECT * FROM " . $this->userTable . " WHERE email = ?", $userEmail);
    }


    public function getUserByInviteToken($tokenAuth)
    {
        $token = $this->hashTokenAuth($tokenAuth);
        if (!empty($token)) {
            $db = $this->getDb();
            return $db->fetchRow("SELECT * FROM " . $this->userTable . " WHERE `invite_token` = ? or `invite_link_token` = ?", [$token ,$token]);
        }
    }

    /**
     * Get an array of user data using the supplied token
     *
     * @param string|null   $tokenAuth
     *
     * @return array|null
     * @throws \Exception
     */
    public function getUserByTokenAuth(?string $tokenAuth): ?array
    {
        if ($tokenAuth === 'anonymous') {
            $row = $this->getUser('anonymous');
            return (is_array($row) ? $row : null);
        }

        $token = $this->getTokenByTokenAuthIfNotExpired($tokenAuth, \Piwik\API\Request::isTokenAuthProvidedSecurely());
        if (!empty($token)) {
            $db = $this->getDb();
            $row = $db->fetchRow("SELECT * FROM " . $this->userTable . " WHERE `login` = ?", $token['login']);
            return (is_array($row) ? $row : null);
        }

        return null;
    }

    /**
     * @param $userLogin
     * @param $hashedPassword
     * @param $email
     * @param $dateRegistered
     */
    public function addUser($userLogin, $hashedPassword, $email, $dateRegistered)
    {
        $user = array(
          'login'                => $userLogin,
          'password'             => $hashedPassword,
          'email'                => $email,
          'date_registered'      => $dateRegistered,
          'superuser_access'     => 0,
          'ts_password_modified' => Date::now()->getDatetime(),
          'idchange_last_viewed' => null,
          'invited_by'           => null,
        );

        $db = $this->getDb();
        $db->insert($this->userTable, $user);
    }

    public function attachInviteToken(string $userLogin, string $token, int $expiryInDays): void
    {
        $this->updateUserFields($userLogin, [
          'invite_token'      => $this->hashTokenAuth($token),
          'invite_expired_at' => Date::now()->addDay($expiryInDays)->getDatetime()
        ]);
    }

    public function attachInviteLinkToken(string $userLogin, string $token, int $expiryInDays): void
    {
        $this->updateUserFields($userLogin, [
            'invite_link_token' => $this->hashTokenAuth($token),
            'invite_expired_at' => Date::now()->addDay($expiryInDays)->getDatetime(),
        ]);
    }

    public function setSuperUserAccess($userLogin, $hasSuperUserAccess)
    {
        $this->updateUserFields($userLogin, array(
          'superuser_access' => $hasSuperUserAccess ? 1 : 0
        ));
    }

    public function updateUserFields($userLogin, $fields)
    {
        $set = array();
        $bind = array();

        foreach ($fields as $key => $val) {
            $set[] = "`$key` = ?";
            $bind[] = $val;
        }

        if (!empty($fields['password'])) {
            $set[] = "ts_password_modified = ?";
            $bind[] = Date::now()->getDatetime();
        }

        $bind[] = $userLogin;

        $db = $this->getDb();
        $db->query(sprintf('UPDATE `%s` SET %s WHERE `login` = ?', $this->userTable, implode(', ', $set)), $bind);
    }

    public function getUsersHavingSuperUserAccess()
    {
        $db = $this->getDb();
        $users = $db->fetchAll("SELECT login, email, superuser_access
                                FROM " . Common::prefixTable("user") . "
                                WHERE superuser_access = 1
                                ORDER BY date_registered ASC");

        return $users;
    }

    public function updateUser($userLogin, $hashedPassword, $email)
    {
        $fields = array(
          'email' => $email,
        );
        if (!empty($hashedPassword)) {
            $fields['password'] = $hashedPassword;
        }
        $this->updateUserFields($userLogin, $fields);
    }

    public function userExists($userLogin)
    {
        $db = $this->getDb();
        $count = $db->fetchOne("SELECT count(*) FROM " . $this->userTable . " WHERE login = ?", $userLogin);

        return $count != 0;
    }

    public function userEmailExists($userEmail)
    {
        $db = $this->getDb();
        $count = $db->fetchOne("SELECT count(*) FROM " . $this->userTable . " WHERE email = ?", $userEmail);

        return $count != 0;
    }

    public function removeUserAccess($userLogin, $access, $idSites)
    {
        $db = $this->getDb();

        $table = Common::prefixTable("access");

        foreach ($idSites as $idsite) {
            $bind = array($userLogin, $idsite, $access);
            $db->query("DELETE FROM " . $table . " WHERE login = ? and idsite = ? and access = ?", $bind);
        }
    }

    public function addUserAccess($userLogin, $access, $idSites)
    {
        $db = $this->getDb();

        $insertSql = "INSERT INTO " . Common::prefixTable("access") . ' (idsite, login, access) VALUES (?, ?, ?)';
        foreach ($idSites as $idsite) {
            $db->query($insertSql, [$idsite, $userLogin, $access]);
        }
    }

    public function deleteUser($userLogin): void
    {
        $this->deleteUserOnly($userLogin);
        $this->deleteUserOptions($userLogin);
        $this->deleteUserAccess($userLogin);
    }

    /**
     * @param string $userLogin
     */
    public function deleteUserOnly($userLogin)
    {
        $db = $this->getDb();
        $db->query("DELETE FROM " . $this->userTable . " WHERE login = ?", $userLogin);
        $db->query("DELETE FROM " . $this->tokenTable . " WHERE login = ?", $userLogin);

        /**
         * Triggered after a user has been deleted.
         *
         * This event should be used to clean up any data that is related to the now deleted user.
         * The **Dashboard** plugin, for example, uses this event to remove the user's dashboards.
         *
         * @param string $userLogins The login handle of the deleted user.
         */
        Piwik::postEvent('UsersManager.deleteUser', array($userLogin));
    }

    public function deleteUserOptions($userLogin)
    {
        Option::deleteLike('UsersManager.%.' . $userLogin);
    }

    /**
     * @param string $userLogin
     */
    public function deleteUserAccess($userLogin, $idSites = null)
    {
        $db = $this->getDb();

        if (is_null($idSites)) {
            $db->query("DELETE FROM " . Common::prefixTable("access") . " WHERE login = ?", $userLogin);
        } else {
            foreach ($idSites as $idsite) {
                $db->query(
                    "DELETE FROM " . Common::prefixTable("access") . " WHERE idsite = ? AND login = ?",
                    [$idsite, $userLogin]
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
     * @param string[]|null $logins the logins to limit the search to (if any)
     * @return array
     */
    public function getUsersWithRole(
        $idSite,
        $limit = null,
        $offset = null,
        $pattern = null,
        $access = null,
        $status = null,
        $logins = null
    ) {
        $filter = new UserTableFilter($access, $idSite, $pattern, $status, $logins);

        list($joins, $bind) = $filter->getJoins('u');
        list($where, $whereBind) = $filter->getWhere();

        $bind = array_merge($bind, $whereBind);

        $limitSql = '';
        $offsetSql = '';
        if ($limit) {
            $limitSql = "LIMIT " . (int)$limit;

            if ($offset) {
                $offsetSql = "OFFSET " . (int)$offset;
            }
        }

        $sql = 'SELECT u.*, GROUP_CONCAT(a.access SEPARATOR "|") as access
                  FROM ' . $this->userTable . " u
                $joins
                $where
              GROUP BY u.login
              ORDER BY u.login ASC
                 $limitSql $offsetSql";

        $db = $this->getDb();

        $users = $db->fetchAll($sql, $bind);
        foreach ($users as &$user) {
            $user['access'] = explode('|', $user['access'] ?? '');
        }

        $sql = 'SELECT COUNT(DISTINCT u.login)
                  FROM ' . $this->userTable . " u
                $joins
                $where";

        $count = $db->fetchOne($sql, $bind);

        return [$users, $count];
    }

    public function getSiteAccessCount($userLogin)
    {
        $sql = "SELECT COUNT(*) FROM " . Common::prefixTable('access') . " WHERE login = ?";
        $bind = [$userLogin];

        $db = $this->getDb();
        return $db->fetchOne($sql, $bind);
    }

    public function getUsersWithAccessToSites($idSites)
    {
        $idSites = array_map('intval', $idSites);

        $loginSql = 'SELECT DISTINCT ia.login FROM ' . Common::prefixTable('access') . ' ia WHERE ia.idsite IN ('
          . implode(',', $idSites) . ')';

        $logins = \Piwik\Db::fetchAll($loginSql);
        $logins = array_column($logins, 'login');
        return $logins;
    }

    public function isPendingUser(string $userLogin): bool
    {
        $db = $this->getDb();
        $sql = "SELECT count(*) FROM " . $this->userTable . " WHERE (login = ? or email = ?) and invite_token is not null";
        $bind = [$userLogin, $userLogin];
        $count = (int) $db->fetchOne($sql, $bind);
        return $count > 0;
    }
}
