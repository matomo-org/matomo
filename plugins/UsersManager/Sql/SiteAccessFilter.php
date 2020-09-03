<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UsersManager\Sql;


use Piwik\Common;

class SiteAccessFilter
{
    /**
     * @var string
     */
    private $filterByRole;

    /**
     * @var int
     */
    private $userLogin;

    /**
     * @var string
     */
    private $filterSearch;

    /**
     * List of sites to limit the search to.
     *
     * @var int[]|null
     */
    private $idSites;

    public function __construct($userLogin, $filterSearch, $filterByRole, $idSites)
    {
        if (empty($userLogin)) {
            throw new \InvalidArgumentException("filtering by role is only supported for a single site");
        }

        $this->userLogin = $userLogin;
        $this->filterSearch = $filterSearch;
        $this->filterByRole = $filterByRole;
        $this->idSites = empty($idSites) ? null : array_map('intval', $idSites);
    }

    public function getJoins($accessTable)
    {
        $result = "RIGHT JOIN ". Common::prefixTable('site') . " s ON s.idsite = $accessTable.idsite AND a.login = ?";
        $bind = [$this->userLogin];

        return [$result, $bind];
    }

    public function getWhere()
    {
        $bind = [];
        $result = [];

        if ($this->filterSearch) {
            $bind = array_merge($bind, \Piwik\Plugins\SitesManager\Model::getPatternMatchSqlBind($this->filterSearch));
            $result[] = \Piwik\Plugins\SitesManager\Model::getPatternMatchSqlQuery('s');
        }

        if ($this->filterByRole) {
            if ($this->filterByRole == 'noaccess') {
                $result[] = 'a.access IS NULL';
            } else if ($this->filterByRole == 'some') {
                $result[] = 'a.access IS NOT NULL';
            } else {
                $result[] = 'a.access = ?';
                $bind[] = $this->filterByRole;
            }
        }

        if (!empty($this->idSites)) {
            $result[] = 's.idsite IN (' . implode(',', $this->idSites) . ')';
        }

        if (!empty($result)) {
            $result = 'WHERE ' . implode(' AND ', $result);
        } else {
            $result = '';
        }

        return [$result, $bind];
    }
}