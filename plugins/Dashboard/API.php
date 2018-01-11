<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\Piwik;

/**
 * This API is the <a href='http://matomo.org/docs/analytics-api/reference/' rel='noreferrer' target='_blank'>Dashboard API</a>: it gives information about dashboards.
 *
 * @method static \Piwik\Plugins\Dashboard\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    private $dashboard = null;

    public function __construct(Dashboard $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    /**
     * Get each dashboard that belongs to a user including the containing widgets that are placed within each dashboard.
     * If the user has not created any dashboard yet, the default dashboard will be returned.
     *
     * @return array[]
     */
    public function getDashboards()
    {
        $dashboards = $this->getUserDashboards();

        if (empty($dashboards)) {
            $dashboards = array($this->getDefaultDashboard());
        }

        return $dashboards;
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
     * @return \array[]
     */
    private function getUserDashboards()
    {
        $userLogin = Piwik::getCurrentUserLogin();
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
