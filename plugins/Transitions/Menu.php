<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Request;

class Menu extends \Piwik\Plugin\Menu
{
    /**
     * Adds the admin page of the pre-processed pages for admins of the selected website.
     *
     * @return void
     */
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $idSite = Request::fromRequest()->getIntegerParameter('idSite', 0);
        if ($idSite < 1) {
            $idSite = (int) (new UserPreferences())->getDefaultWebsiteId();
        }

        if ($idSite > 0 && Piwik::isUserHasAdminAccess($idSite)) {
            $menu->addMeasurableItem('Transitions_PreprocessedTransitions', $this->urlForAction('manage'), 42);
        }
    }
}
