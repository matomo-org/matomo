<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountryMap;

use Piwik\Piwik;
use Piwik\View;
use Piwik\Plugin\Manager as PluginManager;

/**
 * This class allows you to add your own widgets to the Piwik platform. In case you want to remove widgets from another
 * plugin please have a look at the "configureWidgetsList()" method.
 * To configure a widget simply call the corresponding methods as described in the API-Reference:
 * http://developer.piwik.org/api-reference/Piwik/Plugin\Widgets
 */
class Widgets extends \Piwik\Plugin\Widgets
{
    /**
     * Here you can define the category the widget belongs to. You can reuse any existing widget category or define
     * your own category.
     * @var string
     */
    protected $category = 'General_Visitor';

    protected function init()
    {
        if (PluginManager::getInstance()->isPluginActivated('UserCountry')) {
            $this->addWidget(Piwik::translate('UserCountryMap_VisitorMap'), 'visitorMap');
            $this->addWidgetWithCustomCategory('Live!', Piwik::translate('UserCountryMap_RealTimeMap'), 'realtimeMap');
        }
    }

}
