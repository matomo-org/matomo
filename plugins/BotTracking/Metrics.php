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
    public const COLUMN_REQUESTS = 'requests';
    public const COLUMN_DOCUMENT_REQUESTS = 'document_requests';
    public const COLUMN_PAGE_REQUESTS = 'page_requests';
    public const COLUMN_ACQUIRED_VISITS = 'visits_acquired';
}
