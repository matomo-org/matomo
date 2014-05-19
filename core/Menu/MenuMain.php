<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Menu;


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
 * @api
 * @method static \Piwik\Menu\MenuMain getInstance()
 */
class MenuMain extends MenuReporting
{
}
