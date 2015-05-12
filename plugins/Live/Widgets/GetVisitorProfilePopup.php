<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live\Widgets;

use Piwik\Plugin\WidgetConfig;
use Piwik\WidgetsList;

class GetVisitorProfilePopup extends \Piwik\Plugin\Widget
{

    public static function configure(WidgetConfig $config)
    {
        $config->setCategory('Live!');
        $config->setName('Live_VisitorProfile');
        $config->setOrder(25);
    }

    public function render()
    {

    }

    public static function configureWidgetsList(WidgetsList $widgetsList)
    {

    }

}
