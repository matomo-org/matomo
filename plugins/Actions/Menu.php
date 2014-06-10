<?php
/**
 * Piwik - free/libre analytics platform
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
        $menu->add('General_Actions', 'General_Pages', array('module' => 'Actions', 'action' => 'indexPageUrls'), true, 1);
        $menu->add('General_Actions', 'Actions_SubmenuPagesEntry', array('module' => 'Actions', 'action' => 'indexEntryPageUrls'), true, 2);
        $menu->add('General_Actions', 'Actions_SubmenuPagesExit', array('module' => 'Actions', 'action' => 'indexExitPageUrls'), true, 3);
        $menu->add('General_Actions', 'Actions_SubmenuPageTitles', array('module' => 'Actions', 'action' => 'indexPageTitles'), true, 4);
        $menu->add('General_Actions', 'General_Outlinks', array('module' => 'Actions', 'action' => 'indexOutlinks'), true, 6);
        $menu->add('General_Actions', 'General_Downloads', array('module' => 'Actions', 'action' => 'indexDownloads'), true, 7);

        $actions = new Actions();
        if ($actions->isSiteSearchEnabled()) {
            $menu->add('General_Actions', 'Actions_SubmenuSitesearch', array('module' => 'Actions', 'action' => 'indexSiteSearch'), true, 5);
        }
    }

}
