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

        $menu->add('CorePluginsAdmin_MenuPlatform', null, "", !$isAnonymous, $order = 7);
        $menu->add('CorePluginsAdmin_MenuPlatform', Piwik::translate('General_Plugins') . $pluginsUpdateMessage,
                   array('module' => 'CorePluginsAdmin', 'action' => 'plugins', 'activated' => ''),
                   $hasSuperUserAcess,
                   $order = 1);
        $menu->add('CorePluginsAdmin_MenuPlatform', Piwik::translate('CorePluginsAdmin_Themes') . $themesUpdateMessage,
                   array('module' => 'CorePluginsAdmin', 'action' => 'themes', 'activated' => ''),
                   $hasSuperUserAcess,
                   $order = 3);

        if ($isMarketplaceEnabled) {
            $menu->add('CorePluginsAdmin_MenuPlatform', 'CorePluginsAdmin_Marketplace',
                       array('module' => 'CorePluginsAdmin', 'action' => 'extend', 'activated' => ''),
                       !$isAnonymous,
                       $order = 5);

        }
    }

}
