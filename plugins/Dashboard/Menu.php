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
use Piwik\Url;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        $userPreferences = new UserPreferences();
        $idSite = $userPreferences->getDefaultWebsiteId();
        $idSite = Common::getRequestVar('idSite', $idSite, 'int');

        $tooltip = Piwik::translate('Dashboard_TopLinkTooltip', Site::getNameFor($idSite));

        $params = $this->urlForModuleActionWithDefaultUserParams('CoreHome', 'index', ['idSite' => $idSite]);

        // The Analytics entry carries the (default) reporting section in the URL hash, just like the
        // other section entries (e.g. "AI Insights"). This keeps switching back to Analytics within the
        // reporting single-page-app instead of triggering a full page reload, so navigating between
        // sections behaves the same in both directions. The section must live in the hash - not the
        // query string - so it does not leak into other top-menu links; data-reporting-group lets the
        // active highlight be synced client-side (the server cannot read the hash).
        $hashParams = array_merge(
            array_intersect_key($params, array_flip(['idSite', 'period', 'date'])),
            ['group' => Category::DEFAULT_GROUP]
        );

        $url = 'index.php?' . Url::getQueryStringFromParameters($params)
            . '#?' . Url::getQueryStringFromParameters($hashParams);

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
