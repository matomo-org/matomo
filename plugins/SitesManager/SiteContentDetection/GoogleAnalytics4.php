<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class GoogleAnalytics4 extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Google Analytics 4';
    }

    public static function getContentType(): string
    {
        return self::TYPE_TRACKER;
    }

    public function detectByContent(?string $data = null, ?array $headers = null): bool
    {
        if (empty($data)) {
            return false;
        }

        if (strpos($data, 'gtag.js') !== false) {
            return true;
        }

        $tests = ["/properties\/[^\/]/", "/G-[A-Z0-9]{7,10}/", "/gtag\/js\?id=G-/"];
        foreach ($tests as $test) {
            if (preg_match($test, $data) === 1) {
                return true;
            }
        }

        return false;
    }
}
