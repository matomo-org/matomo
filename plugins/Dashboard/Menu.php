<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Dashboard;

use Piwik\Common;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Site;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        $userPreferences = new UserPreferences();
        $idSite = $userPreferences->getDefaultWebsiteId();
        $idSite = Common::getRequestVar('idSite', $idSite, 'int');

        $tooltip = Piwik::translate('Dashboard_TopLinkTooltip', Site::getNameFor($idSite));

        $urlParams = $this->urlForModuleActionWithDefaultUserParams('CoreHome', 'index', ['idSite' => $idSite]);

        // data-reporting-group marks this as the default ("Analytics") reporting section so the active
        // top-menu highlight can be kept in sync client-side with the active section (which lives in the
        // URL hash, not the query string, to avoid leaking into other links).
        $menu->addItem(
            'Dashboard_TopMenuTitle',
            null,
            $urlParams,
            1,
            $tooltip,
            false,
            false,
            'data-reporting-group=""'
        );
    }
}
