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
        $this->addItem('CoreAdminHome_Administration', $menuName, $url, $order, $tooltip);
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
}
