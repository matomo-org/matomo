<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\BotTrackingMethod;

use Piwik\Plugins\SitesManager\SiteContentDetection\Cloudflare as SitesManagerCloudflare;
use Piwik\View;

class Cloudflare extends BotTrackingMethodAbstract
{
    public static function getName(): string
    {
        return 'Cloudflare';
    }

    public static function getPriority(): int
    {
        return 40;
    }

    public static function getSiteContentDetectionId(): ?string
    {
        return SitesManagerCloudflare::getId();
    }

    public static function renderInstructionsTab(): string
    {
        $view = new View('@BotTracking/_noDataCloudflare');
        $view->sendHeadersWhenRendering = false;
        $view->urlKnowledgeBase = 'https://matomo.org/faq/how-to/install-ai-chatbot-tracking';
        $view->urlSourceCode = 'https://github.com/matomo-org/tracker-cloudflare';
        return $view->render();
    }
}
