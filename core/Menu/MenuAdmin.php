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
 * Contains menu entries for the Admin menu. Plugins can subscribe to the 
 * {@hook Menu.Admin.addItems} event to add new pages to the admin menu.
 * 
 * **Example**
 * 
 *     // add a new page in an observer to Menu.Admin.addItems
 *     public function addAdminMenuItem()
 *     {
 *         MenuAdmin::getInstance()->add(
 *             'MyPlugin_MyTranslatedAdminMenuCategory',
 *             'MyPlugin_MyTranslatedAdminPageName',
 *             array('module' => 'MyPlugin', 'action' => 'index'),
 *             Piwik::isUserHasSomeAdminAccess(),
 *             $order = 2
 *         );
 *     }
 * 
 * @method static \Piwik\Menu\MenuAdmin getInstance()
 */
class MenuAdmin extends MenuAbstract
{
    /**
     * Adds a new AdminMenu entry under the 'Settings' category.
     *
     * @param string $adminMenuName The name of the admin menu entry. Can be a translation token.
     * @param string|array $url The URL the admin menu entry should link to, or an array of query parameters
     *                          that can be used to build the URL.
     * @param boolean $displayedForCurrentUser Whether this menu entry should be displayed for the
     *                                         current user. If false, the entry will not be added.
     * @param int $order The order hint.
     * @api
     */
    public static function addEntry($adminMenuName, $url, $displayedForCurrentUser = true, $order = 20)
    {
        self::getInstance()->add('General_Settings', $adminMenuName, $url, $displayedForCurrentUser, $order);
    }

    /**
     * Triggers the Menu.MenuAdmin.addItems hook and returns the admin menu.
     *
     * @return Array
     */
    public function getMenu()
    {
        if (!$this->menu) {

            /**
             * Triggered when collecting all available admin menu items. Subscribe to this event if you want
             * to add one or more items to the Piwik admin menu.
             *
             * Menu items should be added via the {@link add()} method.
             *
             * **Example**
             * 
             *     use Piwik\Menu\MenuAdmin;
             * 
             *     public function addMenuItems()
             *     {
             *         MenuAdmin::getInstance()->add(
             *             'MenuName',
             *             'SubmenuName',
             *             array('module' => 'MyPlugin', 'action' => 'index'),
             *             $showOnlyIf = Piwik::hasUserSuperUserAccess(),
             *             $order = 6
             *         );
             *     }
             */
            Piwik::postEvent('Menu.Admin.addItems');
        }
        return parent::getMenu();
    }

    /**
     * Returns the current AdminMenu name
     *
     * @return boolean
     */
    public function getCurrentAdminMenuName()
    {
        $menu = MenuAdmin::getInstance()->getMenu();
        $currentModule = Piwik::getModule();
        $currentAction = Piwik::getAction();
        foreach ($menu as $submenu) {
            foreach ($submenu as $subMenuName => $parameters) {
                if (strpos($subMenuName, '_') !== 0 &&
                    $parameters['_url']['module'] == $currentModule
                    && $parameters['_url']['action'] == $currentAction
                ) {
                    return $subMenuName;
                }
            }
        }
        return false;
    }

    public static function removeEntry($menuName)
    {
        MenuAdmin::getInstance()->remove('General_Settings', $menuName);
    }
}
