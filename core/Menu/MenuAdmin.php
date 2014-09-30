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
 * Contains menu entries for the Admin menu.
 * Plugins can implement the `configureAdminMenu()` method of the `Menu` plugin class to add, rename of remove
 * items. If your plugin does not have a `Menu` class yet you can create one using `./console generate:menu`.
 *
 * **Example**
 *
 *     public function configureAdminMenu(MenuAdmin $menu)
 *     {
 *         $menu->add(
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
     * @deprecated since version 2.4.0. See {@link Piwik\Plugin\Menu} for new implementation.
     */
    public static function addEntry($adminMenuName, $url, $displayedForCurrentUser = true, $order = 20)
    {
        if ($displayedForCurrentUser) {
            self::getInstance()->addItem('General_Settings', $adminMenuName, $url, $order);
        }
    }

    /**
     * See {@link add()}. Adds a new menu item to the development section of the admin menu.
     * @param string $menuName
     * @param array $url
     * @param int $order
     * @param bool|string $tooltip
     * @api
     * @since 2.5.0
     */
    public function addDevelopmentItem($menuName, $url, $order = 50, $tooltip = false)
    {
        $this->addItem('CoreAdminHome_MenuDevelopment', $menuName, $url, $order, $tooltip);
    }

    /**
     * See {@link add()}. Adds a new menu item to the diagnostic section of the admin menu.
     * @param string $menuName
     * @param array $url
     * @param int $order
     * @param bool|string $tooltip
     * @api
     * @since 2.5.0
     */
    public function addDiagnosticItem($menuName, $url, $order = 50, $tooltip = false)
    {
        $this->addItem('CoreAdminHome_MenuDiagnostic', $menuName, $url, $order, $tooltip);
    }

    /**
     * See {@link add()}. Adds a new menu item to the platform section of the admin menu.
     * @param string $menuName
     * @param array $url
     * @param int $order
     * @param bool|string $tooltip
     * @api
     * @since 2.5.0
     */
    public function addPlatformItem($menuName, $url, $order = 50, $tooltip = false)
    {
        $this->addItem('CorePluginsAdmin_MenuPlatform', $menuName, $url, $order, $tooltip);
    }

    /**
     * See {@link add()}. Adds a new menu item to the settings section of the admin menu.
     * @param string $menuName
     * @param array $url
     * @param int $order
     * @param bool|string $tooltip
     * @api
     * @since 2.5.0
     */
    public function addSettingsItem($menuName, $url, $order = 50, $tooltip = false)
    {
        $this->addItem('General_Settings', $menuName, $url, $order, $tooltip);
    }

    /**
     * See {@link add()}. Adds a new menu item to the manage section of the admin menu.
     * @param string $menuName
     * @param array $url
     * @param int $order
     * @param bool|string $tooltip
     * @api
     * @since 2.5.0
     */
    public function addManageItem($menuName, $url, $order = 50, $tooltip = false)
    {
        $this->addItem('CoreAdminHome_MenuManage', $menuName, $url, $order, $tooltip);
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
             * @ignore
             * @deprecated
             */
            Piwik::postEvent('Menu.Admin.addItems', array());

            foreach ($this->getAllMenus() as $menu) {
                $menu->configureAdminMenu($this);
            }
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

    /**
     * @deprecated since version 2.4.0. See {@link Piwik\Plugin\Menu} for new implementation.
     */
    public static function removeEntry($menuName, $subMenuName = false)
    {
        MenuAdmin::getInstance()->remove($menuName, $subMenuName);
    }
}
