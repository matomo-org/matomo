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

}
