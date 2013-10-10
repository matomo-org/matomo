<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik_Menu
 */
namespace Piwik\Menu;
use Piwik\Piwik;


/**
 * @package Piwik_Menu
 */
class MenuTop extends MenuAbstract
{
    /**
     * Adds a new entry to the TopMenu.
     *
     * @param string $topMenuName
     * @param string $data
     * @param boolean $displayedForCurrentUser
     * @param int $order
     * @param bool $isHTML
     * @param bool|string $tooltip Tooltip to display.
     * @api
     */
    public static function addEntry($topMenuName, $data, $displayedForCurrentUser = true, $order = 10, $isHTML = false, $tooltip = false)
    {
        if ($isHTML) {
            MenuTop::getInstance()->addHtml($topMenuName, $data, $displayedForCurrentUser, $order, $tooltip);
        } else {
            MenuTop::getInstance()->add($topMenuName, null, $data, $displayedForCurrentUser, $order, $tooltip);
        }
    }


    /**
     * Directly adds a menu entry containing html.
     *
     * @param string $menuName
     * @param string $data
     * @param boolean $displayedForCurrentUser
     * @param int $order
     * @param string $tooltip Tooltip to display.
     */
    public function addHtml($menuName, $data, $displayedForCurrentUser, $order, $tooltip)
    {
        if ($displayedForCurrentUser) {
            if (!isset($this->menu[$menuName])) {
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
             * This event is triggered to collect all available menu items that should be displayed on the very top next
             * to login/logout, API and other menu items. Subscribe to this event if you want to add one or more items.
             * It's fairly easy. Just define the name of your menu item as well as a controller and an action that
             * should be executed once a user selects your menu item. It is also possible to display the item only for
             * users having a specific role.
             *
             * Example:
             * ```
             * public function addMenuItems()
             * {
             *     MenuTop::addEntry(
             *         'TopMenuName',
             *         array('module' => 'MyPlugin', 'action' => 'index'),
             *         Piwik::isUserIsSuperUser(),
             *         $order = 6
             *     );
             * }
             * ```
             */
            Piwik::postEvent('Menu.Top.addItems');
        }
        return parent::getMenu();
    }
}
