<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuReporting;
use Piwik\Menu\MenuTop;
use Piwik\Menu\MenuUser;

/**
 * Base class of all plugin menu providers. Plugins that define their own menu items can extend this class to easily
 * add new items, to remove or to rename existing items.
 *
 * Descendants of this class can overwrite any of these methods. Each method will be executed only once per request
 * and cached for any further menu requests.
 *
 * For an example, see the {@link https://github.com/piwik/piwik/blob/master/plugins/ExampleUI/Menu.php} plugin.
 *
 * @api
 */
class Menu
{
    /**
     * Configures the reporting menu which should only contain links to reports of a specific site such as
     * "Search Engines", "Page Titles" or "Locations & Provider".
     */
    public function configureReportingMenu(MenuReporting $menu)
    {
    }

    /**
     * Configures the top menu which is supposed to contain analytics related items such as the
     * "All Websites Dashboard".
     */
    public function configureTopMenu(MenuTop $menu)
    {
    }

    /**
     * Configures the user menu which is supposed to contain user and help related items such as
     * "User settings", "Alerts" or "Email Reports".
     */
    public function configureUserMenu(MenuUser $menu)
    {
    }

    /**
     * Configures the admin menu which is supposed to contain only administration related items such as
     * "Websites", "Users" or "Plugin settings".
     */
    public function configureAdminMenu(MenuAdmin $menu)
    {
    }

}
