<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class Cloudflare extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Cloudflare';
    }

    public static function getIcon(): string
    {
        return './plugins/SitesManager/images/cloudflare.svg';
    }

    public static function getContentType(): int
    {
        return self::TYPE_CMS;
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        return (
            (!empty($headers['server']) && stripos($headers['server'], 'cloudflare') !== false) ||
            (!empty($headers['Server']) && stripos($headers['Server'], 'cloudflare') !== false) ||
            (!empty($headers['SERVER']) && stripos($headers['SERVER'], 'cloudflare') !== false) ||
            !empty($headers['cf-ray']) ||
            !empty($headers['Cf-Ray']) ||
            !empty($headers['CF-RAY'])
        );
    }
}
