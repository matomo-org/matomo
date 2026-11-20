<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class GoogleAnalytics3 extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Google Analytics 3';
    }

    public static function getContentType(): int
    {
        return self::TYPE_TRACKER;
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        if (empty($data)) {
            return false;
        }

        if (str_contains($data, '(i,s,o,g,r,a,m)')) {
            return true;
        }

        $tests = [
            "/UA-\d{5,}-\d{1,}/", "/google\-analytics\.com\/analytics\.js/", "/window\.ga\s?=\s?window\.ga/",
            "/google[ _\-]{0,1}analytics/i",
        ];

        foreach ($tests as $test) {
            if (preg_match($test, $data) === 1) {
                return true;
            }
        }
        return false;
    }
}
