<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $menu->add('General_Settings', 'Installation_SystemCheck',
                   array('module' => 'Installation', 'action' => 'systemCheckPage'),
                   Piwik::hasUserSuperUserAccess(),
                   $order = 15);
    }
}
