<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Actions\Reports;

use Piwik\DbHelper;
use Piwik\Piwik;
use Piwik\Plugin\ReportsProvider;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Actions\Columns\Metrics\AveragePageGenerationTime;
use Piwik\Plugins\Actions\Columns\Metrics\BounceRate;
use Piwik\Plugins\Actions\Columns\PageUrl;
use Piwik\Plugins\Actions\Columns\Metrics\ExitRate;
use Piwik\Plugins\Actions\Columns\Metrics\AverageTimeOnPage;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class GetPageUrls extends Base
{
    protected function init()
    {
        parent::init();

        $this->dimension     = new PageUrl();
        $this->name          = Piwik::translate('Actions_PageUrls');
        $this->documentation = Piwik::translate('Actions_PagesReportDocumentation', '<br />')
                             . '<br />' . Piwik::translate('General_UsePlusMinusIconsDocumentation');

        $this->actionToLoadSubTables = $this->action;
        $this->order   = 2;
        $this->metrics = array('nb_hits', 'nb_visits');
        $this->processedMetrics = array(
            new AverageTimeOnPage(),
            new BounceRate(),
            new ExitRate(),
            new AveragePageGenerationTime()
        );

        $this->subcategoryId = 'General_Pages';
        $this->hasGoalMetrics = true;
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig($factory->createWidget()->setName($this->subcategoryId));
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
        $metrics['bounce_rate'] = Piwik::translate('General_ColumnPageBounceRateDocumentation');

        return $metrics;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->addTranslation('label', $this->dimension->getName());
        $view->config->columns_to_display = array('label', 'nb_hits', 'nb_visits', 'bounce_rate',
                                                  'avg_time_on_page', 'exit_rate');

        if (version_compare(DbHelper::getInstallVersion(), '4.0.0-b1', '<')) {
            $view->config->columns_to_display[] = 'avg_time_generation';
        }

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);

        $view->config->show_goals = true;

        // related reports are only shown on performance page
        if ($view->requestConfig->getRequestParam('performance') !== '1') {
            $view->config->related_reports = [];
        }
    }

    public function getRelatedReports()
    {
        return [
            ReportsProvider::factory('Actions', 'getEntryPageUrls'),
            ReportsProvider::factory('Actions', 'getExitPageUrls'),
        ];
    }
}
