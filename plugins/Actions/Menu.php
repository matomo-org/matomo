<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Common;
use Piwik\Menu\MenuReporting;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->addActionsItem('', array(), 15);

        $idSite  = Common::getRequestVar('idSite', 0, 'int');
        $idSites = Common::getRequestVar('idSites', '', 'string');

        $actions = new Actions();
        if ($actions->isSiteSearchEnabled($idSites, $idSite)) {
            $menu->addActionsItem('Actions_SubmenuSitesearch', $this->urlForAction('indexSiteSearch'), 5);
        }
    }

}
