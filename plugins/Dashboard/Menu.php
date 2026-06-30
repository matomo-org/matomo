<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Dashboard;

use Piwik\Category\Category;
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

        $params = $this->urlForModuleActionWithDefaultUserParams('CoreHome', 'index', ['idSite' => $idSite]);
        if (empty($params)) {
            return;
        }

        // Opens within the reporting SPA like every other section, so switching back to Analytics does
        // not reload the page. data-reporting-group (empty for the default section) syncs the highlight.
        $url = $this->urlForReportingSection($params, Category::DEFAULT_GROUP);

        $menu->addItem(
            'Dashboard_TopMenuTitle',
            null,
            $url,
            1,
            $tooltip,
            false,
            false,
            'data-reporting-group=""'
        );
    }
}
