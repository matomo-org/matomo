<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleUI;

use Piwik\Menu\MenuAdmin;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $menu->addPlatformItem('ExampleUI_UiNotifications', $this->urlForAction('notifications'), $order = 10);
    }
}
