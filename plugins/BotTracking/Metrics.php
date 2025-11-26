<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

class Metrics
{
    public const METRIC_AI_ASSISTANTS_REQUESTS = 'BotTracking_AIAssistantsRequests';
    public const METRIC_AI_ASSISTANTS_ACQUIRED_VISITS = 'BotTracking_AIAssistantsAcquiredVisits';
    public const METRIC_AI_ASSISTANTS_UNIQUE_PAGE_URLS = 'BotTracking_AIAssistantsUniquePageUrls';
    public const METRIC_AI_ASSISTANTS_UNIQUE_DOCUMENT_URLS = 'BotTracking_AIAssistantsUniqueDocumentUrls';
    public const METRIC_AI_ASSISTANTS_UNIQUE_ASSISTANTS = 'BotTracking_AIAssistantsUniqueAssistants';
    public const METRIC_AI_ASSISTANTS_NOT_FOUND_REQUESTS = 'BotTracking_AIAssistantsNotFoundRequests';
    public const METRIC_AI_ASSISTANTS_SERVER_ERROR_REQUESTS = 'BotTracking_AIAssistantsServerErrorRequests';
    public const METRIC_AI_ASSISTANTS_CLICK_THROUGH_RATE = 'BotTracking_AIAssistantsClickThroughRate';
    public const COLUMN_REQUESTS = 'requests';
    public const COLUMN_DOCUMENT_REQUESTS = 'document_requests';
    public const COLUMN_PAGE_REQUESTS = 'page_requests';
    public const COLUMN_ACQUIRED_VISITS = 'visits_acquired';
}
