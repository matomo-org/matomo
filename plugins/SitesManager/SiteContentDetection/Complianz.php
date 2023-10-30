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

class Complianz extends ConsentManagerDetectionAbstract
{
    public static function getName(): string
    {
        return 'Complianz';
    }

    public static function getInstructionUrl(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/how-to/using-complianz-for-wordpress-consent-manager-with-matomo');
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $needle = 'complianz-gdpr';
        return (strpos($data, $needle) !== false);
    }

    public function checkIsConnected(?string $data = null, ?array $headers = null): bool
    {
        $needle = "if (!cmplz_in_array( 'statistics', consentedCategories )) {
		_paq.push(['forgetCookieConsentGiven']);";
        return (strpos($data, $needle) !== false);
    }
}
