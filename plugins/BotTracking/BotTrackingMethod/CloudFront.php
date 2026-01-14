<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\BotTrackingMethod;

use Piwik\Plugins\SitesManager\SiteContentDetection\CloudFront as SitesManagerCloudFront;
use Piwik\View;

class CloudFront extends BotTrackingMethodAbstract
{
    public static function getName(): string
    {
        return 'CloudFront';
    }

    public static function getPriority(): int
    {
        return 50;
    }

    public static function getSiteContentDetectionId(): ?string
    {
        return SitesManagerCloudFront::getId();
    }

    public static function renderInstructionsTab(): string
    {
        $view = new View('@BotTracking/_noDataCloudFront');
        $view->sendHeadersWhenRendering = false;
        return $view->render();
    }
}
