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
 * The Dashboard API lets you manage user dashboards and retrieve their widget configurations.
 *
 * @method static \Piwik\Plugins\Dashboard\API getInstance()
 *
 * @phpstan-type DashboardWidget array{module: string, action: string}
 * @phpstan-type DashboardInfo array{name: string, id: int, widgets: list<DashboardWidget>}
 * @phpstan-type DashboardRecord array{name: string, iddashboard: int, layout: mixed}
 */
class API extends \Piwik\Plugin\API
{
    /**
     * @var Dashboard
     */
    private $dashboard;

    /**
     * @var Model
     */
    private $model;

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
     * @return list<DashboardInfo> List of dashboards, each containing name, id, and widgets keys.
     */
    public function getDashboards(string $login = '', bool $returnDefaultIfEmpty = true): array
    {
        $login = $login ?: Piwik::getCurrentUserLogin();

        $dashboards = [];

        if (!Piwik::isUserIsAnonymous()) {
            Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);
            $dashboards = $this->getUserDashboards($login);
        }

        if (empty($dashboards) && $returnDefaultIfEmpty) {
            $dashboards = [$this->getDefaultDashboard()];
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
    public function createNewDashboardForUser(string $login, string $dashboardName = '', bool $addDefaultWidgets = true)
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
     * Note: Deleting the first dashboard (ID = 1) is allowed but will cause buggy behavior
     * unless a new dashboard is immediately added. This should only be done for automation purposes.
     *
     * @param int $idDashboard ID of the dashboard to remove.
     * @param string $login Login of the dashboard owner. Defaults to the current user.
     */
    public function removeDashboard(int $idDashboard, string $login = ''): void
    {
        $login = $login ?: Piwik::getCurrentUserLogin();

        $this->checkLoginIsNotAnonymous($login);
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $this->model->deleteDashboardForUser($idDashboard, $login);
    }

    /**
     * Copies one of the current user's dashboards to another user. Requires admin access.
     *
     * @param int $idDashboard ID of the dashboard to copy.
     * @param string $copyToUser Login of the user that should receive the dashboard copy.
     * @param string $dashboardName Name for the copied dashboard.
     * @return int|string ID of the newly created dashboard copy.
     */
    public function copyDashboardToUser(int $idDashboard, string $copyToUser, string $dashboardName = '')
    {
        Piwik::checkUserHasSomeAdminAccess();

        // get users only returns users of sites the current user has at least admin access to
        /** @var array $users */
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
     */
    public function resetDashboardLayout(int $idDashboard, string $login = ''): void
    {
        $login = $login ?: Piwik::getCurrentUserLogin();

        $this->checkLoginIsNotAnonymous($login);
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $layout = $this->dashboard->getDefaultLayout();

        $this->model->updateLayoutForUser($login, $idDashboard, $layout);
    }

    /**
     * @return DashboardInfo
     */
    private function getDefaultDashboard(): array
    {
        $defaultLayout = $this->dashboard->getDefaultLayout();
        $defaultLayout = $this->dashboard->decodeLayout($defaultLayout);
        $defaultDashboard = ['name' => Piwik::translate('Dashboard_Dashboard'), 'layout' => $defaultLayout, 'iddashboard' => 1];

        $widgets = $this->getVisibleWidgetsWithinDashboard($defaultDashboard);

        return $this->buildDashboard($defaultDashboard, $widgets);
    }

    /**
     * @return list<DashboardInfo>
     */
    private function getUserDashboards(string $userLogin): array
    {
        $userDashboards = $this->dashboard->getAllDashboards($userLogin);

        $dashboards = [];

        foreach ($userDashboards as $userDashboard) {
            $widgets = $this->getVisibleWidgetsWithinDashboard($userDashboard);
            $dashboards[] = $this->buildDashboard($userDashboard, $widgets);
        }

        return $dashboards;
    }

    /**
     * @param DashboardRecord $dashboard
     * @return list<DashboardWidget>
     */
    private function getVisibleWidgetsWithinDashboard(array $dashboard): array
    {
        $columns = $this->getColumnsFromDashboard($dashboard);

        $widgets = [];
        $columns = array_filter($columns);

        foreach ($columns as $column) {
            foreach ($column as $widget) {
                if ($this->widgetIsNotHidden($widget) && !empty($widget->parameters->module)) {
                    $module = $widget->parameters->module;
                    $action = $widget->parameters->action;

                    $widgets[] = ['module' => $module, 'action' => $action];
                }
            }
        }

        return $widgets;
    }

    private function checkLoginIsNotAnonymous(string $login): void
    {
        Piwik::checkUserIsNotAnonymous();

        if ($login === 'anonymous') {
            throw new \Exception('This method can\'t be performed for anonymous user');
        }
    }

    /**
     * @param DashboardRecord $dashboard
     */
    private function getColumnsFromDashboard(array $dashboard): array
    {
        if (empty($dashboard['layout'])) {
            return [];
        }

        if (is_array($dashboard['layout'])) {
            return $dashboard['layout'];
        }

        if (!empty($dashboard['layout']->columns)) {
            return $dashboard['layout']->columns;
        }

        return [];
    }

    /**
     * @param DashboardRecord $dashboard
     * @param list<DashboardWidget> $widgets
     * @return DashboardInfo
     */
    private function buildDashboard(array $dashboard, array $widgets): array
    {
        return ['name' => $dashboard['name'], 'id' => (int)$dashboard['iddashboard'], 'widgets' => $widgets];
    }

    private function widgetIsNotHidden(object $widget): bool
    {
        return empty($widget->isHidden);
    }
}
