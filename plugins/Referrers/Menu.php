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
        $menu->addReferrersItem('', array(), 20);
        $menu->addReferrersItem('General_Overview', $this->urlForAction('index'), 1);
        $menu->addReferrersItem('Referrers_WidgetGetAll', $this->urlForAction('allReferrers'), 2);
        $menu->addReferrersItem('Referrers_SubmenuSearchEngines', $this->urlForAction('getSearchEnginesAndKeywords'), 3);
        $menu->addReferrersItem('Referrers_SubmenuWebsites', $this->urlForAction('indexWebsites'), 4);
    }
}
