<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class Klaro extends ConsentManagerDetectionAbstract
{
    public static function getName(): string
    {
        return 'Klaro';
    }

    public static function getInstructionUrl(): ?string
    {
        return 'https://matomo.org/faq/how-to/using-klaro-consent-manager-with-matomo';
    }

    public function detectByContent(?string $data = null, ?array $headers = null): bool
    {
        $needle1 = 'klaro.js';
        $needle2 = 'kiprotect.com';
        return (strpos($data, $needle1) !== false || strpos($data, $needle2) !== false);
    }

    public function checkIsConnected(?string $data = null, ?array $headers = null): bool
    {
        $needle1 = 'KlaroWatcher()';
        $needle2 = "title: 'Matomo',";
        return (strpos($data, $needle1) !== false || strpos($data, $needle2) !== false);
    }
}
