<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{

    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (!Piwik::isUserIsAnonymous()) {
            $menu->addPlatformItem(
                'Marketplace_Marketplace',
                $this->urlForAction('overview', ['activated' => '', 'mode' => 'admin', 'type' => '', 'show' => '']),
                5
            );
        }

        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addPluginItem(
                Piwik::translate('Marketplace_LicenseKey'),
                $this->urlForAction('manageLicenseKey'),
                10
            );
            $menu->addPluginItem(
                Piwik::translate('General_ManageSubscriptions'),
                $this->urlForAction('subscriptionOverview'),
                20
            );
        }
    }

}
