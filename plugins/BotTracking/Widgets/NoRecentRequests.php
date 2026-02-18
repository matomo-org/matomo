<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Widgets;

use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class NoRecentRequests extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config
            ->setName('BotTracking_NoRecentRequestsWidgetTitle')
            ->setCategoryId('General_AIAssistants')
            ->setSubcategoryId('BotTracking_AIChatbotsOverview')
            ->setModule('BotTracking')
            ->setAction('noRecentRequestsMessage')
            ->setMiddlewareParameters(['module' => 'BotTracking', 'action' => 'showNoRecentRequestsMessage'])
            ->setIsWide()
            ->setOrder(0)
            ->setIsNotWidgetizable();
    }
}
