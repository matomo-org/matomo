<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class Squarespace extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Squarespace';
    }

    public static function getContentType(): int
    {
        return self::TYPE_CMS;
    }

    public static function getInstructionUrl(): ?string
    {
        return 'https://matomo.org/faq/new-to-piwik/how-do-i-integrate-matomo-with-squarespace-website/';
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $needle = '<!-- This is Squarespace. -->';
        return (strpos($data, $needle) !== false);
    }
}
