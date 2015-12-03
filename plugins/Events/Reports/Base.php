<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Reports;

use Piwik\API\Request;
use Piwik\Plugins\Events\Columns\Metrics\AverageEventValue;

abstract class Base extends \Piwik\Plugin\Report
{
    protected function init()
    {
        $this->category = 'Events_Events';
        $this->processedMetrics = array(
            new AverageEventValue()
        );

        $secondaryDimensions = Request::processRequest('Events.getDefaultSecondaryDimension', array('apiMethod' => $this->action));

        $this->widgetParams = array(
            'secondaryDimension' => $secondaryDimensions
        );
    }

}
