<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PrivacyManager;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::isUserHasSomeAdminAccess()) {
            $category = 'PrivacyManager_MenuPrivacySettings';
            $menu->registerMenuIcon($category, 'icon-locked-4');
            $menu->addItem($category, null, array(), 2);

            if (Piwik::hasUserSuperUserAccess()) {
                $menu->addItem($category, 'PrivacyManager_AnonymizeData', $this->urlForAction('privacySettings'), 5);
            }

            $menu->addItem($category, 'PrivacyManager_UsersOptOut', $this->urlForAction('usersOptOut'), 10);
            $menu->addItem($category, 'PrivacyManager_AskingForConsent', $this->urlForAction('consent'), 15);
            $menu->addItem($category, 'PrivacyManager_GdprOverview', $this->urlForAction('gdprOverview'), 20);
            $menu->addItem($category, 'PrivacyManager_GdprTools', $this->urlForAction('gdprTools'), 25);
        }
    }
}
