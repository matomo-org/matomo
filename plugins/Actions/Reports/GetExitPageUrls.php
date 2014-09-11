<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\API\Request;
use Piwik\Plugins\Actions\Columns\ExitPageUrl;

class GetExitPageUrls extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new ExitPageUrl();
        $this->name          = Piwik::translate('Actions_SubmenuPagesExit');
        $this->documentation = Piwik::translate('Actions_ExitPagesReportDocumentation', '<br />')
                             . '<br />' . Piwik::translate('General_UsePlusMinusIconsDocumentation');

        $this->metrics = array('exit_nb_visits', 'nb_visits', 'exit_rate');
        $this->actionToLoadSubTables = $this->action;

        $this->order = 4;

        $this->menuTitle   = 'Actions_SubmenuPagesExit';
        $this->widgetTitle = 'Actions_WidgetPagesExit';
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();
        $metrics['nb_visits'] = Piwik::translate('General_ColumnUniquePageviews');

        return $metrics;
    }

    protected function getMetricsDocumentation()
    {
        $metrics = parent::getMetricsDocumentation();
        $metrics['nb_visits'] = Piwik::translate('General_ColumnUniquePageviewsDocumentation');

        return $metrics;
    }

    public function configureView(ViewDataTable $view)
    {
        // link to the page, not just the report, but only if not a widget
        $widget = Common::getRequestVar('widget', false);

        $view->config->self_url = Request::getCurrentUrlWithoutGenericFilters(array(
            'module' => 'Actions',
            'action' => $widget === false ? 'indexExitPageUrls' : 'getExitPageUrls'
        ));

        $view->config->addTranslations(array('label' => $this->dimension->getName()));

        $view->config->title = $this->name;

        $view->config->columns_to_display        = array('label', 'exit_nb_visits', 'nb_visits', 'exit_rate');
        $view->requestConfig->filter_sort_column = 'exit_nb_visits';
        $view->requestConfig->filter_sort_order  = 'desc';

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function getRelatedReports()
    {
        return array(
            new GetExitPageTitles()
        );
    }

}
