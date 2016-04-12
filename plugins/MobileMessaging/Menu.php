<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MobileMessaging;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addManageItem('MobileMessaging_SettingsMenu', $this->urlForAction('index'), $order = 35);
        }

        if (!Piwik::isUserIsAnonymous()) {
            $menu->addPersonalItem('MobileMessaging_SettingsMenu', $this->urlForAction('userSettings'), $order = 12);
        }
    }
}
