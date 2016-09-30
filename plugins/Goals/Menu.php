<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals;

use Piwik\Common;
use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UserPreferences;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $userPreferences = new UserPreferences();
        $idSite = $this->getIdSite($userPreferences->getDefaultWebsiteId());

        if (Piwik::isUserHasAdminAccess($idSite)) {
            $menu->addMeasurableItem('Goals_Goals', $this->urlForAction('manage', array('idSite' => $idSite)), 40);
        }
    }

    private function getIdSite($default = null)
    {
        $idSite = Common::getRequestVar('idSite', $default, 'int');
        return $idSite;
    }

}
