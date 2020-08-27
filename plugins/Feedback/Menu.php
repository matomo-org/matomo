<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Feedback;

use Piwik\Menu\MenuTop;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureTopMenu(MenuTop $menu)
    {
        $menu->registerMenuIcon('General_Help', 'icon-info2');
        $menu->addItem('General_Help', null, array('module' => 'Feedback', 'action' => 'index'), $order = 990, Piwik::translate('General_Help'));
    }
}
