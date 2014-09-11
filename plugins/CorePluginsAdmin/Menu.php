<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Db;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuUser;
use Piwik\Piwik;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{

    public function configureAdminMenu(MenuAdmin $menu)
    {
        $hasSuperUserAcess    = Piwik::hasUserSuperUserAccess();
        $isAnonymous          = Piwik::isUserIsAnonymous();
        $isMarketplaceEnabled = CorePluginsAdmin::isMarketplaceEnabled();

        $pluginsUpdateMessage = '';
        $themesUpdateMessage  = '';

        if ($hasSuperUserAcess && $isMarketplaceEnabled) {
            $marketplace = new Marketplace();
            $pluginsHavingUpdate = $marketplace->getPluginsHavingUpdate($themesOnly = false);
            $themesHavingUpdate  = $marketplace->getPluginsHavingUpdate($themesOnly = true);

            if (!empty($pluginsHavingUpdate)) {
                $pluginsUpdateMessage = sprintf(' (%d)', count($pluginsHavingUpdate));
            }
            if (!empty($themesHavingUpdate)) {
                $themesUpdateMessage = sprintf(' (%d)', count($themesHavingUpdate));
            }
        }

        if (!$isAnonymous) {
            $menu->addPlatformItem(null, "", $order = 7);
        }

        if ($hasSuperUserAcess) {
            $menu->addPlatformItem(Piwik::translate('General_Plugins') . $pluginsUpdateMessage,
                                   $this->urlForAction('plugins', array('activated' => '')),
                                   $order = 1);
            $menu->addPlatformItem(Piwik::translate('CorePluginsAdmin_Themes') . $themesUpdateMessage,
                                   $this->urlForAction('themes', array('activated' => '')),
                                   $order = 3);
        }

        if ($this->isAllowedToSeeMarketPlace()) {
            $menu->addPlatformItem('CorePluginsAdmin_Marketplace',
                                   $this->urlForAction('extend', array('activated' => '')),
                                   $order = 5);

        }
    }

    private function isAllowedToSeeMarketPlace()
    {
        $isAnonymous          = Piwik::isUserIsAnonymous();
        $isMarketplaceEnabled = CorePluginsAdmin::isMarketplaceEnabled();

        return $isMarketplaceEnabled && !$isAnonymous;
    }

    public function configureUserMenu(MenuUser $menu)
    {
        if ($this->isAllowedToSeeMarketPlace()) {
            $menu->addPlatformItem('CorePluginsAdmin_Marketplace',
                                   $this->urlForAction('browsePlugins', array('activated' => '')),
                                   $order = 5);
        }
    }
}
