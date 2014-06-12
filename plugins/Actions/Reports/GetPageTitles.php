<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\API;
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
        $this->metrics = array_keys($this->getMetrics());

        $this->actionToLoadSubTables = $this->action;

        $this->menuTitle   = 'Actions_SubmenuPageTitles';
        $this->widgetTitle = 'Actions_WidgetPageTitles';
    }

    protected function getMetrics()
    {
        return array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviews'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviews'),
            'bounce_rate'         => Piwik::translate('General_ColumnBounceRate'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPage'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRate'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTime')
        );
    }

    protected function getMetricsDocumentation()
    {
        return array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviewsDocumentation'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
            'bounce_rate'         => Piwik::translate('General_ColumnPageBounceRateDocumentation'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPageDocumentation'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRateDocumentation'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTimeDocumentation'),
        );
    }

    public function configureView(ViewDataTable $view)
    {
        // link to the page, not just the report, but only if not a widget
        $widget = Common::getRequestVar('widget', false);

        $view->config->self_url = Request::getCurrentUrlWithoutGenericFilters(array(
            'module' => 'Actions',
            'action' => $widget === false ? 'indexPageTitles' : 'getPageTitles'
        ));

        $view->config->title = $this->name;
        $view->config->addRelatedReports(array(
            'Actions.getEntryPageTitles' => Piwik::translate('Actions_EntryPageTitles'),
            'Actions.getExitPageTitles'  => Piwik::translate('Actions_ExitPageTitles'),
        ));

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
