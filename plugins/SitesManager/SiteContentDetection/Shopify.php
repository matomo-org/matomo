<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Url;

class Shopify extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Shopify';
    }

    public static function getContentType(): int
    {
        return self::TYPE_CMS;
    }

    public static function getInstructionUrl(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-my-shopify-store/');
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $needle = 'Shopify.theme';
        return (strpos($data, $needle) !== false);
    }
}
