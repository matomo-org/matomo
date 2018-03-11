<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
            $menu->addItem($category, null, array(), 2);
            $menu->addItem($category, 'General_Settings',
                                 $this->urlForAction('privacySettings'),
                                 $order = 5);
            $menu->addItem($category, 'GDPR Overview', $this->urlForAction('gdprOverview'), 10);
            $menu->addItem($category, 'PrivacyManager_ManageRights', $this->urlForAction('gdprManageRights'), 15);
        }
    }
}
