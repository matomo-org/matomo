<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Dashboard;

use Piwik\API\Request;
use Piwik\Piwik;

/**
 * This API is the <a href='https://matomo.org/docs/analytics-api/reference/' rel='noreferrer' target='_blank'>Dashboard API</a>: it gives information about dashboards.
 *
 * @method static \Piwik\Plugins\Dashboard\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    private $dashboard = null;
    private $model     = null;

    public function __construct(Dashboard $dashboard, Model $model)
    {
        $this->dashboard = $dashboard;
        $this->model     = $model;
    }

    /**
     * Returns the dashboards available to a user, including the widgets in each dashboard.
     *
     * @param string $login Login of the user to load dashboards for. Defaults to the current user.
     * @param bool $returnDefaultIfEmpty Whether to return the default dashboard when the user has none.
     * @return array<int, array{name:string, id:int, widgets:array<int, array{module:string, action:string}>}> Dashboards for the requested user.
     */
    public function getDashboards($login = '', $returnDefaultIfEmpty = true)
    {
        $login = $login ? $login : Piwik::getCurrentUserLogin();

        $dashboards = [];

        if (!Piwik::isUserIsAnonymous()) {
            Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);
            $dashboards = $this->getUserDashboards($login);
        }

        if (empty($dashboards) && $returnDefaultIfEmpty) {
            $dashboards = array($this->getDefaultDashboard());
        }

        return $dashboards;
    }


    /**
     * Creates a new dashboard for a user.
     *
     * @param string $login Login of the user the dashboard should be created for.
     * @param string $dashboardName Name of the new dashboard.
     * @param bool $addDefaultWidgets Whether to populate the dashboard with the default widget layout.
     * @return int|string ID of the newly created dashboard.
     */
    public function createNewDashboardForUser($login, $dashboardName = '', $addDefaultWidgets = true)
    {
        $this->checkLoginIsNotAnonymous($login);
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $layout = '{}';

        if ($addDefaultWidgets) {
            $layout = $this->dashboard->getDefaultLayout();
        }

        return $this->model->createNewDashboardForUser($login, $dashboardName, $layout);
    }

    /**
     * Removes a dashboard for a user.
     *
     * @param int $idDashboard ID of the dashboard to remove.
     * @param string $login Login of the dashboard owner. Defaults to the current user.
     * @return void
     */
    public function removeDashboard($idDashboard, $login = '')
    {
        $login = $login ? $login : Piwik::getCurrentUserLogin();

        $this->checkLoginIsNotAnonymous($login);
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $this->model->deleteDashboardForUser($idDashboard, $login);
    }

    /**
     * Copies one of the current user's dashboards to another user.
     *
     * @param int $idDashboard ID of the dashboard to copy.
     * @param string $copyToUser Login of the user that should receive the dashboard copy.
     * @param string $dashboardName Name of the copied dashboard.
     * @return int|string ID of the new dashboard.
     */
    public function copyDashboardToUser($idDashboard, $copyToUser, $dashboardName = '')
    {
        Piwik::checkUserHasSomeAdminAccess();

        // get users only returns users of sites the current user has at least admin access to
        $users = Request::processRequest('UsersManager.getUsers', ['filter_limit' => -1]);
        $userFound = false;
        foreach ($users as $user) {
            if ($user['login'] === $copyToUser) {
                $userFound = true;
                break;
            }
        }

        if (!$userFound) {
            throw new \Exception(sprintf('Cannot copy dashboard to user %s, user not found.', $copyToUser));
        }

        $login  = Piwik::getCurrentUserLogin();
        $layout = $this->dashboard->getLayoutForUser($login, $idDashboard);

        if ($layout !== false) {
            return $this->model->createNewDashboardForUser($copyToUser, $dashboardName, $layout);
        }

        throw new \Exception('Dashboard not found');
    }

    /**
     * Resets a dashboard to the default widget layout.
     *
     * @param int $idDashboard ID of the dashboard to reset.
     * @param string $login Login of the dashboard owner. Defaults to the current user.
     * @return void
     */
    public function resetDashboardLayout($idDashboard, $login = '')
    {
        $login = $login ?: Piwik::getCurrentUserLogin();

        $this->checkLoginIsNotAnonymous($login);
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $layout = $this->dashboard->getDefaultLayout();

        $this->model->updateLayoutForUser($login, $idDashboard, $layout);
    }

    /**
     * @return array{name:string, id:int, widgets:array<int, array{module:string, action:string}>}
     */
    private function getDefaultDashboard()
    {
        $defaultLayout = $this->dashboard->getDefaultLayout();
        $defaultLayout = $this->dashboard->decodeLayout($defaultLayout);
        $defaultDashboard = array('name' => Piwik::translate('Dashboard_Dashboard'), 'layout' => $defaultLayout, 'iddashboard' => 1);

        $widgets = $this->getVisibleWidgetsWithinDashboard($defaultDashboard);

        return $this->buildDashboard($defaultDashboard, $widgets);
    }

    /**
     * @param string $userLogin
     * @return array<int, array{name:string, id:int, widgets:array<int, array{module:string, action:string}>}>
     */
    private function getUserDashboards($userLogin)
    {
        $userDashboards = $this->dashboard->getAllDashboards($userLogin);

        $dashboards = array();

        foreach ($userDashboards as $userDashboard) {
            $widgets = $this->getVisibleWidgetsWithinDashboard($userDashboard);
            $dashboards[] = $this->buildDashboard($userDashboard, $widgets);
        }

        return $dashboards;
    }

    /**
     * @param array<string, mixed> $dashboard
     * @return array<int, array{module:string, action:string}>
     */
    private function getVisibleWidgetsWithinDashboard($dashboard)
    {
        $columns = $this->getColumnsFromDashboard($dashboard);

        $widgets = array();
        $columns = array_filter($columns);

        foreach ($columns as $column) {
            foreach ($column as $widget) {
                if ($this->widgetIsNotHidden($widget) && !empty($widget->parameters->module)) {
                    $module = $widget->parameters->module;
                    $action = $widget->parameters->action;

                    $widgets[] = array('module' => $module, 'action' => $action);
                }
            }
        }

        return $widgets;
    }

    /**
     * @param string $login
     */
    private function checkLoginIsNotAnonymous($login)
    {
        Piwik::checkUserIsNotAnonymous();

        if ($login === 'anonymous') {
            throw new \Exception('This method can\'t be performed for anonymous user');
        }
    }

    /**
     * @param array<string, mixed> $dashboard
     * @return array<int, mixed>
     */
    private function getColumnsFromDashboard($dashboard)
    {
        if (empty($dashboard['layout'])) {
            return array();
        }

        if (is_array($dashboard['layout'])) {
            return $dashboard['layout'];
        }

        if (!empty($dashboard['layout']->columns)) {
            return $dashboard['layout']->columns;
        }

        return array();
    }

    /**
     * @param array<string, mixed> $dashboard
     * @param array<int, array{module:string, action:string}> $widgets
     * @return array{name:string, id:int, widgets:array<int, array{module:string, action:string}>}
     */
    private function buildDashboard($dashboard, $widgets)
    {
        return array('name' => $dashboard['name'], 'id' => $dashboard['iddashboard'], 'widgets' => $widgets);
    }

    /**
     * @param object $widget
     */
    private function widgetIsNotHidden($widget)
    {
        return empty($widget->isHidden);
    }
}
