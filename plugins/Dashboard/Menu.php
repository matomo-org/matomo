<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Dashboard;

use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Site;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        $userPreferences = new UserPreferences();
        $idSite = $userPreferences->getDefaultWebsiteId();
        $tooltip = Piwik::translate('Dashboard_TopLinkTooltip', Site::getNameFor($idSite));

        $urlParams = $this->urlForModuleActionWithDefaultUserParams('CoreHome', 'index') ;
        $menu->addItem('Dashboard_Dashboard', null, $urlParams, 1, $tooltip);
    }
}

