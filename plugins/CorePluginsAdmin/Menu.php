<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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
            $menu->addSystemItem(Piwik::translate('General_Plugins') . $pluginsUpdateMessage,
                $this->urlForAction('plugins', array('activated' => '')),
                $order = 20);
        }
    }

}
