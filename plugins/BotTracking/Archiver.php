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

    public static function shouldRunEvenWhenNoVisits(): bool
    {
        return true;
    }
}
