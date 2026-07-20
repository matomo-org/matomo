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

/**
 * Places the "no recent AI bot requests" message on the Content Requests page too, so it matches the
 * Overview when nothing has been tracked recently. A distinct action gives it its own widget id.
 */
class NoRecentRequestsContentRequests extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        NoRecentRequests::configureMessageWidget(
            $config,
            'BotTracking_AIChatbotsContentRequests',
            'noRecentRequestsMessageContentRequests'
        );
    }
}
