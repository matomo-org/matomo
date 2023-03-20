<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Piwik;

class Contents extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
            'Metrics.getDefaultMetricDocumentationTranslations' => 'addMetricDocumentationTranslations',
            'Metrics.getDefaultMetricSemanticTypes' => 'addMetricSemanticTypes',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Actions.getCustomActionDimensionFieldsAndJoins' => 'provideActionDimensionFields'
        );
    }

    public function addMetricTranslations(&$translations)
    {
        $translations['nb_impressions']   = 'Contents_Impressions';
        $translations['nb_interactions']  = 'Contents_ContentInteractions';
        $translations['interaction_rate'] = 'Contents_InteractionRate';
    }

    public function addMetricSemanticTypes(array &$types): void
    {
        $types['nb_impressions']   = Dimension::TYPE_NUMBER;
        $types['nb_interactions']  = Dimension::TYPE_NUMBER;
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Contents/javascripts/contentsDataTable.js";
    }

    public function addMetricDocumentationTranslations(&$translations)
    {
        $translations['nb_impressions'] = Piwik::translate('Contents_ImpressionsMetricDocumentation');
        $translations['nb_interactions'] = Piwik::translate('Contents_InteractionsMetricDocumentation');
    }

    public function provideActionDimensionFields(&$fields, &$joins)
    {
        $fields[] = 'log_action_content_name.name as contentName';
        $fields[] = 'log_action_content_piece.name as contentPiece';
        $fields[] = 'log_action_content_target.name as contentTarget';
        $fields[] = 'log_action_content_interaction.name as contentInteraction';
        $joins[] = 'LEFT JOIN ' . Common::prefixTable('log_action') . ' AS log_action_content_name
					ON  log_link_visit_action.idaction_content_name = log_action_content_name.idaction';
        $joins[] = 'LEFT JOIN ' . Common::prefixTable('log_action') . ' AS log_action_content_piece
					ON  log_link_visit_action.idaction_content_piece = log_action_content_piece.idaction';
        $joins[] = 'LEFT JOIN ' . Common::prefixTable('log_action') . ' AS log_action_content_target
					ON  log_link_visit_action.idaction_content_target = log_action_content_target.idaction';
        $joins[] = 'LEFT JOIN ' . Common::prefixTable('log_action') . ' AS log_action_content_interaction
					ON  log_link_visit_action.idaction_content_interaction = log_action_content_interaction.idaction';
    }
}