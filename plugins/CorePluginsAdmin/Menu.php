<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugins\CorePluginsAdmin\Model\TagManagerTeaser;

class Menu extends \Piwik\Plugin\Menu
{
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

        if (!$isAnonymous) {
            $menu->addPlatformItem('', [], 7);
        }

        if ($hasSuperUserAccess) {
            $menu->addPluginItem(
                Piwik::translate('General_ManagePlugins'),
                $this->urlForAction('plugins', ['activated' => '']),
                10,
                false,
                'manage-plugins'
            );
        }
    }
}
