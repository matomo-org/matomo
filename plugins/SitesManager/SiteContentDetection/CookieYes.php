<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Url;

class CookieYes extends ConsentManagerDetectionAbstract
{
    public static function getName(): string
    {
        return 'CookieYes';
    }

    public static function getInstructionUrl(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/how-to/using-cookieyes-consent-manager-with-matomo');
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $needle = 'cookieyes.com';
        return (strpos($data, $needle) !== false);
    }

    public function checkIsConnected(?string $data = null, ?array $headers = null): bool
    {
        $needle = 'document.addEventListener("cookieyes_consent_update", function (eventData)';
        return (strpos($data, $needle) !== false);
    }
}
