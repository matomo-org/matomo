<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Widgetize;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $tooltip   = Piwik::translate('Widgetize_TopLinkTooltip');
        $urlParams = $this->urlForAction('index', array('segment' => false));

        $menu->addPlatformItem('General_Widgets', $urlParams, 6, $tooltip);
    }

}
