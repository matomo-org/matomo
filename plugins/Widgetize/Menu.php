<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Widgetize;

use Piwik\Menu\MenuUser;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureUserMenu(MenuUser $menu)
    {
        $tooltip   = Piwik::translate('Widgetize_TopLinkTooltip');
        $urlParams = $this->urlForAction('index', array('segment' => false));

        $menu->addPlatformItem(null, $urlParams, 50, $tooltip);
        $menu->addPlatformItem('General_Widgets', $urlParams, 5, $tooltip);
    }

}
