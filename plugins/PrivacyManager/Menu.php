<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager;

use Piwik\Container\StaticContainer;
use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::isUserHasSomeAdminAccess()) {
            $category = 'PrivacyManager_MenuPrivacySettings';
            $menu->registerMenuIcon($category, 'icon-locked');
            $menu->addItem($category, null, [], 3);

            if (Piwik::hasUserSuperUserAccess()) {
                $featureFlagManager = StaticContainer::get(FeatureFlagManager::class);
                if ($featureFlagManager->isFeatureActive(PrivacyCompliance::class)) {
                    $menu->addItem($category, 'PrivacyManager_Compliance', $this->urlForAction('compliance'), 0);
                }
                $menu->addItem($category, 'PrivacyManager_AnonymizeData', $this->urlForAction('privacySettings'), 5);
            }

            $menu->addItem($category, 'PrivacyManager_UsersOptOut', $this->urlForAction('usersOptOut'), 10);
            $menu->addItem($category, 'PrivacyManager_AskingForConsent', $this->urlForAction('consent'), 15);
            $menu->addItem($category, 'PrivacyManager_GdprOverview', $this->urlForAction('gdprOverview'), 20);
            $menu->addItem($category, 'PrivacyManager_GdprTools', $this->urlForAction('gdprTools'), 25);
        }
    }
}
