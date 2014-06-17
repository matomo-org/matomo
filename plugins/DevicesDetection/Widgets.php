<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicesDetection;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $detection = new DevicesDetection();

        foreach ($detection->getRawMetadataReports() as $report) {
            list($category, $name, $controllerName, $controllerAction) = $report;
            if ($category == false)
                continue;
            $widgetsList->add($category, $name, $controllerName, $controllerAction);
        }
    }

}
