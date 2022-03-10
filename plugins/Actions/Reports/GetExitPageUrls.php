<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\API\Request;
use Piwik\Plugins\Actions\Columns\ExitPageUrl;
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\Actions\Columns\Metrics\AverageTimeOnPage;
use Piwik\Plugins\Actions\Columns\Metrics\BounceRate;
use Piwik\Plugins\Actions\Columns\Metrics\ExitRate;
use Piwik\Plugin\ReportsProvider;

class GetExitPageUrls extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new ExitPageUrl();
        $this->name          = Piwik::translate('Actions_SubmenuPagesExit');
        $this->documentation = Piwik::translate('Actions_ExitPagesReportDocumentation', '<br />')
                             . '<br />' . Piwik::translate('General_UsePlusMinusIconsDocumentation');

        $this->metrics = array('exit_nb_visits', 'nb_visits');
        $this->processedMetrics = array(
            new AverageTimeOnPage(),
            new BounceRate(),
            new ExitRate(),
            new AveragePageGenerationTime()
        );
        $this->actionToLoadSubTables = $this->action;

        $this->order = 4;

        $this->subcategoryId = 'Actions_SubmenuPagesExit';
    }

    public function getProcessedMetrics()
    {
        $result = parent::getProcessedMetrics();

        // these metrics are not displayed in the API.getProcessedReport version of this report,
        // so they are removed here.
        unset($result['bounce_rate']);
        unset($result['avg_time_on_page']);

        return $result;
    }

    public function getMetrics()
    {
        $metrics = parent::getMetrics();
        $metrics['nb_visits'] = Piwik::translate('General_ColumnUniquePageviews');

        unset($metrics['bounce_rate']);
        unset($metrics['avg_time_on_page']);

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
        $view->config->self_url = Request::getCurrentUrlWithoutGenericFilters(array(
            'module' => 'Actions',
            'action' => 'getExitPageUrls',
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
            ReportsProvider::factory('Actions', 'getExitPageTitles'),
        );
    }

}
