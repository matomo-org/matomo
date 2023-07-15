<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

// Note: tarte au citron pro is configured server side so we cannot tell if it has been connected by
// crawling the website, however setup of Matomo with the pro version is automatic, so displaying the guide
// link for pro isn't necessary. Only the open source version is detected by this definition.
class TarteAuCitron extends ConsentManagerDetectionAbstract
{
    public static function getName(): string
    {
        return 'Tarte au Citron';
    }

    public static function getInstructionUrl(): ?string
    {
        return 'https://matomo.org/faq/how-to/using-tarte-au-citron-consent-manager-with-matomo';
    }

    protected function detectSiteByContent(?string $data = null, ?array $headers = null): bool
    {
        $needle = 'tarteaucitron.js';
        return (strpos($data, $needle) !== false);
    }

    public function checkIsConnected(?string $data = null, ?array $headers = null): bool
    {
        $needle = 'tarteaucitron.user.matomoHost';
        return (strpos($data, $needle) !== false);
    }
}
