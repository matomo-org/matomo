<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Events\Visualizations;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Plugins\Events\API;

/**
 * @property Events\RequestConfig $requestConfig
 */
class Events extends HtmlTable
{
    const ID = 'tableEvents';

    public static function getDefaultRequestConfig()
    {
        return new Events\RequestConfig();
    }

    public function beforeLoadDataTable()
    {
        parent::beforeLoadDataTable();
        if($this->requestConfig->getApiModuleToRequest() != 'Events') {
            // this is not an Events apiMethod
            throw new \Exception( self::ID . " visualisation works only for Events data.");
        }

        // eg. 'Events.getCategory'
        $apiMethod = $this->requestConfig->getApiMethodToRequest();


        $secondaryDimension = $this->requestConfig->secondaryDimension;
        $this->config->subtable_controller_action = API::getInstance()->getActionToLoadSubtables($apiMethod, $secondaryDimension);
        $this->config->columns_to_display = array('label', 'nb_events', 'sum_event_value');
        $this->config->show_flatten_table = true;
        $this->config->custom_parameters = array('flat' => 0);
        $this->requestConfig->filter_sort_column = 'nb_events';

        $events = new \Piwik\Plugins\Events\Events();
        $labelTranslation = $events->getColumnTranslation($apiMethod);

        $this->config->addTranslation('label', $labelTranslation);
        $this->config->addTranslations($events->getMetricTranslations());
        $this->addRelatedReports($events);
        $this->addTooltipEventValue();
    }

    protected function addRelatedReports(\Piwik\Plugins\Events\Events $events)
    {
        $this->config->show_related_reports = true;

        $apiMethod = $this->requestConfig->getApiMethodToRequest();
        $secondaryDimensions = API::getInstance()->getSecondaryDimensions($apiMethod);

        $currentSecondaryDimension = $this->requestConfig->secondaryDimension;
        $currentSecondaryDimensionTranslation = $events->getDimensionLabel($currentSecondaryDimension);
        $this->config->related_reports_title =
            Piwik::translate('Events_SecondaryDimension', $currentSecondaryDimensionTranslation)
            . "<br/>"
            . Piwik::translate('Events_SwitchToSecondaryDimension', '');

        foreach($secondaryDimensions as $dimension) {
            if($dimension == $currentSecondaryDimension) {
                // don't show as related report the currently selected dimension
                continue;
            }

            $dimensionTranslation = $events->getDimensionLabel($dimension);
            $this->config->addRelatedReport(
                $this->requestConfig->apiMethodToRequestDataTable,
                $dimensionTranslation,
                array('secondaryDimension' => $dimension)
            );
        }

    }

    protected function addTooltipEventValue()
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
        $this->config->filters[] = array('ColumnCallbackAddMetadata',
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
