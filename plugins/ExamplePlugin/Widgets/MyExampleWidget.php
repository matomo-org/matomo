<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExamplePlugin\Widgets;

use Piwik\Plugin\Widget;
use Piwik\View;

/**
 * This class allows you to add your own widget to the Piwik platform. In case you want to remove widgets from another
 * plugin please have a look at the "configureWidgetsList()" method.
 * To configure a widget simply call the corresponding methods as described in the API-Reference:
 * http://developer.piwik.org/api-reference/Piwik/Plugin\Widget
 */
class MyExampleWidget extends Widget
{
    /**
     * Here you can define the category the widget belongs to. You can reuse any existing widget category or define
     * your own category.
     * @var string
     */
    protected $category = 'Example Category';

    /**
     * Here you can define the name of the widget belongs to.
     * @var string
     */
    protected $name     = 'Example Widget Name';

    /**
     * Here you can define the order of the widget. The lower the number, the earlier the widget will be listed.
     * @var string
     */
    protected $order    = 99;

    /**
     * This method renders the widget. It's on you how to generate the content of the widget.
     * As long as you return a string everything is fine. You can use for instance a "Piwik\View" to render a
     * twig template. In such a case don't forget to create a twig template (eg. myViewTemplate.twig) in the
     * "templates" directory of your plugin.
     *
     * @return string
     */
    public function render()
    {
        // $view = new View('@ExamplePlugin/myViewTemplate');
        // return $view->render();

        return '<div>My Widget Text</div>';
    }

    /**
     * Here you can optionally define URL parameters that will be used when this widget is requested.
    public function getParameters()
    {
        return array('myparam' => 'myvalue');
    }
     */

    /**
     * Defines whether a widget is enabled or not. For instance some widgets might not be available to every user or
     * might depend on a setting (such as Ecommerce) of a site. In such a case you can perform any checks and then
     * return `true` or `false`. If your report is only available to users having super user access you can do the
     * following: `return \Piwik\Piwik::hasUserSuperUserAccess();`
     * @return bool
    public function isEnabled()
    {
        return true;
    }
     */

}