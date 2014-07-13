<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitsSummary;

use Piwik\Menu\MenuReporting;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->add('General_Visitors', '', array('module' => 'VisitsSummary', 'action' => 'index'), true, 10);
        $menu->add('General_Visitors', 'General_Overview', array('module' => 'VisitsSummary', 'action' => 'index'), true, 1);
    }
}
