<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Plugin\Manager;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        if (Piwik::hasUserSuperUserAccess() && Manager::getInstance()->isPluginActivated('Diagnostics')) {
            $menu->addDiagnosticItem('Installation_SystemCheck',
                                   $this->urlForAction('systemCheckPage'),
                                   $order = 1);
        }
    }
}
