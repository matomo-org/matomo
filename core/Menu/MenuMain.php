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
 * Contains menu entries for the Main menu (the menu displayed under the Piwik logo).
 * Plugins can subscribe to the {@hook Menu.Reporting.addItems} event to add new pages to
 * the main menu.
 * 
 * **Example**
 * 
 *     // add a new page in an observer to Menu.Admin.addItems
 *     public function addMainMenuItem()
 *     {
 *         MenuMain::getInstance()->add(
 *             'MyPlugin_MyTranslatedMenuCategory',
 *             'MyPlugin_MyTranslatedMenuName',
 *             array('module' => 'MyPlugin', 'action' => 'index'),
 *             Piwik::isUserHasSomeAdminAccess(),
 *             $order = 2
 *         );
 *     }
 * 
 * @package Piwik_Menu
 * @api
 * @method static \Piwik\Menu\MenuMain getInstance()
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
             * Triggered when collecting all available reporting menu items. Subscribe to this event if you
             * want to add one or more items to the Piwik reporting menu.
             * 
             * Menu items should be added via the {@link add()} method.
             *
             * **Example**
             * 
             *     use Piwik\Menu\Main;
             * 
             *     public function addMenuItems()
             *     {
             *         Main::getInstance()->add(
             *             'CustomMenuName',
             *             'CustomSubmenuName',
             *             array('module' => 'MyPlugin', 'action' => 'index'),
             *             $showOnlyIf = Piwik::isUserIsSuperUser(),
             *             $order = 6
             *         );
             *     }
             */
            Piwik::postEvent('Menu.Reporting.addItems');
        }
        return parent::getMenu();
    }
}