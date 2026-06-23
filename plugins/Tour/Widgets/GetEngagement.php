<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;
use Piwik\Piwik;

class GetEngagement extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName(Piwik::translate('Tour_BecomeMatomoExpert'));
        $config->setClientSideComponent('Tour', 'BecomeMatomoExpert');
        $config->setOrder(99);

        if (!Piwik::hasUserSuperUserAccess()) {
            $config->disable();
        }
    }
}
