<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\AIAgents;

use Piwik\Piwik;
use Piwik\Plugin\Controller as PluginController;
use Piwik\Request;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

class Controller extends PluginController
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        parent::__construct();

        $this->translator = $translator;
    }

    public function getEvolutionGraph()
    {
        $period  = Request::fromRequest()->getStringParameter('period');
        $columns = Request::fromRequest()->getParameter('columns', false);

        if (false !== $columns) {
            $columns = Piwik::getArrayFromApiParameter($columns);
        }

        $documentation = $this->translator->translate('AIAgents_AIAgentVisitsDocumentation')
            . '<br />'
            . $this->translator->translate('General_BrokenDownReportDocumentation');

        $columnNames = ['nb_visits'];

        if (SettingsPiwik::isUniqueVisitorsEnabled($period)) {
            $columnNames[] = 'nb_uniq_visitors';
        }

        $columnNames[] = 'nb_actions';
        $columnNames[] = 'nb_actions_per_visit';
        $columnNames[] = 'bounce_rate';
        $columnNames[] = 'avg_time_on_site';

        $suffixes          = [API::AI_AGENT_COLUMN_SUFFIX, API::HUMAN_COLUMN_SUFFIX, ''];
        $selectableColumns = [];

        foreach ($suffixes as $suffix) {
            foreach ($columnNames as $column) {
                $selectableColumns[] = $column . $suffix;
            }
        }

        $view = $this->getLastUnitGraphAcrossPlugins(
            $this->pluginName,
            __FUNCTION__,
            $columns,
            $selectableColumns,
            $documentation
        );

        if (empty($view->config->columns_to_display)) {
            $view->config->columns_to_display = ['nb_visits' . API::AI_AGENT_COLUMN_SUFFIX];
        }

        // Some plugins (e.g. Bandwidth) might have added new selectable columns to our
        // report by listening for the "ViewDataTable.configure" for "API.get" requests.
        // Remove those columns again to keep the visualization clean.
        $view->config->selectable_columns = $selectableColumns;

        return $this->renderView($view);
    }
}
