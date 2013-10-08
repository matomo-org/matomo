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

use Piwik\Menu\MenuAbstract;

/**
 * @package Piwik_Menu
 */
class Top extends MenuAbstract
{
    static private $instance = null;

    /**
     * @return \Piwik\Menu\Top
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
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
    public function get()
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
             *     Piwik_AddTopMenu(
             *         'TopMenuName',
             *         array('module' => 'MyPlugin', 'action' => 'index'),
             *         Piwik::isUserIsSuperUser(),
             *         $order = 6
             *     );
             * }
             * ```
             */
            Piwik_PostEvent('Menu.Top.addItems');
        }
        return parent::get();
    }
}
