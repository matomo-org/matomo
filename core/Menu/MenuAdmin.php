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
class MenuAdmin extends MenuAbstract
{
    /**
     * Adds a new AdminMenu entry.
     *
     * @param string $adminMenuName
     * @param string $url
     * @param boolean $displayedForCurrentUser
     * @param int $order
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
             * This event is triggered to collect all available admin menu items. Subscribe to this event if you want
             * to add one or more items to the Piwik admin menu. Just define the name of your menu item as well as a
             * controller and an action that should be executed once a user selects your menu item. It is also possible
             * to display the item only for users having a specific role.
             *
             * Example:
             * ```
             * public function addMenuItems()
             * {
             *     MenuAdmin::getInstance()->add(
             *         'MenuName',
             *         'SubmenuName',
             *         array('module' => 'MyPlugin', 'action' => 'index'),
             *         Piwik::isUserIsSuperUser(),
             *         $order = 6
             *     );
             * }
             * ```
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
    function getCurrentAdminMenuName()
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
}

