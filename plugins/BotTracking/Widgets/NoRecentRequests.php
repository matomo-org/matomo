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
        self::configureMessageWidget($config, 'BotTracking_AIChatbotsOverview', 'noRecentRequestsMessage');
    }

    /**
     * Shared config for the "no recent AI bot requests" message. It tops the Overview and Content
     * Requests pages as its own widget (see {@see NoRecentRequestsContentRequests}); the
     * showNoRecentRequestsMessage middleware hides it once recent requests exist.
     */
    public static function configureMessageWidget(WidgetConfig $config, string $subcategoryId, string $action): void
    {
        $config
            ->setName('BotTracking_NoRecentRequestsWidgetTitle')
            ->setCategoryId('General_AIAssistants')
            ->setSubcategoryId($subcategoryId)
            ->setModule('BotTracking')
            ->setAction($action)
            ->setClientSideComponent('BotTracking', 'NoRecentRequestsWidget')
            ->setMiddlewareParameters(['module' => 'BotTracking', 'action' => 'showNoRecentRequestsMessage'])
            ->setIsWide()
            ->setOrder(0)
            ->setIsNotWidgetizable();
    }
}
