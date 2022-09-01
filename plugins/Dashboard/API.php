<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link     https://matomo.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\API\Request;
use Piwik\Piwik;

/**
 * This API is the <a href='http://matomo.org/docs/analytics-api/reference/' rel='noreferrer' target='_blank'>Dashboard API</a>: it gives information about dashboards.
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
     * Get each dashboard that belongs to a user including the containing widgets that are placed within each dashboard.
     * If the user has not created any dashboard yet, the default dashboard will be returned unless
     * $returnDefaultIfEmpty is set to `false`
     *
     * @param string $login Login of the user [defaults to current user]
     * @param bool $returnDefaultIfEmpty  disable return of default dashboard
     *
     * @return array[]
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
     * Creates a new dashboard for the given login
     *
     * Note: Only a super user is able to create dashboards for other users
     *
     * @param string $login login of the user that dashboard should be created for
     * @param string $dashboardName name of the new dashboard
     * @param bool $addDefaultWidgets  whether to add the current default widget collection or not
     * @return int|string
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
     * Removes a dashboard according to given dashboard id and login
     *
     * Note: Only a super user is able to remove dashboards for other users
     *
     * Also note: It is allowed to delete the first dashboard for a user, BUT
     * that will cause buggy behavior if a new dashboard is not immediately added.
     * Deleting the first dashboard (with ID = 1) should only be done for automation
     * purposes.
     *
     * @param int $idDashboard id of the dashboard to be removed
     * @param string $login  Login of the dashboard user [defaults to current user]
     */
    public function removeDashboard($idDashboard, $login='')
    {
        $login = $login ? $login : Piwik::getCurrentUserLogin();

        $this->checkLoginIsNotAnonymous($login);
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $this->model->deleteDashboardForUser($idDashboard, $login);
    }

    /**
     * Copy a dashboard of current user to another user
     *
     * Note: current user needs super user access
     *
     * @param int $idDashboard Id of the dashboard that should be copied
     * @param string $copyToUser User the dashboard should be copied to
     * @param string $dashboardName Name of the new dashboard (defaults to 'Dashboard of {user}')
     * @return int id of the new dashboard
     * @throws \Exception if an error occurs, or dashboard can't be found
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
     * Resets a dashboard to the default widget configuration
     *
     * Note: Only a super user is able to reset dashboards for other users

     * @param int $idDashboard dashboard id
     * @param string $login user the dashboard belongs
     *
     */
    public function resetDashboardLayout($idDashboard, $login='')
    {
        $login = $login ?: Piwik::getCurrentUserLogin();

        $this->checkLoginIsNotAnonymous($login);
        Piwik::checkUserHasSuperUserAccessOrIsTheUser($login);

        $layout = $this->dashboard->getDefaultLayout();

        $this->model->updateLayoutForUser($login, $idDashboard, $layout);
    }

    /**
     * Get the default dashboard.
     * @return \array[]
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
     * Get all dashboards which a user has created.
     *
     * @param string $userLogin login of the user
     * @return \array[]
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

    private function checkLoginIsNotAnonymous($login)
    {
        Piwik::checkUserIsNotAnonymous();

        if ($login === 'anonymous') {
            throw new \Exception('This method can\'t be performed for anonymous user');
        }
    }

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

    private function buildDashboard($dashboard, $widgets)
    {
        return array('name' => $dashboard['name'], 'id' => $dashboard['iddashboard'], 'widgets' => $widgets);
    }

    private function widgetIsNotHidden($widget)
    {
        return empty($widget->isHidden);
    }
}
