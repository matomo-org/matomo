<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Visualizations;

use Piwik\API\Request;
use Piwik\Common;
use Piwik\DataTable\Filter\AddColumnsProcessedMetricsGoal;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Site;

require_once PIWIK_INCLUDE_PATH . '/core/Twig.php';

/**
 * DataTable Visualization that derives from HtmlTable and shows goals metrics for page conversions
 */
class GoalsPages extends Goals
{
    const ID = 'tableGoalsPages';

    public function beforeRender()
    {
        $this->removeExcludedColumns();

        $this->config->addTranslation('nb_hits', Piwik::translate('General_ColumnUniquePageviews'));
        $this->config->metrics_documentation['nb_hits'] = Piwik::translate('General_ColumnUniquePageviewsDocumentation');

        parent::beforeRender();
    }

    protected function setPropertiesForGoalsOverview($idSite)
    {
        $allGoals = $this->getGoals($idSite);

        // set view properties
        $this->config->columns_to_display = array('label', 'nb_hits');

        foreach ($allGoals as $goal) {
            $column        = "goal_{$goal['idgoal']}_nb_conversion_page_rate";
            $this->config->columns_to_display[]  = $column;
        }

    }

    protected function setPropertiesForGoals($idSite, $idGoals)
    {

        $allGoals = $this->getGoals($idSite);

        if ('all' == $idGoals) {
            $idGoals = array_keys($allGoals);
        } else {
            // only sort by a goal's conversions if not showing all goals (for FULL_REPORT)
            $this->requestConfig->filter_sort_column = 'goal_' . reset($idGoals) . '_nb_conversions';
            $this->requestConfig->filter_sort_order  = 'desc';
        }

        $this->config->columns_to_display = array('label', 'nb_hits');

        $goalColumnTemplates = array(
            'goal_%s_nb_conversions_float',
            'goal_%s_revenue',
            'goal_%s_nb_conversion_page_rate',
        );

        // set columns to display (columns of same type but different goals will be next to each other,
        // ie, goal_0_nb_conversions, goal_1_nb_conversions, etc.)
        foreach ($idGoals as $idGoal) {
            foreach ($goalColumnTemplates as $columnTemplate) {
                $this->config->columns_to_display[] = sprintf($columnTemplate, $idGoal);
            }
        }

    }

}
