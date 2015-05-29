<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Menu;

use Piwik\Piwik;

/**
 * Contains menu entries for the Top menu (the menu at the very top of the page).
 * Plugins can implement the `configureTopMenu()` method of the `Menu` plugin class to add, rename of remove
 * items. If your plugin does not have a `Menu` class yet you can create one using `./console generate:menu`.
 *
 * **Example**
 *
 *     public function configureTopMenu(MenuTop $menu)
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
 * @method static \Piwik\Menu\MenuTop getInstance()
 */
class MenuTop extends MenuAbstract
{
    /**
     * Directly adds a menu entry containing html.
     *
     * @param string $menuName
     * @param string $data
     * @param boolean $displayedForCurrentUser
     * @param int $order
     * @param string $tooltip Tooltip to display.
     * @api
     */
    public function addHtml($menuName, $data, $displayedForCurrentUser, $order, $tooltip)
    {
        if ($displayedForCurrentUser) {
            if (!isset($this->menu[$menuName])) {
                $this->menu[$menuName]['_name'] = $menuName;
                $this->menu[$menuName]['_html'] = $data;
                $this->menu[$menuName]['_order'] = $order;
                $this->menu[$menuName]['_hasSubmenu'] = false;
                $this->menu[$menuName]['_tooltip'] = $tooltip;
            }
        }
    }

    /**
     * Triggers the Menu.Top.addItems hook and returns the menu.
     *
     * @return Array
     */
    public function getMenu()
    {
        if (!$this->menu) {

            /**
             * @ignore
             * @deprecated
             */
            Piwik::postEvent('Menu.Top.addItems', array());

            foreach ($this->getAllMenus() as $menu) {
                $menu->configureTopMenu($this);
            }
        }

        return parent::getMenu();
    }
}
