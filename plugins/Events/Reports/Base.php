<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Reports;

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


}
