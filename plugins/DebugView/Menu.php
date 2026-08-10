<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        // deliberately shown even when the Live plugin or the visits log is
        // disabled: the page itself then explains why Debug View cannot be
        // used, which beats silently hiding that the feature exists
        if (
            !Piwik::isUserIsAnonymous()
            && Piwik::isUserHasSomeViewAccess()
        ) {
            $menu->addDiagnosticItem('DebugView_DebugView', $this->urlForAction('index'), $orderId = 20);
        }
    }
}
