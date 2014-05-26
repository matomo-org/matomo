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
 * Contains menu entries for the Reporting menu (the menu displayed under the Piwik logo).
 * Plugins can subscribe to the {@hook Menu.Reporting.addItems} event to add new pages to
 * the reporting menu.
 *
 * **Example**
 *
 *     // add a new page in an observer to Menu.Admin.addItems
 *     public function addReportingMenuItem()
 *     {
 *         MenuReporting::getInstance()->add(
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
             */
            Piwik::postEvent('Menu.Reporting.addItems', array());

            foreach ($this->getAvailableMenus() as $menu) {
                $menu->configureReportingMenu($this);
            }
        }

        return parent::getMenu();
    }
}
