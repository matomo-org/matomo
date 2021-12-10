<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Visualizations;

use Piwik\Piwik;

/**
 * DataTable Visualization that derives from HtmlTable and sets shows goal columns for page entries
 */
class GoalsEntryPages extends Goals
{
    const ID = 'tableGoalsEntryPages';

    public function beforeRender()
    {

        $this->removeExcludedColumns();

        $this->config->metrics_documentation['entry_nb_visits'] = Piwik::translate('General_ColumnEntrancesDocumentation');

        parent::beforeRender();
    }

    protected function setPropertiesForGoalsOverview($idSite)
    {
        $allGoals = $this->getGoals($idSite);

        // set view properties
        $this->config->columns_to_display = array('label', 'entry_nb_visits');

        foreach ($allGoals as $goal) {
            $column        = "goal_{$goal['idgoal']}_nb_conversion_entry_rate";
            $this->config->columns_to_display[]  = $column;
        }

        $this->config->columns_to_display[] = 'revenue_per_entry';
    }

    protected function setPropertiesForGoals($idSite, $idGoals)
    {
        $allGoals = $this->getGoals($idSite);

        if ('all' === $idGoals) {
            $idGoals = array_keys($allGoals);
            $this->requestConfig->filter_sort_column = 'entry_nb_visits';
        } else {
            // only sort by a goal's conversions if not showing all goals (for FULL_REPORT)
            $this->requestConfig->filter_sort_column = 'goal_' . reset($idGoals) . '_nb_conversions';
        }
        $this->requestConfig->filter_sort_order  = 'desc';

        $this->config->columns_to_display = array('label', 'entry_nb_visits');

        $goalColumnTemplates = array(
            'goal_%s_nb_conversions',
            'goal_%s_nb_conversion_entry_rate',
            'goal_%s_revenue',
            'goal_%s_revenue_per_entry',
        );

        foreach ($idGoals as $idGoal) {
            foreach ($goalColumnTemplates as $columnTemplate) {
                $this->config->columns_to_display[] = sprintf($columnTemplate, $idGoal);
            }
        }

        $this->config->columns_to_display[] = 'revenue_per_entry';
    }

}
