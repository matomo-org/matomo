<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Reports;

use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Events\API;
use Piwik\Plugins\Events\Events;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->category = 'Events_Events';
        $this->processedMetrics = false;

        $this->widgetParams = array(
            'secondaryDimension' => API::getInstance()->getDefaultSecondaryDimension($this->action)
        );
    }

    public function configureView(ViewDataTable $view)
    {
        if ($view->requestConfig->getApiModuleToRequest() != 'Events') {
            return;
        }

        // eg. 'Events.getCategory'
        $apiMethod = $view->requestConfig->getApiMethodToRequest();

        $events = new Events();
        $secondaryDimension = $events->getSecondaryDimensionFromRequest();
        $view->config->subtable_controller_action = API::getInstance()->getActionToLoadSubtables($apiMethod, $secondaryDimension);
        $view->config->columns_to_display = array('label', 'nb_events', 'sum_event_value');
        $view->config->show_flatten_table = true;
        $view->config->show_table_all_columns = false;
        $view->requestConfig->filter_sort_column = 'nb_events';

        $labelTranslation = $events->getColumnTranslation($apiMethod);
        $view->config->addTranslation('label', $labelTranslation);
        $view->config->addTranslations($events->getMetricTranslations());
        $this->addRelatedReports($view, $secondaryDimension);
        $this->addTooltipEventValue($view);
    }

    private function addRelatedReports($view, $secondaryDimension)
    {
        if(empty($secondaryDimension)) {
            // eg. Row Evolution
            return;
        }

        $events = new Events();
        $view->config->show_related_reports = true;

        $apiMethod = $view->requestConfig->getApiMethodToRequest();
        $secondaryDimensions = API::getInstance()->getSecondaryDimensions($apiMethod);

        if(empty($secondaryDimensions)) {
            return;
        }

        $secondaryDimensionTranslation = $events->getDimensionLabel($secondaryDimension);
        $view->config->related_reports_title =
            Piwik::translate('Events_SecondaryDimension', $secondaryDimensionTranslation)
            . "<br/>"
            . Piwik::translate('Events_SwitchToSecondaryDimension', '');

        foreach($secondaryDimensions as $dimension) {
            if($dimension == $secondaryDimension) {
                // don't show as related report the currently selected dimension
                continue;
            }

            $dimensionTranslation = $events->getDimensionLabel($dimension);
            $view->config->addRelatedReport(
                $view->requestConfig->apiMethodToRequestDataTable,
                $dimensionTranslation,
                array('secondaryDimension' => $dimension)
            );
        }

    }

    private function addTooltipEventValue($view)
    {
        // Creates the tooltip message for Event Value column
        $tooltipCallback = function ($hits, $min, $max, $avg) {
            if (!$hits) {
                return false;
            }
            $msgEventMinMax = Piwik::translate("Events_EventValueTooltip", array($hits, "<br />", $min, $max));
            $msgEventAvg = Piwik::translate("Events_AvgEventValue", $avg);
            return $msgEventMinMax . "<br/>" . $msgEventAvg;
        };

        // Add tooltip metadata column to the DataTable
        $view->config->filters[] = array('ColumnCallbackAddMetadata',
            array(
                array(
                    'nb_events',
                    'min_event_value',
                    'max_event_value',
                    'avg_event_value'
                ),
                'sum_event_value_tooltip',
                $tooltipCallback
            )
        );
    }
}
