<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Marketplace;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{

    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (!Piwik::isUserIsAnonymous()) {
            $menu->addPlatformItem('Marketplace_Marketplace',
                $this->urlForAction('overview', array('activated' => '', 'mode' => 'admin', 'type' => '', 'show' => '')),
                $order = 5);
        }
    }

}
