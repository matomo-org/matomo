<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

use Piwik\Piwik;
use Piwik\Columns\Dimension;

class Metrics
{
    public const METRIC_AI_CHATBOTS_REQUESTS = 'BotTracking_AIChatbotsRequests';
    public const METRIC_AI_CHATBOTS_ACQUIRED_VISITS = 'BotTracking_AIChatbotsAcquiredVisits';
    public const METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS = 'BotTracking_AIChatbotsUniquePageUrls';
    public const METRIC_AI_CHATBOTS_UNIQUE_DOCUMENT_URLS = 'BotTracking_AIChatbotsUniqueDocumentUrls';
    public const METRIC_AI_CHATBOTS_UNIQUE_CHATBOTS = 'BotTracking_AIChatbotsUniqueChatbots';
    public const METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS = 'BotTracking_AIChatbotsNotFoundRequests';
    public const METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS = 'BotTracking_AIChatbotsServerErrorRequests';
    public const METRIC_AI_CHATBOTS_CLICK_THROUGH_RATE = 'BotTracking_AIChatbotsClickThroughRate';
    public const COLUMN_REQUESTS = 'requests';
    public const COLUMN_DOCUMENT_REQUESTS = 'document_requests';
    public const COLUMN_PAGE_REQUESTS = 'page_requests';
    public const COLUMN_ACQUIRED_VISITS = 'visits_acquired';
    public const COLUMN_AVG_SERVER_TIME = 'avg_server_time';
    public const COLUMN_AVG_RESPONSE_SIZE = 'avg_response_size';
    public const COLUMN_PAGE_NOT_FOUND_404_REQUESTS = 'page_not_found_404_requests';
    public const COLUMN_SERVER_ERROR_5XX_REQUESTS = 'server_error_5xx_requests';
    public const COLUMN_TOTAL_BROKEN_REQUESTS = 'total_broken_requests';

    // Raw (blob-stored) accumulator columns used to compute the above averages at display time.
    public const COLUMN_SUM_SERVER_TIME = 'sum_server_time';
    public const COLUMN_NB_SERVER_TIME = 'nb_server_time';
    public const COLUMN_SUM_RESPONSE_SIZE = 'sum_response_size';
    public const COLUMN_NB_RESPONSE_SIZE = 'nb_response_size';


    // -----------------------------------------------------------------------------------------
    // Legacy translation/doc/semantic maps — AI Chatbots Overview numerics only.
    // New per-column metrics (COLUMN_AVG_*, COLUMN_*_5XX_REQUESTS, COLUMN_TOTAL_BROKEN_REQUESTS, …)
    // live on their Metric classes — do NOT add them here.
    // -----------------------------------------------------------------------------------------

    /**
     * Metrics displayed in the reports (including derived metrics).
     *
     * @return string[]
     */
    public static function getReportMetricColumns(): array
    {
        return [
            self::METRIC_AI_CHATBOTS_REQUESTS,
            self::METRIC_AI_CHATBOTS_ACQUIRED_VISITS,
            self::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS,
            self::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS,
            self::METRIC_AI_CHATBOTS_UNIQUE_CHATBOTS,
            self::METRIC_AI_CHATBOTS_UNIQUE_DOCUMENT_URLS,
            self::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS,
        ];
    }

    /**
     * Ordered list used for sparklines & graph selectors.
     *
     * @return string[]
     */
    public static function getSparklineMetricOrder(): array
    {
        return [
            self::METRIC_AI_CHATBOTS_REQUESTS,
            self::METRIC_AI_CHATBOTS_ACQUIRED_VISITS,
            self::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS,
            self::METRIC_AI_CHATBOTS_UNIQUE_DOCUMENT_URLS,
            self::METRIC_AI_CHATBOTS_CLICK_THROUGH_RATE,
            self::METRIC_AI_CHATBOTS_UNIQUE_CHATBOTS,
            self::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS,
            self::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getMetricTranslations(): array
    {
        return [
            self::METRIC_AI_CHATBOTS_REQUESTS              => Piwik::translate('BotTracking_ColumnRequests'),
            self::METRIC_AI_CHATBOTS_ACQUIRED_VISITS       => Piwik::translate('BotTracking_ColumnAcquiredVisits'),
            self::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS      => Piwik::translate('BotTracking_ColumnUniquePageUrls'),
            self::METRIC_AI_CHATBOTS_UNIQUE_DOCUMENT_URLS  => Piwik::translate('BotTracking_ColumnUniqueDocumentUrls'),
            self::METRIC_AI_CHATBOTS_UNIQUE_CHATBOTS       => Piwik::translate('BotTracking_ColumnUniqueAiChatbots'),
            self::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS    => Piwik::translate('BotTracking_ColumnNotFoundRequests'),
            self::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS => Piwik::translate('BotTracking_ColumnServerErrorRequests'),
            self::METRIC_AI_CHATBOTS_CLICK_THROUGH_RATE    => Piwik::translate('BotTracking_ColumnClickThroughRate'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getMetricDocumentation(): array
    {
        return [
            self::METRIC_AI_CHATBOTS_REQUESTS              => Piwik::translate('BotTracking_ColumnRequestsDocumentation'),
            self::METRIC_AI_CHATBOTS_ACQUIRED_VISITS       => Piwik::translate('BotTracking_ColumnAcquiredVisitsDocumentation'),
            self::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS      => Piwik::translate('BotTracking_ColumnUniquePageUrlsDocumentation'),
            self::METRIC_AI_CHATBOTS_UNIQUE_DOCUMENT_URLS  => Piwik::translate('BotTracking_ColumnUniqueDocumentUrlsDocumentation'),
            self::METRIC_AI_CHATBOTS_UNIQUE_CHATBOTS       => Piwik::translate('BotTracking_ColumnUniqueAiChatbotsDocumentation'),
            self::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS    => Piwik::translate('BotTracking_ColumnNotFoundRequestsDocumentation'),
            self::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS => Piwik::translate('BotTracking_ColumnServerErrorRequestsDocumentation'),
            self::METRIC_AI_CHATBOTS_CLICK_THROUGH_RATE    => Piwik::translate('BotTracking_ColumnClickThroughRateDocumentation'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getMetricSemanticTypes(): array
    {
        return [
            self::METRIC_AI_CHATBOTS_REQUESTS              => Dimension::TYPE_NUMBER,
            self::METRIC_AI_CHATBOTS_ACQUIRED_VISITS       => Dimension::TYPE_NUMBER,
            self::METRIC_AI_CHATBOTS_UNIQUE_PAGE_URLS      => Dimension::TYPE_NUMBER,
            self::METRIC_AI_CHATBOTS_UNIQUE_DOCUMENT_URLS  => Dimension::TYPE_NUMBER,
            self::METRIC_AI_CHATBOTS_UNIQUE_CHATBOTS       => Dimension::TYPE_NUMBER,
            self::METRIC_AI_CHATBOTS_NOT_FOUND_REQUESTS    => Dimension::TYPE_NUMBER,
            self::METRIC_AI_CHATBOTS_SERVER_ERROR_REQUESTS => Dimension::TYPE_NUMBER,
            self::METRIC_AI_CHATBOTS_CLICK_THROUGH_RATE    => Dimension::TYPE_PERCENT,
        ];
    }
}
