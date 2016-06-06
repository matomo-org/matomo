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
use Piwik\Plugins\Actions\Columns\PageTitle;
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\Actions\Columns\Metrics\AverageTimeOnPage;
use Piwik\Plugins\Actions\Columns\Metrics\BounceRate;
use Piwik\Plugins\Actions\Columns\Metrics\ExitRate;

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
        $this->metrics = array('nb_hits', 'nb_visits');
        $this->processedMetrics = array(
            new AverageTimeOnPage(),
            new BounceRate(),
            new ExitRate(),
            new AveragePageGenerationTime()
        );

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
        $view->config->self_url = Request::getCurrentUrlWithoutGenericFilters(array(
            'module' => $this->module,
            'action' => 'getPageTitles',
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
            self::factory('Actions', 'getEntryPageTitles'),
            self::factory('Actions', 'getExitPageTitles'),
        );
    }
}
