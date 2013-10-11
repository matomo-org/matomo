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
class MenuMain extends MenuAbstract
{
    /**
     * Returns if the URL was found in the menu.
     *
     * @param string $url
     * @return boolean
     */
    public function isUrlFound($url)
    {
        $menu = MenuMain::getInstance()->getMenu();

        foreach ($menu as $subMenus) {
            foreach ($subMenus as $subMenuName => $menuUrl) {
                if (strpos($subMenuName, '_') !== 0 && $menuUrl['_url'] == $url) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Triggers the Menu.Reporting.addItems hook and returns the menu.
     *
     * @return Array
     */
    public function getMenu()
    {
        // We trigger the Event only once!
        if (!$this->menu) {

            /**
             * This event is triggered to collect all available reporting menu items. Subscribe to this event if you
             * want to add one or more items to the Piwik reporting menu. Just define the name of your menu item as
             * well as a controller and an action that should be executed once a user selects your menu item. It is
             * also possible to display the item only for users having a specific role.
             *
             * Example:
             * ```
             * public function addMenuItems()
             * {
             *     \Piwik\Menu\Main::getInstance()->add(
             *         'CustomMenuName',
             *         'CustomSubmenuName',
             *         array('module' => 'MyPlugin', 'action' => 'index'),
             *         Piwik::isUserIsSuperUser(),
             *         $order = 6
             *     );
             * }
             * ```
             */
            Piwik::postEvent('Menu.Reporting.addItems');
        }
        return parent::getMenu();
    }
}

