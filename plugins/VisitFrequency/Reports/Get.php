<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\VisitFrequency\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreHome\Columns\Metrics\ActionsPerVisit;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;
use Piwik\Plugins\CoreHome\Columns\Metrics\BounceRate;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\VisitFrequency\API;
use Piwik\Plugins\VisitFrequency\Columns\Metrics\ReturningMetric;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Get extends \Piwik\Plugin\Report
{
    protected function init()
    {
        parent::init();
        $this->categoryId    = 'General_Actions';
        $this->name          = Piwik::translate('VisitFrequency_ColumnReturningVisits');
        $this->documentation = Piwik::translate('VisitFrequency_VisitFrequencyReportDocumentation');
        $this->processedMetrics = [
            new ReturningMetric(new AverageTimeOnSite(), API::RETURNING_COLUMN_SUFFIX),
            new ReturningMetric(new ActionsPerVisit(), API::RETURNING_COLUMN_SUFFIX),
            new ReturningMetric(new BounceRate(), API::RETURNING_COLUMN_SUFFIX),
            new ReturningMetric(new AverageTimeOnSite(), API::NEW_COLUMN_SUFFIX),
            new ReturningMetric(new ActionsPerVisit(), API::NEW_COLUMN_SUFFIX),
            new ReturningMetric(new BounceRate(), API::NEW_COLUMN_SUFFIX)
        ];
        $this->metrics       = [
            'nb_visits_returning',
            'nb_actions_returning',
            'nb_uniq_visitors_returning',
            'nb_users_returning',
            'max_actions_returning',

            'nb_visits_new',
            'nb_actions_new',
            'nb_uniq_visitors_new',
            'nb_users_new',
            'max_actions_new',
        ];
        $this->order = 40;
        $this->subcategoryId = 'VisitorInterest_Engagement';
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->setName('VisitFrequency_WidgetGraphReturning')
                ->forceViewDataTable(Evolution::ID)
                ->setAction('getEvolutionGraph')
                ->setOrder(1)
        );

        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->forceViewDataTable(Sparklines::ID)
                ->setName('VisitFrequency_WidgetOverview')
                ->setOrder(2)
        );
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Sparklines::ID)) {
            $view->requestConfig->apiMethodToRequestDataTable = 'VisitFrequency.get';
            $this->addSparklineColumns($view);
            $view->config->addTranslations($this->getSparklineTranslations());
        }
    }

    private function getSparklineTranslations()
    {
        $translations = [
            'nb_visits_returning' => 'ReturnVisits',
            'nb_actions_returning' => 'ReturnActions',
            'nb_actions_per_visit_returning' => 'ReturnAvgActions',
            'avg_time_on_site_returning' => 'ReturnAverageVisitDuration',
            'bounce_rate_returning' => 'ReturnBounceRate',

            'nb_visits_new' => 'NewVisits',
            'nb_actions_new' => 'NewActions',
            'nb_actions_per_visit_new' => 'NewAvgActions',
            'avg_time_on_site_new' => 'NewAverageVisitDuration',
            'bounce_rate_new' => 'NewBounceRate',
        ];

        foreach ($translations as $metric => $key) {
            $translations[$metric] = Piwik::translate('VisitFrequency_' . $key);
        }

        return $translations;
    }

    private function addSparklineColumns(Sparklines $view)
    {
        $metrics = [
            'nb_visits',
            'avg_time_on_site',
            'nb_actions_per_visit',
            'bounce_rate',
            'nb_actions'
        ];

        $i = 1;
        foreach ($metrics as $metric) {
            foreach (['_returning', '_new'] as $suffix) {
                $view->config->addSparklineMetric([$metric . $suffix], $i++);
            }
        }
    }
}
