<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Menu;

use Piwik\Piwik;

/**
 * Contains menu entries for the User menu (the menu at the very top of the page).
 * Plugins can subscribe to the {@hook Menu.User.addItems} event to add new pages to
 * the user menu.
 * 
 * **Example**
 * 
 *     // add a new page in an observer to Menu.User.addItems
 *     public function addUserMenuItem()
 *     {
 *         MenuUser::getInstance()->add(
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
class MenuUser extends MenuTop
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
