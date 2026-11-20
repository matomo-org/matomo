<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $idSiteParameter = (new SiteAwareLinks())->getIdSiteParameter();

        if (!Piwik::isUserIsAnonymous()) {
            $menu->addPlatformItem(
                'Marketplace_Marketplace',
                $this->urlForAction('overview', array_merge($idSiteParameter, ['activated' => '', 'mode' => 'admin', 'type' => '', 'show' => ''])),
                5
            );
        }

        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addPluginItem(
                Piwik::translate('Marketplace_LicenseKey'),
                $this->urlForAction('manageLicenseKey', $idSiteParameter),
                10
            );
            $menu->addPluginItem(
                Piwik::translate('General_ManageSubscriptions'),
                $this->urlForAction('subscriptionOverview', $idSiteParameter),
                20
            );
        }
    }
}
