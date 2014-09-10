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
        $menu->addActionsItem('', $this->urlForAction('menuGetPageUrls'), 15);

        $actions = new Actions();
        if ($actions->isSiteSearchEnabled()) {
            $menu->addActionsItem('Actions_SubmenuSitesearch', $this->urlForAction('indexSiteSearch'), 5);
        }
    }

}
