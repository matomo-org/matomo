<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleRssWidget;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $category   = 'Example Widgets';
        $controller = 'ExampleRssWidget';

        $widgetsList->add($category, 'Piwik.org Blog', $controller, 'rssPiwik');
        $widgetsList->add($category, 'Piwik Changelog', $controller, 'rssChangelog');
    }

}
