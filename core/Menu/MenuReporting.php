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
use Piwik\Plugin\Report;

/**
 * Contains menu entries for the Reporting menu (the menu displayed under the Piwik logo).
 * Plugins can implement the `configureReportingMenu()` method of the `Menu` plugin class to add, rename of remove
 * items. If your plugin does not have a `Menu` class yet you can create one using `./console generate:menu`.
 *
 * **Example**
 *
 *     public function configureReportingMenu(MenuReporting $menu)
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
 * @api
 * @method static \Piwik\Menu\MenuReporting getInstance()
 */
class MenuReporting extends MenuAbstract
{

    /**
     * See {@link add()}. Adds a new menu item to the visitors section of the reporting menu.
     * @param string $menuName
     * @param array $url
     * @param int $order
     * @param bool|string $tooltip
     * @api
     * @since 2.5.0
     */
    public function addVisitorsItem($menuName, $url, $order = 50, $tooltip = false)
    {
        $this->add('General_Visitors', $menuName, $url, true, $order, $tooltip);
    }

    /**
     * See {@link add()}. Adds a new menu item to the actions section of the reporting menu.
     * @param string $menuName
     * @param array $url
     * @param int $order
     * @param bool|string $tooltip
     * @api
     * @since 2.5.0
     */
    public function addActionsItem($menuName, $url, $order = 50, $tooltip = false)
    {
        $this->add('General_Actions', $menuName, $url, true, $order, $tooltip);
    }

    /**
     * Should not be a public API yet. We probably have to change the API once we have another use case.
     * @ignore
     */
    public function addGroup($menuName, $defaultTitle, Group $group, $order = 50, $tooltip = false)
    {
        $this->menuEntries[] = array(
            $menuName,
            $defaultTitle,
            $group,
            $order,
            $tooltip
        );
    }

    /**
     * See {@link add()}. Adds a new menu item to the referrers section of the reporting menu.
     * @param string $menuName
     * @param array $url
     * @param int $order
     * @param bool|string $tooltip
     * @api
     * @since 2.5.0
     */
    public function addReferrersItem($menuName, $url, $order = 50, $tooltip = false)
    {
        $this->add('Referrers_Referrers', $menuName, $url, true, $order, $tooltip);
    }

    /**
     * Returns if the URL was found in the menu.
     *
     * @param string $url
     * @return boolean
     */
    public function isUrlFound($url)
    {
        $menu = $this->getMenu();

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
        if (!$this->menu) {

            /**
             * @ignore
             * @deprecated
             */
            Piwik::postEvent('Menu.Reporting.addItems', array());

            foreach (Report::getAllReports() as $report) {
                if ($report->isEnabled()) {
                    $report->configureReportingMenu($this);
                }
            }

            foreach ($this->getAllMenus() as $menu) {
                $menu->configureReportingMenu($this);
            }

        }

        return parent::getMenu();
    }
}
