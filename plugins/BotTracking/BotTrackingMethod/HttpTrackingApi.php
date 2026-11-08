<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\BotTrackingMethod;

use Piwik\Piwik;
use Piwik\View;

class HttpTrackingApi extends BotTrackingMethodAbstract
{
    public static function getName(): string
    {
        return Piwik::translate('BotTracking_NoDataHttpTrackingApi');
    }

    public static function getSiteContentDetectionId(): ?string
    {
        return null;
    }

    public static function getPriority(): int
    {
        return 9000;
    }

    public static function getIcon(): ?string
    {
        return './plugins/SitesManager/images/code.svg';
    }

    public static function isOthers(): bool
    {
        return true;
    }

    public static function renderInstructionsTab(): string
    {
        $view = new View('@BotTracking/_noDataHttpTrackingApi');
        $view->sendHeadersWhenRendering = false;
        $view->urlKnowledgeBase = 'https://matomo.org/faq/how-to/install-ai-chatbot-tracking';
        $view->urlApiReference = 'https://developer.matomo.org/api-reference/tracking-api';
        return $view->render();
    }
}
