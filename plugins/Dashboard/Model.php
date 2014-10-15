<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;

class Model
{
    private static $rawPrefix = 'user_dashboard';
    private $table;

    public function __construct()
    {
        $this->table = Common::prefixTable(self::$rawPrefix);
    }

    /**
     * Returns the layout in the DB for the given user, or false if the layout has not been set yet.
     * Parameters must be checked BEFORE this function call
     *
     * @param string $login
     * @param int $idDashboard
     *
     * @return bool|string
     */
    public function getLayoutForUser($login, $idDashboard)
    {
        $query   = sprintf('SELECT layout FROM %s WHERE login = ? AND iddashboard = ?',
                           $this->table);
        $bind    = array($login, $idDashboard);
        $layouts = Db::fetchAll($query, $bind);

        return $layouts;
    }

    public function getAllDashboardsForUser($login)
    {
        $dashboards = Db::fetchAll('SELECT iddashboard, name, layout FROM ' . $this->table .
                                   ' WHERE login = ? ORDER BY iddashboard', array($login));

        return $dashboards;
    }

    public function deleteAllLayoutsForUser($userLogin)
    {
        Db::query('DELETE FROM ' . $this->table . ' WHERE login = ?', array($userLogin));
    }

    /**
     * Updates the name of a dashboard
     *
     * @param string $login
     * @param int $idDashboard
     * @param string $name
     */
    public function updateDashboardName($login, $idDashboard, $name)
    {
        $bind  = array($name, $login, $idDashboard);
        $query = sprintf('UPDATE %s SET name = ? WHERE login = ? AND iddashboard = ?', $this->table);
        Db::query($query, $bind);
    }

    /**
     * Removes the dashboard with the given id
     */
    public function deleteDashboardForUser($idDashboard, $login)
    {
        $query = sprintf('DELETE FROM %s WHERE iddashboard = ? AND login = ?', $this->table);
        Db::query($query, array($idDashboard, $login));
    }

    /**
     * Creates a new dashboard for the current user
     * User needs to be logged in
     */
    public function createNewDashboardForUser($login, $name, $layout)
    {
        $nextId = $this->getNextIdDashboard($login);

        $query = sprintf('INSERT INTO %s (login, iddashboard, name, layout) VALUES (?, ?, ?, ?)', $this->table);
        $bind  = array($login, $nextId, $name, $layout);
        Db::query($query, $bind);

        return $nextId;
    }

    /**
     * Saves the layout as default
     */
    public function createOrUpdateDashboard($login, $idDashboard, $layout)
    {
        $bind   = array($login, $idDashboard, $layout, $layout);
        $query  = sprintf('INSERT INTO %s (login, iddashboard, layout) VALUES (?,?,?) ON DUPLICATE KEY UPDATE layout=?',
                          $this->table);
        Db::query($query, $bind);
    }

    private function getNextIdDashboard($login)
    {
        $nextIdQuery = sprintf('SELECT MAX(iddashboard)+1 FROM %s WHERE login = ?', $this->table);
        $nextId      = Db::fetchOne($nextIdQuery, array($login));

        if (empty($nextId)) {
            $nextId = 1;
        }

        return $nextId;
    }

    /**
     * Records the layout in the DB for the given user.
     *
     * @param string $login
     * @param int $idDashboard
     * @param string $layout
     */
    public function updateLayoutForUser($login, $idDashboard, $layout)
    {
        $bind  = array($login, $idDashboard, $layout, $layout);
        $query = sprintf('INSERT INTO %s (login, iddashboard, layout) VALUES (?,?,?) ON DUPLICATE KEY UPDATE layout=?',
                         $this->table);
        Db::query($query, $bind);
    }

    public static function install()
    {
        $dashboard = "login VARCHAR( 100 ) NOT NULL ,
					  iddashboard INT NOT NULL ,
					  name VARCHAR( 100 ) NULL DEFAULT NULL ,
					  layout TEXT NOT NULL,
					  PRIMARY KEY ( login , iddashboard )";

        DbHelper::createTable(self::$rawPrefix, $dashboard);
    }

    public static function uninstall()
    {
        Db::dropTables(Common::prefixTable(self::$rawPrefix));
    }
}
