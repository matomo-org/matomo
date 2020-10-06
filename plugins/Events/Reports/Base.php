<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Reports;

use Piwik\EventDispatcher;
use Piwik\Common;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\Events\API;
use Piwik\Plugins\Events\Columns\Metrics\AverageEventValue;
use Piwik\Report\ReportWidgetFactory;
use Piwik\Widget\WidgetsList;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->categoryId = 'General_Actions';
        $this->subcategoryId = 'Events_Events';
        $this->onlineGuideUrl = 'https://matomo.org/docs/event-tracking/';

        $this->processedMetrics = array(
            new AverageEventValue()
        );
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory)
    {
        if (!$this->isSubtableReport) {
            $widget = $factory->createWidget()->setParameters(array(
                'secondaryDimension' => API::getInstance()->getDefaultSecondaryDimension($this->action)
            ));

            $widgetsList->addToContainerWidget('Events', $widget);
        }
    }

    public function configureView(ViewDataTable $view)
    {
        if (Common::getRequestVar('secondaryDimension', '', 'string')) {
            foreach (['pivotBy', 'pivotByColumn'] as $property) {
                $index = array_search($property, $view->requestConfig->overridableProperties);
                if ($index) {
                    unset($view->requestConfig->overridableProperties[$index]);
                }
            }
            $view->requestConfig->overridableProperties = array_values($view->requestConfig->overridableProperties);
        }

        if (property_exists($view->config, 'selectable_columns')) {
            $view->config->selectable_columns = ['nb_events', 'nb_visits', 'sum_event_value', 'nb_events_with_value'];
        }

        $this->configureFooterMessage($view);
    }

    protected function configureFooterMessage(ViewDataTable $view)
    {
        if ($this->isSubtableReport) {
            // no footer message for subtables
            return;
        }

        $out = '';
        EventDispatcher::getInstance()->postEvent('Template.afterEventsReport', array(&$out));
        $view->config->show_footer_message = $out;
    }


}
