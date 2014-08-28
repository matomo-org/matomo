<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents;

use Piwik\Menu\MenuReporting;

/**
 * This class allows you to add, remove or rename menu items.
 * To configure a menu (such as Admin Menu, Reporting Menu, User Menu...) simply call the corresponding methods as
 * described in the API-Reference http://developer.piwik.org/api-reference/Piwik/Menu/MenuAbstract
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->addActionsItem('Contents_Contents', array('module' => 'Contents', 'action' => 'index'), $orderId = 40);
    }
}
