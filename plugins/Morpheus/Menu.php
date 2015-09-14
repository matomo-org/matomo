<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Morpheus;

use Piwik\Development;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuUser;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Development::isEnabled() && Piwik::isUserHasSomeAdminAccess()) {
            $menu->addDevelopmentItem('UI Demo', $this->urlForAction('demo'));
        }
    }
    public function configureUserMenu(MenuUser $menu)
    {
        if (Development::isEnabled() && Piwik::isUserHasSomeAdminAccess()) {
            $menu->addPlatformItem('UI Demo', $this->urlForAction('demo'), $order = 15);
        }
    }
}
