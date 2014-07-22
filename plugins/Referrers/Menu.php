<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\Menu\MenuReporting;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->addReferrersItem('', array('module' => 'Referrers', 'action' => 'index'), 20);
        $menu->addReferrersItem('General_Overview', array('module' => 'Referrers', 'action' => 'index'), 1);
        $menu->addReferrersItem('Referrers_SubmenuSearchEngines', array('module' => 'Referrers', 'action' => 'getSearchEnginesAndKeywords'), 2);
        $menu->addReferrersItem('Referrers_SubmenuWebsites', array('module' => 'Referrers', 'action' => 'indexWebsites'), 3);
    }
}
