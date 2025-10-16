<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\Reports;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\JqplotGraph\Pie;
use Piwik\Plugins\Goals\Visualizations\Goals;
use Piwik\Plugins\Referrers\Columns\AIAssistant;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Request;
use Piwik\Widget\WidgetsList;

class GetAIAssistants extends Base
{
    protected function init()
    {
        parent::init();
        $this->dimension = new AIAssistant();
        $this->name = Piwik::translate('Referrers_AIAssistants');
        $this->documentation = Piwik::translate('Referrers_AIAssistantsReportDocumentation', '<br />');
        $this->hasGoalMetrics = true;
        $this->order = 13;
        $this->subcategoryId = 'Referrers_AIAssistants';

        if (Common::getRequestVar('secondaryDimension', false) == 'entryPageTitle') {
            $this->actionToLoadSubTables = 'getEntryPageTitlesForAIAssistant';
        } else {
            $this->actionToLoadSubTables = 'getEntryPageUrlsForAIAssistant';
        }
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        $widget = $factory->createWidget()->setName('Referrers_AIAssistants');
        $widgetsList->addWidgetConfig($widget);
    }

    public function getDefaultTypeViewDataTable()
    {
        return Pie::ID;
    }

    public function configureView(ViewDataTable $view)
    {
        $view->config->show_pivot_by_subtable = false;
        $view->config->show_exclude_low_population = false;

        $view->requestConfig->filter_limit = 10;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_subtable_when_show_goals = true;

            if (!$view->isViewDataTableId(Goals::ID)) {
                $view->config->show_related_reports = true;

                $secondaryDimension = 'entryPageUrl';
                if ('entryPageTitle' === Request::fromRequest()->getStringParameter('secondaryDimension', '')) {
                    $secondaryDimension = 'entryPageTitle';
                }

                $secondaryDimensionTranslation       = $this->getDimensionLabel($secondaryDimension);
                $view->config->related_reports_title =
                    Piwik::translate('Events_SecondaryDimension', $secondaryDimensionTranslation)
                    . "<br/>"
                    . Piwik::translate('Events_SwitchToSecondaryDimension', '');

                foreach (['entryPageUrl', 'entryPageTitle'] as $dimension) {
                    if ($dimension === $secondaryDimension) {
                        // don't show as related report the currently selected dimension
                        continue;
                    }

                    $dimensionTranslation = $this->getDimensionLabel($dimension);
                    $view->config->addRelatedReport(
                        $view->requestConfig->apiMethodToRequestDataTable,
                        $dimensionTranslation,
                        ['secondaryDimension' => $dimension]
                    );
                }
            }
        }
    }

    private function getDimensionLabel(string $dimension): string
    {
        if ($dimension === 'entryPageTitle') {
            return Piwik::translate('Actions_ColumnEntryPageTitle');
        }

        return Piwik::translate('Actions_ColumnEntryPageURL');
    }
}
