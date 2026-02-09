<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

class Archiver extends \Piwik\Plugin\Archiver
{
    public const AI_CHATBOTS_PAGES_RECORD = 'BotTracking_AIChatbotsPages';
    public const AI_CHATBOTS_DOCUMENTS_RECORD = 'BotTracking_AIChatbotsDocuments';
    public const AI_CHATBOTS_REQUESTED_PAGES_RECORD = 'BotTracking_AIChatbotsRequestedPages';
    public const AI_CHATBOTS_REQUESTED_DOCUMENTS_RECORD = 'BotTracking_AIChatbotsRequestedDocuments';

    public static function shouldRunEvenWhenNoVisits(): bool
    {
        return true;
    }
}
