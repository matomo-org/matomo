<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExamplePlugin;

use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuReporting;
use Piwik\Menu\MenuTop;
use Piwik\Menu\MenuUser;

/**
 * This class allows you to add, remove or rename menu items.
 * To configure a menu (such as Admin Menu, Reporting Menu, User Menu...) simply call the corresponding methods as
 * described in the API-Reference http://developer.piwik.org/api-reference/Piwik/Menu/MenuAbstract
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        // reuse an existing category. Execute the showList() method within the controller when menu item was clicked
        // $menu->addVisitorsItem('Report 1', $this->urlForAction('showList'), $orderId = 30);
        // $menu->addActionsItem('Report 1', $this->urlForAction('showList'), $orderId = 30);

        // or create a custom category 'UI Framework'
        // $menu->addItem('UI Framework', '', $this->urlForDefaultAction(), $orderId = 30);
        // $menu->addItem('UI Framework', 'Report 1', $this->urlForAction('showList'), $orderId = 30);
    }

    public function configureAdminMenu(MenuAdmin $menu)
    {
        // reuse an existing category
        // $menu->addSettingsItem('My Admin Item', $this->urlForDefaultAction(), $orderId = 30);
        // $menu->addPlatformItem('My Admin Item', $this->urlForDefaultAction(), $orderId = 30);

        // or create a custom category
        // $menu->addItem('General_Settings', 'My Admin Item', $this->urlForDefaultAction(), $orderId = 30);
    }

    public function configureTopMenu(MenuTop $menu)
    {
        // $menu->addItem('My Top Item', null, $this->urlForDefaultAction(), $orderId = 30);
    }

    public function configureUserMenu(MenuUser $menu)
    {
        // reuse an existing category. Execute the showList() method within the controller when menu item was clicked
        // $menu->addManageItem('My User Item', $this->urlForAction('showList'), $orderId = 30);
        // $menu->addPlatformItem('My User Item', $this->urlForDefaultAction(), $orderId = 30);

        // or create a custom category
        // $menu->addItem('CoreAdminHome_MenuManage', 'My User Item', $this->urlForDefaultAction(), $orderId = 30);
    }
}
