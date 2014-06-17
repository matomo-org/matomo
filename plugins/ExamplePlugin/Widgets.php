<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExamplePlugin;

use Piwik\WidgetsList;

/**
 * This class allows you to add or remove widgets.
 * To configure a widget simply call the corresponding methods as described in the API-Reference:
 * http://developer.piwik.org/api-reference/Piwik/WidgetsList
 */
class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
      // $widgetsList->add('Example Category', 'Example Widget Name', $controller = 'ExamplePlugin', $action = 'index');
    }

}
