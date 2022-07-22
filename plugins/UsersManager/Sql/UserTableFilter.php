<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\Sql;

use Piwik\Common;
use Piwik\Piwik;

class UserTableFilter
{
    /**
     * @var string
     */
    private $filterByRole;

    /**
     * @var int
     */
    private $filterByRoleSite;

    /**
     * @var string
     */
    private $filterSearch;

    /**
     * @var string
     */
    private $filterStatus;

    /**
     * @var string[]
     */
    private $logins;

    public function __construct($filterByRole, $filterByRoleSite, $filterSearch, $filterStatus, $logins = null)
    {
        $this->filterByRole = $filterByRole;
        $this->filterByRoleSite = $filterByRoleSite;
        $this->filterSearch = $filterSearch;
        $this->filterStatus = $filterStatus;
        $this->logins = $logins;

        if (isset($this->filterByRole) && !isset($this->filterByRoleSite)) {
            throw new \InvalidArgumentException("filtering by role is only supported for a single site");
        }

        // can only filter by superuser if current user is a superuser
        if (
            $this->filterByRole == 'superuser'
            && !Piwik::hasUserSuperUserAccess()
        ) {
            $this->filterByRole = null;
        }
    }

    public function getJoins($userTable)
    {
        $result = "LEFT JOIN " . Common::prefixTable('access') . " a ON $userTable.login = a.login AND (a.idsite IS NULL OR a.idsite = ?)";
        $bind = [$this->filterByRoleSite];

        return [$result, $bind];
    }

    public function getWhere()
    {
        $conditions = [];
        $bind = [];

        if ($this->filterByRole) {
            list($filterByRoleSql, $filterByRoleBind) = $this->getAccessSelectSqlCondition();

            $conditions[] = $filterByRoleSql;
            $bind = array_merge($bind, $filterByRoleBind);
        }

        if ($this->filterSearch) {
            $conditions[] = '(u.login LIKE ? OR u.email LIKE ?)';
            $bind = array_merge($bind, ['%' . $this->filterSearch . '%', '%' . $this->filterSearch . '%']);
        }

        if ($this->filterStatus) {
            if ($this->filterStatus === 'active') {
                $conditions[] = '(u.invite_token is NULL and u.invite_expired_at is NULL)';
            }
            if ($this->filterStatus === 'pending') {
                $conditions[] = '(u.invite_token is not NULL and u.invite_expired_at > DATE(Now()))';
                // Pending users are only visible for super user or the user, who invited the user
                if (!Piwik::hasUserSuperUserAccess()) {
                    $conditions[] = 'u.invited_by = ?';
                    $bind[] = Piwik::getCurrentUserLogin();
                }
            }
            if ($this->filterStatus === 'expired') {
                $conditions[] = '(u.invite_token is not NULL and u.invite_expired_at < DATE(Now()))';
                // Expired users are only visible for super user or the user, who invited the user
                if (!Piwik::hasUserSuperUserAccess()) {
                    $conditions[] = 'u.invited_by = ?';
                    $bind[] = Piwik::getCurrentUserLogin();
                }
            }
        }

        if ($this->logins !== null) {
            $logins = array_map('json_encode', $this->logins);
            $conditions[] = 'u.login IN (' . implode(',', $logins) . ')';
        }

        $result = implode(' AND ', $conditions);
        if (!empty($result)) {
            $result = 'WHERE ' . $result;
        }

        return [$result, $bind];
    }

    private function getAccessSelectSqlCondition()
    {
        $bind = [];

        switch ($this->filterByRole) {
            case 'noaccess':
                $sql = "(a.access IS NULL AND u.superuser_access <> 1)";
                break;
            case 'some':
                $sql = "(a.access IS NOT NULL OR u.superuser_access = 1)";
                break;
            case 'superuser':
                $sql = "u.superuser_access = 1";
                break;
            default:
                $sql = "u.login IN (SELECT login from " . Common::prefixTable('access') . " WHERE access = ? AND (idsite IS NULL OR idsite = ?))";
                $bind[] = $this->filterByRole;
                $bind[] = $this->filterByRoleSite;
                break;
        }

        return [$sql, $bind];
    }
}
