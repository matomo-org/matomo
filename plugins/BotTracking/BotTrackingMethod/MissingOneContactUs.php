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

class MissingOneContactUs extends BotTrackingMethodAbstract
{
    public static function getName(): string
    {
        return Piwik::translate('BotTracking_NoDataMissingOneContactUs');
    }

    public static function getSiteContentDetectionId(): ?string
    {
        return null;
    }

    public static function getPriority(): int
    {
        return 10000;
    }

    public static function getIcon(): ?string
    {
        return './plugins/SitesManager/images/others.svg';
    }

    public static function renderInstructionsTab(): string
    {
        return '';
    }

    public static function getLink(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/support/');
    }
}
