<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Container\StaticContainer;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugins\CorePluginsAdmin\Model\TagManagerTeaser;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Plugins\Marketplace\Plugins;

class Menu extends \Piwik\Plugin\Menu
{
    private $marketplacePlugins;

    /**
     * Menu constructor.
     * @param Plugins $marketplacePlugins
     */
    public function __construct($marketplacePlugins = null)
    {
        if (!empty($marketplacePlugins)) {
            $this->marketplacePlugins = $marketplacePlugins;
        } elseif (Marketplace::isMarketplaceEnabled()) {
            // we load it manually as marketplace plugin might not be loaded
            $this->marketplacePlugins = StaticContainer::get('Piwik\Plugins\Marketplace\Plugins');
        }
    }

    public function configureTopMenu(MenuTop $menu)
    {
        $tagManagerTeaser = new TagManagerTeaser(Piwik::getCurrentUserLogin());

        if ($tagManagerTeaser->shouldShowTeaser()) {
            $menu->addItem('Tag Manager', null, $this->urlForAction('tagManagerTeaser'));
        }
    }

    public function configureAdminMenu(MenuAdmin $menu)
    {
        $hasSuperUserAccess   = Piwik::hasUserSuperUserAccess();
        $isAnonymous          = Piwik::isUserIsAnonymous();
        $isMarketplaceEnabled = Marketplace::isMarketplaceEnabled();

        $pluginsUpdateMessage = '';

        if ($hasSuperUserAccess && $isMarketplaceEnabled && $this->marketplacePlugins) {
            $pluginsHavingUpdate = $this->marketplacePlugins->getPluginsHavingUpdate();

            if (!empty($pluginsHavingUpdate)) {
                $pluginsUpdateMessage = sprintf(' (%d)', count($pluginsHavingUpdate));
            }
        }

        if (!$isAnonymous) {
            $menu->addPlatformItem('', [], 7);
        }

        if ($hasSuperUserAccess) {
            $menu->addPluginItem(
                Piwik::translate('General_ManagePlugins') . $pluginsUpdateMessage,
                $this->urlForAction('plugins', ['activated' => '']),
                10
            );
        }
    }
}
