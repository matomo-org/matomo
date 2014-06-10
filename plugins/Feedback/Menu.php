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
        $menu->add(
            'General_Help',
            null,
            array('module' => 'Feedback', 'action' => 'index', 'segment' => false),
            true,
            $order = 99,
            $tooltip = Piwik::translate('Feedback_TopLinkTooltip')
        );
    }
}
