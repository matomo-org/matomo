<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\Reports;

use Piwik\Plugin\Report;
use Piwik\Plugins\AIAgents\Columns\Metrics\AIAgentMetric;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreHome\Columns\Metrics\ActionsPerVisit;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;
use Piwik\Plugins\CoreHome\Columns\Metrics\BounceRate;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Evolution;
use Piwik\Plugins\CoreVisualizations\Visualizations\Sparklines;
use Piwik\Plugins\AIAgents\API;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

class Get extends Report
{
    protected function init()
    {
        parent::init();

        $this->name          = Piwik::translate('AIAgents_AIAgentVisits');
        $this->categoryId    = 'AIAgents_AIAssistants';
        $this->subcategoryId = 'General_Overview';
        $this->order         = 10;

        $this->processedMetrics = [
            new AIAgentMetric(new AverageTimeOnSite(), API::AI_AGENT_COLUMN_SUFFIX),
            new AIAgentMetric(new ActionsPerVisit(), API::AI_AGENT_COLUMN_SUFFIX),
            new AIAgentMetric(new BounceRate(), API::AI_AGENT_COLUMN_SUFFIX),
            new AIAgentMetric(new AverageTimeOnSite(), API::HUMAN_COLUMN_SUFFIX),
            new AIAgentMetric(new ActionsPerVisit(), API::HUMAN_COLUMN_SUFFIX),
            new AIAgentMetric(new BounceRate(), API::HUMAN_COLUMN_SUFFIX),
        ];

        $this->metrics       = [
            'nb_visits_ai_agent',
            'nb_actions_ai_agent',
            'nb_uniq_visitors_ai_agent',
            'nb_users_ai_agent',
            'max_actions_ai_agent',

            'nb_visits_human',
            'nb_actions_human',
            'nb_uniq_visitors_human',
            'nb_users_human',
            'max_actions_human',
        ];
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->setName('AIAgents_WidgetGraphAIAgents')
                ->forceViewDataTable(Evolution::ID)
                ->setAction('getEvolutionGraph')
                ->setOrder(1)
        );

        $widgetsList->addWidgetConfig(
            $factory->createWidget()
                ->forceViewDataTable(Sparklines::ID)
                ->setName('AIAgents_WidgetOverviewAIAgents')
                ->setOrder(2)
        );
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->isViewDataTableId(Sparklines::ID)) {
            /** @var Sparklines $view */
            $view->requestConfig->apiMethodToRequestDataTable = 'AIAgents.get';
            $this->addSparklineColumns($view);
            $view->config->addTranslations($this->getSparklineTranslations());
        }
    }

    /**
     * @return array<string, string>
     */
    private function getSparklineTranslations(): array
    {
        return [
            'avg_time_on_site_ai_agent'     => Piwik::translate('AIAgents_SparklineAIAgentAverageVisitDuration'),
            'avg_time_on_site_human'        => Piwik::translate('AIAgents_SparklineHumanAverageVisitDuration'),
            'bounce_rate_ai_agent'          => Piwik::translate('AIAgents_SparklineAIAgentBounceRate'),
            'bounce_rate_human'             => Piwik::translate('AIAgents_SparklineHumanBounceRate'),
            'nb_actions_ai_agent'           => Piwik::translate('AIAgents_SparklineAIAgentActions'),
            'nb_actions_human'              => Piwik::translate('AIAgents_SparklineHumanActions'),
            'nb_actions_per_visit_ai_agent' => Piwik::translate('AIAgents_SparklineAIAgentAvgActionsPerVisit'),
            'nb_actions_per_visit_human'    => Piwik::translate('AIAgents_SparklineHumanAvgActionsPerVisit'),
            'nb_visits_ai_agent'            => Piwik::translate('AIAgents_SparklineAIAgentVisits'),
            'nb_visits_human'               => Piwik::translate('AIAgents_SparklineHumanVisits'),
        ];
    }

    private function addSparklineColumns(Sparklines $view): void
    {
        $metrics = [
            'nb_visits',
            'avg_time_on_site',
            'nb_actions_per_visit',
            'bounce_rate',
            'nb_actions',
        ];

        $i = 1;

        foreach ($metrics as $metric) {
            foreach (['_ai_agent', '_human'] as $suffix) {
                $view->config->addSparklineMetric([$metric . $suffix], $i++);
            }
        }
    }
}
