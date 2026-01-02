<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents;

use Piwik\Columns\Dimension;
use Piwik\Plugin;
use Piwik\Plugins\AIAgents\Providers\AgentAbstract;
use Piwik\Plugins\AIAgents\Providers\ChatGPT;
use Piwik\Plugins\AIAgents\Providers\NovaAct;

class AIAgents extends Plugin
{
    public function registerEvents()
    {
        return [
            'Metrics.getDefaultMetricTranslations'  => 'addMetricTranslations',
            'Metrics.getDefaultMetricSemanticTypes' => 'addMetricSemanticTypes',
        ];
    }

    /**
     * @param array<string, string> $translations
     */
    public function addMetricTranslations(&$translations): void
    {
        $translations['avg_time_on_site_ai_agent']     = 'AIAgents_ColumnAIAgentAverageVisitDuration';
        $translations['avg_time_on_site_human']        = 'AIAgents_ColumnHumanAverageVisitDuration';
        $translations['bounce_rate_ai_agent']          = 'AIAgents_ColumnAIAgentBounceRate';
        $translations['bounce_rate_human']             = 'AIAgents_ColumnHumanBounceRate';
        $translations['max_actions_ai_agent']          = 'AIAgents_ColumnAIAgentMaxActions';
        $translations['max_actions_human']             = 'AIAgents_ColumnHumanMaxActions';
        $translations['nb_actions_ai_agent']           = 'AIAgents_ColumnAIAgentActions';
        $translations['nb_actions_human']              = 'AIAgents_ColumnHumanActions';
        $translations['nb_actions_per_visit_ai_agent'] = 'AIAgents_ColumnAIAgentAvgActionsPerVisit';
        $translations['nb_actions_per_visit_human']    = 'AIAgents_ColumnHumanAvgActionsPerVisit';
        $translations['nb_uniq_visitors_ai_agent']     = 'AIAgents_ColumnAIAgentUniqueVisitors';
        $translations['nb_uniq_visitors_human']        = 'AIAgents_ColumnHumanUniqueVisitors';
        $translations['nb_users_ai_agent']             = 'AIAgents_ColumnAIAgentUsers';
        $translations['nb_users_human']                = 'AIAgents_ColumnHumanUsers';
        $translations['nb_visits_ai_agent']            = 'AIAgents_ColumnAIAgentVisits';
        $translations['nb_visits_human']               = 'AIAgents_ColumnHumanVisits';
    }

    /**
     * @param array<string, string> $types
     */
    public function addMetricSemanticTypes(array &$types): void
    {
        $types['max_actions_ai_agent']       = Dimension::TYPE_NUMBER;
        $types['max_actions_human']          = Dimension::TYPE_NUMBER;
        $types['nb_actions_ai_agent']        = Dimension::TYPE_NUMBER;
        $types['nb_actions_human']           = Dimension::TYPE_NUMBER;
        $types['nb_uniq_ai_agent']           = Dimension::TYPE_NUMBER;
        $types['nb_uniq_human']              = Dimension::TYPE_NUMBER;
        $types['nb_uniq_visitors_ai_agent']  = Dimension::TYPE_NUMBER;
        $types['nb_uniq_visitors_human']     = Dimension::TYPE_NUMBER;
        $types['nb_users_ai_agent']          = Dimension::TYPE_NUMBER;
        $types['nb_users_human']             = Dimension::TYPE_NUMBER;
        $types['nb_visits_ai_agent']         = Dimension::TYPE_NUMBER;
        $types['nb_visits_human']            = Dimension::TYPE_NUMBER;
    }

    /**
     * @return array<AgentAbstract>
     */
    public static function getAvailableAgentProviders(): array
    {
        return [
            ChatGPT::getInstance(),
            NovaAct::getInstance(),
        ];
    }
}
