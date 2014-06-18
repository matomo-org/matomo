<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\UserSettings\Columns\Plugin;

class GetPlugin extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension     = new Plugin();
        $this->name          = Piwik::translate('UserSettings_WidgetPlugins');
        $this->documentation = Piwik::translate('UserSettings_WidgetPluginsDocumentation', '<br />');
        $this->metrics       = array('nb_visits', 'nb_visits_percentage');
        $this->constantRowsCount = true;
        $this->processedMetrics = array();
        $this->order = 4;
        $this->widgetTitle  = 'UserSettings_WidgetPlugins';
    }

    public function configureView(ViewDataTable $view)
    {
        $this->getBasicUserSettingsDisplayProperties($view);

        $view->config->addTranslations(array(
            'label'                => $this->dimension->getName(),
            'nb_visits_percentage' =>
            str_replace(' ', '&nbsp;', Piwik::translate('General_ColumnPercentageVisits'))
        ));

        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
        $view->config->show_all_views_icons    = false;
        $view->config->show_table_all_columns  = false;
        $view->config->columns_to_display  = array('label', 'nb_visits_percentage', 'nb_visits');
        $view->config->show_footer_message = Piwik::translate('UserSettings_PluginDetectionDoesNotWorkInIE');

        $view->requestConfig->filter_sort_column = 'nb_visits_percentage';
        $view->requestConfig->filter_sort_order  = 'desc';
        $view->requestConfig->filter_limit       = 10;
    }

}
