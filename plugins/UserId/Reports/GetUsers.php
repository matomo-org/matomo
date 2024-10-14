<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\UserId\Columns\UserId;
use Piwik\Url;

/**
 * A report showing all unique user IDs and some aggregated information about them. It also allows
 * to open a popover with visitor details
 */
class GetUsers extends Base
{
    protected function init()
    {
        parent::init();

        $this->name = Piwik::translate('UserId_UserReportTitle');
        $this->subcategoryId = 'UserId_UserReportTitle';
        $this->documentation = Piwik::translate('UserId_UserReportDocumentation');
        $this->dimension = new UserId();
        $this->metrics = array('label', 'nb_visits', 'nb_actions', 'nb_visits_converted');
        $this->supportsFlatten = false;

        // This defines in which order your report appears in the mobile app, in the menu and in the list of widgets
        $this->order = 9;
    }

    /**
     * @return array
     */
    public static function getColumnsToDisplay()
    {
        return array();
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
        $view->config->columns_to_display = $this->metrics;

        $view->config->show_all_views_icons = false;
        $view->config->show_related_reports = false;
        $view->config->show_insights = false;
        $view->config->show_pivot_by_subtable = false;
        $view->config->no_data_message = Piwik::translate('CoreHome_ThereIsNoDataForThisReport') . '<br><br>'
          . sprintf(
              Piwik::translate('UserId_ThereIsNoDataForThisReportHelp'),
              "<a target='_blank' rel='noreferrer noopener' href='" . Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/user-id/') . "'>",
              "</a>"
          );

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_row_evolution = false;
        }

        if ($view->isViewDataTableId(HtmlTable\AllColumns::ID)) {
            $view->config->filters[] = function () use ($view) {
                // unique visitors and user metrics doesn't make sense here as they would be always showing a value of 1
                $columnsToRemove = ['nb_uniq_visitors', 'nb_users'];
                $view->config->columns_to_display = array_diff($view->config->columns_to_display, $columnsToRemove);
            };
        }

        // exclude users with less then 2 visits, when low population filter is active
        $view->requestConfig->filter_excludelowpop_value = 2;
    }
}
