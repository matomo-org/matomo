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
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Plugins\Marketplace\Plugins;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{
    private $marketplacePlugins;

    public function __construct(Plugins $marketplacePlugins = null)
    {
        $this->marketplacePlugins = $marketplacePlugins;
    }

    public function configureAdminMenu(MenuAdmin $menu)
    {
        $hasSuperUserAcess    = Piwik::hasUserSuperUserAccess();
        $isAnonymous          = Piwik::isUserIsAnonymous();
        $isMarketplaceEnabled = Marketplace::isMarketplaceEnabled();

        $pluginsUpdateMessage = '';

        if ($hasSuperUserAcess && $isMarketplaceEnabled && $this->marketplacePlugins) {
            $pluginsHavingUpdate = $this->marketplacePlugins->getPluginsHavingUpdate();

            if (!empty($pluginsHavingUpdate)) {
                $pluginsUpdateMessage = sprintf(' (%d)', count($pluginsHavingUpdate));
            }
        }

        if (!$isAnonymous) {
            $menu->addPlatformItem(null, "", $order = 7);
        }

        if ($hasSuperUserAcess) {
            $menu->addManageItem(Piwik::translate('General_Plugins') . $pluginsUpdateMessage,
                                   $this->urlForAction('plugins', array('activated' => '')),
                                   $order = 4);
        }
    }
}
