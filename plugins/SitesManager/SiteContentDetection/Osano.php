<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Url;

class Osano extends ConsentManagerDetectionAbstract
{
    public static function getName(): string
    {
        return 'Osano';
    }

    public static function getInstructionUrl(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/how-to/using-osano-consent-manager-with-matomo');
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $needle = 'osano.com';
        return (strpos($data, $needle) !== false);
    }

    public function checkIsConnected(?string $data = null, ?array $headers = null): bool
    {
        $needle = "Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });";
        return (strpos($data, $needle) !== false);
    }
}
