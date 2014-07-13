<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\Piwik;
use Piwik\WidgetsList;

/**
 * This API is the <a href='http://piwik.org/docs/analytics-api/reference/' target='_blank'>Dashboard API</a>: it gives information about dashboards.
 *
 * @method static \Piwik\Plugins\Dashboard\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    private $dashboard = null;

    protected function __construct()
    {
        $this->dashboard = new Dashboard();
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
     *
     * @return array[]
     */
    private function getDefaultDashboard()
    {
        $defaultLayout = $this->dashboard->getDefaultLayout();
        $defaultLayout = $this->dashboard->decodeLayout($defaultLayout);

        $defaultDashboard = array('name' => Piwik::translate('Dashboard_Dashboard'), 'layout' => $defaultLayout);

        $widgets = $this->getExistingWidgetsWithinDashboard($defaultDashboard);

        return $this->buildDashboard($defaultDashboard, $widgets);
    }

    /**
     * Get all dashboards which a user has created.
     *
     * @return array[]
     */
    private function getUserDashboards()
    {
        $userLogin = Piwik::getCurrentUserLogin();
        $userDashboards = $this->dashboard->getAllDashboards($userLogin);

        $dashboards = array();

        foreach ($userDashboards as $userDashboard) {

            if ($this->hasDashboardColumns($userDashboard)) {
                $widgets = $this->getExistingWidgetsWithinDashboard($userDashboard);
                $dashboards[] = $this->buildDashboard($userDashboard, $widgets);
            }
        }

        return $dashboards;
    }

    private function getExistingWidgetsWithinDashboard($dashboard)
    {
        $columns = $this->getColumnsFromDashboard($dashboard);

        $widgets = array();
        $columns = array_filter($columns);

        foreach ($columns as $column) {
            foreach ($column as $widget) {

                if ($this->widgetIsNotHidden($widget) && $this->widgetExists($widget)) {
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
        if (is_array($dashboard['layout'])) {

            return $dashboard['layout'];
        }

        return $dashboard['layout']->columns;
    }

    private function hasDashboardColumns($dashboard)
    {
        if (is_array($dashboard['layout'])) {

            return !empty($dashboard['layout']);
        }

        return !empty($dashboard['layout']->columns);
    }

    private function buildDashboard($dashboard, $widgets)
    {
        return array('name' => $dashboard['name'], 'widgets' => $widgets);
    }

    private function widgetExists($widget)
    {
        if (empty($widget->parameters)) {
            return false;
        }

        $module = $widget->parameters->module;
        $action = $widget->parameters->action;

        return WidgetsList::isDefined($module, $action);
    }

    private function widgetIsNotHidden($widget)
    {
        return empty($widget->isHidden);
    }
}
