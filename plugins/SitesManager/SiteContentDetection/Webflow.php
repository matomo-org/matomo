<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Url;

class Webflow extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Webflow';
    }

    public static function getContentType(): int
    {
        return self::TYPE_CMS;
    }

    public static function getInstructionUrl(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-webflow');
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $pattern = '/data-wf-(?:domain|page)=/i';
        return (preg_match($pattern, $data) === 1);
    }
}
