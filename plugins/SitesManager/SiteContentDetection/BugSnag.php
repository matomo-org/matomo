<?php

declare(strict_types=1);

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class BugSnag extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'BugSnag';
    }

    public static function getContentType(): int
    {
        return self::TYPE_JS_CRASH_ANALYTICS;
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        return false !== stripos($data, 'bugsnag.min.js');
    }
}
