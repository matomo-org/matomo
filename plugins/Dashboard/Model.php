<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link     https://matomo.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Widget\WidgetsList;

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

    /**
     * Replaces widgets on the given dashboard layout with other ones
     *
     * It uses the given widget definitions to find the old and to create the new widgets
     * Each widget is defined with an array containing the following information
     * array (
     *      'module' => string
     *      'action' => string
     *      'params' => array()
     * )
     *
     * if $newWidget does not contain a widget definition at the current position,
     * the old widget will simply be removed
     *
     * @param array $oldWidgets array containing widget definitions
     * @param array $newWidgets array containing widget definitions
     */
    public static function replaceDashboardWidgets($dashboardLayout, $oldWidgets, $newWidgets)
    {
        if (empty($dashboardLayout) || !isset($dashboardLayout->columns)) {
            return $dashboardLayout;
        }

        $newColumns = array();

        foreach ($dashboardLayout->columns as $id => $column) {

            $newColumn = array();

            foreach ($column as $widget) {

                foreach ($oldWidgets AS $pos => $oldWidgetData) {

                    $oldWidgetId = WidgetsList::getWidgetUniqueId($oldWidgetData['module'], $oldWidgetData['action'], $oldWidgetData['params']);

                    if (empty($newWidgets[$pos])) {
                        continue 2;
                    }

                    $newWidget = $newWidgets[$pos];

                    if ($widget->uniqueId == $oldWidgetId) {

                        if (!empty($newWidget['uniqueId'])) {
                            $newWidgetId = $newWidget['uniqueId'];
                        } else {
                            $newWidgetId = WidgetsList::getWidgetUniqueId($newWidget['module'], $newWidget['action'], $newWidget['params']);
                        }

                        // is new widget already is on dashboard just remove the old one
                        if (self::layoutContainsWidget($dashboardLayout, $newWidgetId)) {
                            continue 2;
                        }

                        $widget->uniqueId = $newWidgetId;
                        $widget->parameters->module = $newWidget['module'];
                        $widget->parameters->action = $newWidget['action'];
                        foreach ($newWidget['params'] as $key => $value) {
                            $widget->parameters->{$key} = $value;
                        }
                    }
                }


                $newColumn[] = $widget;
            }

            $newColumns[] = $newColumn;
        }

        $dashboardLayout->columns = $newColumns;

        return $dashboardLayout;
    }

    /**
     * Checks if a given dashboard layout contains a given widget
     *
     * @param $dashboardLayout
     * @param $widgetId
     * @return bool
     */
    protected static function layoutContainsWidget($dashboardLayout, $widgetId)
    {
        if (!isset($dashboardLayout->columns)) {
            return false;
        }

        foreach ($dashboardLayout->columns as $id => $column) {

            foreach ($column as $widget) {

                if ($widget->uniqueId == $widgetId) {
                    return true;
                }
            }
        }

        return false;
    }
}
