<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome;

use Piwik\WidgetsList;

class Widgets extends \Piwik\Plugin\Widgets
{
    public function configure(WidgetsList $widgetsList)
    {
        $category   = 'Example Widgets';
        $controller = 'CoreHome';

        $widgetsList->add($category, 'CoreHome_SupportPiwik', $controller, 'getDonateForm');
        $widgetsList->add($category, 'Installation_Welcome', $controller, 'getPromoVideo');
    }

}
