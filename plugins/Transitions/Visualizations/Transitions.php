<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Transitions\Visualizations;

use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugin\Visualization;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Plugins\Transitions\Controller;
use Piwik\View;

/**
 * A special DataTable visualization for the Live.getLastVisitsDetails API method.
 *
 */
class Transitions extends Visualization
{
    const ID = 'Transitions';
    const TEMPLATE_FILE = "@Transitions/getPageUrlTransitions.twig";
    const FOOTER_ICON_TITLE = '';
    const FOOTER_ICON = '';

    public function beforeLoadDataTable()
    {
        $this->requestConfig->flat = 1;
        $this->requestConfig->filter_sort_column = 'nb_visits';
        $this->requestConfig->filter_sort_order = 'desc';
    }


    /**
     * Configure visualization.
     */
    public function beforeRender()
    {
        $this->config->datatable_js_type = 'Transition';
        $this->config->enable_sort       = false;
        $this->config->show_search       = false;
        $this->config->show_exclude_low_population = false;
        $this->config->show_offset_information     = false;
        $this->config->show_all_views_icons        = false;
        $this->config->show_table_all_columns      = false;
        $this->config->show_export_as_rss_feed     = false;
        $this->config->disable_all_rows_filter_limit = true;
        $this->assignTemplateVar('translations', Controller::getTranslations());

        $pages = array();
        foreach ($this->dataTable->getRows() as $row) {
            $pages[] = array('key' => $row->getColumn('label'), 'value' => $row->getColumn('label'));
        }
        $this->assignTemplateVar('pages', $pages);

        $this->config->documentation = Piwik::translate('Live_VisitorLogDocumentation', array('<br />', '<br />'));

        $this->config->footer_icons = array();
    }

    public static function canDisplayViewDataTable(ViewDataTable $view)
    {
        return ($view->requestConfig->getApiModuleToRequest() === 'Transitions');
    }
}
