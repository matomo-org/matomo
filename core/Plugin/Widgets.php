<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\WidgetsList;

/**
 * Base class of all plugin widget providers. Plugins that define their own widgets can extend this class to easily
 * add new widgets, to remove or to rename existing items.
 *
 * For an example, see the {@link https://github.com/piwik/piwik/blob/master/plugins/ExampleRssWidget/Widget.php} plugin.
 *
 * @api
 */
class Widgets
{
    /**
     * Configures the widgets. Here you can for instance add or remove widgets.
     */
    public function configure(WidgetsList $widgetsList)
    {
    }
}
