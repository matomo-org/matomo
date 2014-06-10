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
        $menu->add('Referrers_Referrers', '', array('module' => 'Referrers', 'action' => 'index'), true, 20);
        $menu->add('Referrers_Referrers', 'General_Overview', array('module' => 'Referrers', 'action' => 'index'), true, 1);
        $menu->add('Referrers_Referrers', 'Referrers_SubmenuSearchEngines', array('module' => 'Referrers', 'action' => 'getSearchEnginesAndKeywords'), true, 2);
        $menu->add('Referrers_Referrers', 'Referrers_SubmenuWebsites', array('module' => 'Referrers', 'action' => 'indexWebsites'), true, 3);
        $menu->add('Referrers_Referrers', 'Referrers_Campaigns', array('module' => 'Referrers', 'action' => 'indexCampaigns'), true, 4);
    }
}
