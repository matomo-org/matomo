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
    public const AI_ASSISTANTS_PAGES_RECORD = 'BotTracking_AIAssistantsPages';
    public const AI_ASSISTANTS_DOCUMENTS_RECORD = 'BotTracking_AIAssistantsDocuments';
    public const AI_ASSISTANTS_REQUESTED_PAGES_RECORD = 'BotTracking_AIAssistantsRequestedPages';
    public const AI_ASSISTANTS_REQUESTED_DOCUMENTS_RECORD = 'BotTracking_AIAssistantsRequestedDocuments';

    public static function shouldRunEvenWhenNoVisits(): bool
    {
        return true;
    }
}
