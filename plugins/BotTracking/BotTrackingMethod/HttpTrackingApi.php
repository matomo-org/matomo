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
use Piwik\Url;

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
        $knowledgeBaseLink = Url::getExternalLinkTag('https://matomo.org/docs/tracking-api/');
        $referenceLink     = Url::getExternalLinkTag('https://developer.matomo.org/api-reference/tracking-api');

        $description = Piwik::translate(
            'BotTracking_NoDataHttpTrackingApiDescription',
            [$knowledgeBaseLink, '</a>', $referenceLink, '</a>']
        );

        return '<p>' . $description . '</p>';
    }
}
