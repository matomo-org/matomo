<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\FeatureFlags;

use Piwik\Plugins\FeatureFlags\FeatureFlagInterface;

/**
 * Feature flag that gates the three AI Chatbots Content Requests reports
 * (Pages, Documents, Broken Pages and Documents).
 *
 * When disabled (the default in production), the reports are hidden from every
 * UI surface (menu, widget list, Custom Alerts, Scheduled Reports) and direct
 * API calls return "Report not enabled". Archiving is NOT gated — the three
 * blob records are written regardless so historical data is ready the moment
 * this flag is flipped on.
 *
 * Enable via config/config.ini.php:
 *   [FeatureFlags]
 *   AIChatbotsContentReports_feature = enabled
 */
class AIChatbotsContentReports implements FeatureFlagInterface
{
    public function getName(): string
    {
        return 'AIChatbotsContentReports';
    }
}
