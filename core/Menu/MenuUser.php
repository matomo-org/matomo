<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Menu;

/**
 * Contains menu entries for the User menu (the menu at the very top of the page).
 * Plugins can implement the `configureUserMenu()` method of the `Menu` plugin class to add, rename of remove
 * items. If your plugin does not have a `Menu` class yet you can create one using `./console generate:menu`.
 * 
 * **Example**
 *
 *     public function configureUserMenu(MenuUser $menu)
 *     {
 *         $menu->add(
 *             'MyPlugin_MyTranslatedMenuCategory',
 *             'MyPlugin_MyTranslatedMenuName',
 *             array('module' => 'MyPlugin', 'action' => 'index'),
 *             Piwik::isUserHasSomeAdminAccess(),
 *             $order = 2
 *         );
 *     }
 * 
 * @method static \Piwik\Menu\MenuUser getInstance()
 */
class MenuUser extends MenuAbstract
{

    /**
     * Triggers the Menu.User.addItems hook and returns the menu.
     *
     * @return Array
     */
    public function getMenu()
    {
        if (!$this->menu) {
            foreach ($this->getAvailableMenus() as $menu) {
                $menu->configureUserMenu($this);
            }
        }

        return parent::getMenu();
    }
}
