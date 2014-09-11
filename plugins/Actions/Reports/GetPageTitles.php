<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Plugins\Actions\Columns\PageTitle;

class GetPageTitles extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new PageTitle();
        $this->name          = Piwik::translate('Actions_SubmenuPageTitles');
        $this->documentation = Piwik::translate('Actions_PageTitlesReportDocumentation',
                                                array('<br />', htmlentities('<title>')));

        $this->order   = 5;
        $this->metrics = array('nb_hits', 'nb_visits', 'bounce_rate', 'avg_time_on_page', 'exit_rate', 'avg_time_generation');

        $this->actionToLoadSubTables = $this->action;

        $this->menuTitle   = 'Actions_SubmenuPageTitles';
        $this->widgetTitle = 'Actions_WidgetPageTitles';
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
        $metrics['nb_visits']   = Piwik::translate('General_ColumnUniquePageviewsDocumentation');
        $metrics['bounce_rate'] = Piwik::translate('General_ColumnPageBounceRateDocumentation');

        return $metrics;
    }

    public function configureView(ViewDataTable $view)
    {
        // link to the page, not just the report, but only if not a widget
        $widget = Common::getRequestVar('widget', false);

        $view->config->self_url = Request::getCurrentUrlWithoutGenericFilters(array(
            'module' => $this->module,
            'action' => $widget === false ? 'indexPageTitles' : 'getPageTitles'
        ));

        $view->config->title = $this->name;

        $view->config->addTranslation('label', $this->dimension->getName());
        $view->config->columns_to_display = array('label', 'nb_hits', 'nb_visits', 'bounce_rate',
                                                  'avg_time_on_page', 'exit_rate', 'avg_time_generation');

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function getRelatedReports()
    {
        return array(
            new GetEntryPageTitles(),
            new GetExitPageTitles()
        );
    }
}
