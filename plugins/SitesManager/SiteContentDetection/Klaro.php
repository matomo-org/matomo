<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Url;

class Klaro extends ConsentManagerDetectionAbstract
{
    public static function getName(): string
    {
        return 'Klaro';
    }

    public static function getInstructionUrl(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/how-to/using-klaro-consent-manager-with-matomo');
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $needle1 = 'klaro.js';
        $needle2 = 'kiprotect.com';
        return (str_contains($data, $needle1) || str_contains($data, $needle2));
    }

    public function checkIsConnected(?string $data = null, ?array $headers = null): bool
    {
        $needle1 = 'KlaroWatcher()';
        $needle2 = "title: 'Matomo',";
        return (str_contains($data, $needle1) || str_contains($data, $needle2));
    }
}
