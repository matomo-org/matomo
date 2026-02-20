<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\BotTrackingMethod;

use Piwik\Plugins\SitesManager\SiteContentDetection\WordPress as SitesManagerWordPress;
use Piwik\View;

class WordPress extends BotTrackingMethodAbstract
{
    public static function getName(): string
    {
        return 'WordPress';
    }

    public static function getPriority(): int
    {
        return 30;
    }

    public static function getSiteContentDetectionId(): ?string
    {
        return SitesManagerWordPress::getId();
    }

    public static function renderInstructionsTab(): string
    {
        $view = new View('@BotTracking/_noDataWordPress');
        $view->sendHeadersWhenRendering = false;
        $view->urlKnowledgeBase = 'https://matomo.org/faq/how-to/install-ai-chatbot-tracking-wordpress';
        $view->urlSourceCode = 'https://github.com/matomo-org/matomo-for-wordpress';
        return $view->render();
    }
}
