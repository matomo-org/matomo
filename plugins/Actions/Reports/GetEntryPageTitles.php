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
use Piwik\Plugins\Actions\Columns\EntryPageTitle;
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\Actions\Columns\Metrics\AverageTimeOnPage;
use Piwik\Plugins\Actions\Columns\Metrics\BounceRate;
use Piwik\Plugins\Actions\Columns\Metrics\ExitRate;
use Piwik\Plugin\ReportsProvider;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class GetEntryPageTitles extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new EntryPageTitle();
        $this->name          = Piwik::translate('Actions_EntryPageTitles');
        $this->documentation = Piwik::translate('Actions_EntryPageTitlesReportDocumentation', '<br />')
                             . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation');
        $this->metrics = array('entry_nb_visits', 'entry_bounce_count');
        $this->processedMetrics = array(
            new AverageTimeOnPage(),
            new BounceRate(),
            new ExitRate(),
            new AveragePageGenerationTime()
        );
        $this->order   = 6;
        $this->actionToLoadSubTables = $this->action;
        $this->subcategoryId = 'Actions_SubmenuPagesEntry';
        $this->hasGoalMetrics = true;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig($factory->createWidget()->setName('Actions_WidgetEntryPageTitles'));
    }

    public function getProcessedMetrics()
    {
        $result = parent::getProcessedMetrics();

        // these metrics are not displayed in the API.getProcessedReport version of this report,
        // so they are removed here.
        unset($result['avg_time_on_page']);
        unset($result['exit_rate']);

        return $result;
    }

    protected function getMetricsDocumentation()
    {
        $metrics = parent::getMetricsDocumentation();
        $metrics['bounce_rate'] = Piwik::translate('General_ColumnPageBounceRateDocumentation');

        // remove these metrics from API.getProcessedReport version of this report
        unset($metrics['avg_time_on_page']);
        unset($metrics['exit_rate']);

        return $metrics;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslations(array('label' => $this->dimension->getName()));

        $view->config->columns_to_display = array('label', 'entry_nb_visits', 'entry_bounce_count', 'bounce_rate');
        $view->config->title = $this->name;

        $view->requestConfig->filter_sort_column = 'entry_nb_visits';

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);

        $view->config->show_goals = true;
    }

    public function getRelatedReports()
    {
        return array(
            ReportsProvider::factory('Actions', 'getPageTitles'),
            ReportsProvider::factory('Actions', 'getEntryPageUrls')
        );
    }
}
