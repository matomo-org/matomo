<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Feedback;

use Piwik\Menu\MenuUser;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureUserMenu(MenuUser $menu)
    {
        $menu->addPlatformItem(
            'General_Help',
            $this->urlForAction('index', array('segment' => false)),
            $order = 99,
            $tooltip = Piwik::translate('Feedback_TopLinkTooltip')
        );
    }
}
