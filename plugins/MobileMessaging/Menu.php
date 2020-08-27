<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
        $title = 'MobileMessaging_SettingsMenu';
        $url = $this->urlForAction('index');
        $order = 35;

        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addSystemItem($title, $url, $order);
        } else if (!Piwik::isUserIsAnonymous()) {
            $menu->addPersonalItem($title, $url, $order);
        }
    }
}
