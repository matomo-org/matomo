<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        foreach(Events::getLabelTranslations() as $apiMethod => $labels) {
            $params = array(
                'secondaryDimension' => API::getInstance()->getDefaultSecondaryDimension($apiMethod)
            );
            $widgetsList->add('Events_Events', $labels[0], 'Events', $apiMethod, $params);
        }
    }

}
