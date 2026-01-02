<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ScheduledReports;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $tooltip = Piwik::translate(
            \Piwik\Plugin\Manager::getInstance()->isPluginActivated('MobileMessaging')
            ? 'MobileMessaging_TopLinkTooltip' : 'ScheduledReports_TopLinkTooltip'
        );

        $menu->addPersonalItem(
            'ScheduledReports_ScheduleReports',
            $this->urlForAction('index', array('segment' => false)),
            7,
            $tooltip
        );
    }
}
