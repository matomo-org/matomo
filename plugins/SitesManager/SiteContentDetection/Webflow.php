<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class Webflow extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Webflow';
    }

    public static function getContentType(): string
    {
        return self::TYPE_CMS;
    }

    public static function getInstructionUrl(): ?string
    {
        return 'https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-webflow';
    }

    protected function detectSiteByContent(?string $data = null, ?array $headers = null): bool
    {
        $pattern = '/data-wf-(?:domain|page)=/i';
        return (preg_match($pattern, $data) === 1);
    }
}
