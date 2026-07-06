<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Reports;

final class AIChatbotsRealTimeWidgets
{
    public const LOOKBACK_30_MINUTES = 30;
    public const LOOKBACK_480_MINUTES = 480; // 8 hours

    /**
     * @return array<int, array{name: string, documentation: string, lastMinutes: int, order: int}>
     */
    public static function getChatbotWidgets(): array
    {
        return [
            [
                'name'          => 'BotTracking_AIChatbotsLast30MinutesTitle',
                'documentation' => 'BotTracking_AIChatbotsLast30MinutesDocumentation',
                'lastMinutes'   => self::LOOKBACK_30_MINUTES,
                'order'         => 10,
            ],
            [
                'name'          => 'BotTracking_AIChatbotsLast8HoursTitle',
                'documentation' => 'BotTracking_AIChatbotsLast8HoursDocumentation',
                'lastMinutes'   => self::LOOKBACK_480_MINUTES,
                'order'         => 20,
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, documentation: string, lastMinutes: int, order: int}>
     */
    public static function getTopPageUrlWidgets(): array
    {
        return [
            [
                'name'          => 'BotTracking_TopPageUrlsLast30MinutesTitle',
                'documentation' => 'BotTracking_TopPageUrlsLast30MinutesDocumentation',
                'lastMinutes'   => self::LOOKBACK_30_MINUTES,
                'order'         => 30,
            ],
            [
                'name'          => 'BotTracking_TopPageUrlsLast8HoursTitle',
                'documentation' => 'BotTracking_TopPageUrlsLast8HoursDocumentation',
                'lastMinutes'   => self::LOOKBACK_480_MINUTES,
                'order'         => 40,
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, documentation: string, lastMinutes: int, order: int}>
     */
    public static function getAllWidgets(): array
    {
        return array_merge(self::getChatbotWidgets(), self::getTopPageUrlWidgets());
    }
}
