<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\BotTrackingMethod;

use Piwik\Plugins\SitesManager\SiteContentDetection\AmazonCloudFront as SitesManagerAmazonCloudFront;
use Piwik\View;

class AmazonCloudFront extends BotTrackingMethodAbstract
{
    public static function getName(): string
    {
        return 'Amazon CloudFront';
    }

    public static function getPriority(): int
    {
        return 50;
    }

    public static function getSiteContentDetectionId(): ?string
    {
        return SitesManagerAmazonCloudFront::getId();
    }

    public static function renderInstructionsTab(): string
    {
        $view = new View('@BotTracking/_noDataAmazonCloudFront');
        $view->sendHeadersWhenRendering = false;
        $view->urlKnowledgeBase = 'https://matomo.org/faq/how-to/install-ai-chatbot-tracking';
        $view->urlSourceCode = 'https://github.com/matomo-org/tracker-cloudfront';
        return $view->render();
    }
}
