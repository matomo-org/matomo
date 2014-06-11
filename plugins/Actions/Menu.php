<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Menu\MenuReporting;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->add('General_Actions', '', array('module' => 'Actions', 'action' => 'indexPageUrls'), true, 15);

        $actions = new Actions();
        if ($actions->isSiteSearchEnabled()) {
            $menu->add('General_Actions', 'Actions_SubmenuSitesearch', array('module' => 'Actions', 'action' => 'indexSiteSearch'), true, 5);
        }
    }

}
