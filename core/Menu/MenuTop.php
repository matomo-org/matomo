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
 * Contains menu entries for the Top menu (the menu at the very top of the page).
 * Plugins can subscribe to the [Menu.Top.addItems](#) event to add new pages to
 * the top menu.
 * 
 * **Example**
 * 
 *     // add a new page in an observer to Menu.Admin.addItems
 *     public function addTopMenuItem()
 *     {
 *         MenuTop::getInstance()->add(
 *             'MyPlugin_MyTranslatedMenuCategory',
 *             'MyPlugin_MyTranslatedMenuName',
 *             array('module' => 'MyPlugin', 'action' => 'index'),
 *             Piwik::isUserHasSomeAdminAccess(),
 *             $order = 2
 *         );
 *     }
 * 
 * @package Piwik_Menu
 * @method static \Piwik\Menu\MenuTop getInstance()
 */
class MenuTop extends MenuAbstract
{
    /**
     * Adds a new entry to the TopMenu.
     *
     * @param string $topMenuName The menu item name. Can be a translation token.
     * @param string|array $url The URL the admin menu entry should link to, or an array of query parameters
     *                          that can be used to build the URL. If `$isHTML` is true, this can be a string with
     *                          HTML that is simply embedded.
     * @param boolean $displayedForCurrentUser Whether this menu entry should be displayed for the
     *                                         current user. If false, the entry will not be added.
     * @param int $order The order hint.
     * @param bool $isHTML Whether `$url` is an HTML string or a URL that will be rendered as a link.
     * @param bool|string $tooltip Optional tooltip to display.
     * @api
     */
    public static function addEntry($topMenuName, $url, $displayedForCurrentUser = true, $order = 10, $isHTML = false, $tooltip = false)
    {
        if ($isHTML) {
            MenuTop::getInstance()->addHtml($topMenuName, $url, $displayedForCurrentUser, $order, $tooltip);
        } else {
            MenuTop::getInstance()->add($topMenuName, null, $url, $displayedForCurrentUser, $order, $tooltip);
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
             * Triggered when collecting all available menu items that are be displayed on the very top of every
             * page, next to the login/logout links. Subscribe to this event if you want to add one or more items
             * to the top menu.
             * 
             * Menu items should be added via the [MenuTop::addEntry](#addEntry) method.
             *
             * **Example**
             * 
             *     use Piwik\Menu\MenuTop;
             *
             *     public function addMenuItems()
             *     {
             *         MenuTop::addEntry(
             *             'TopMenuName',
             *             array('module' => 'MyPlugin', 'action' => 'index'),
             *             $showOnlyIf = Piwik::isUserIsSuperUser(),
             *             $order = 6
             *         );
             *     }
             */
            Piwik::postEvent('Menu.Top.addItems');
        }
        return parent::getMenu();
    }
}