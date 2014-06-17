<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CustomVariables;

use Piwik\Db;
use Piwik\Menu\MenuReporting;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{

    public function configureReportingMenu(MenuReporting $menu)
    {
        $menu->add('General_Visitors', 'CustomVariables_CustomVariables', array('module' => 'CustomVariables', 'action' => 'index'), $display = true, $order = 50);
    }

}
