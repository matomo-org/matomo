<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId\Reports;

use Piwik\Piwik;
use Piwik\Plugin\Report;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\UserId\Visualizations\UserIds;
use Piwik\View;

/**
 * A report showing all unique user IDs and some aggregated information about them. It also allows
 * to open a popover with visitor details
 */
class GetUsers extends Base
{
    /**
     * @return array
     */
    public static function getColumnsToDisplay()
    {
        return array(
            'label', 'nb_visits', 'nb_actions', 'nb_visits_converted'
        );
    }

    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('UsersManager_MenuUsers');
        $this->menuTitle     = $this->name;
        $this->documentation = '';

        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 1;
    }

    /**
     * @param ViewDataTable $view
     */
    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslation('label', Piwik::translate('General_UserId'));
        $view->config->addTranslation('nb_visits_converted', Piwik::translate('General_VisitConvertedGoal'));

        /*
         * Hide most of the table footer actions, leaving only export icons and pagination
         */
        $view->config->columns_to_display = $this->getColumnsToDisplay();
        $view->config->show_all_views_icons = false;
        $view->config->show_active_view_icon = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_export_as_rss_feed = false;
        $view->config->show_related_reports = false;
        $view->config->show_insights = false;
        $view->config->show_pivot_by_subtable = false;
        $view->config->show_flatten_table = false;
        $view->config->show_table = false;
        $view->config->show_table_all_columns = false;
        $view->config->disable_row_evolution = true;
    }

    public function getDefaultTypeViewDataTable()
    {
        return UserIds::ID;
    }

    public function alwaysUseDefaultViewDataTable()
    {
        return true;
    }
}
