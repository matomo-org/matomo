<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class Joomla extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Joomla';
    }

    public static function getContentType(): string
    {
        return self::TYPE_CMS;
    }

    public static function getInstructionUrl(): ?string
    {
        return 'https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-analytics-tracking-code-on-joomla/';
    }

    protected function detectSiteByContent(?string $data = null, ?array $headers = null): bool
    {
        // https://github.com/joomla/joomla-cms/blob/staging/libraries/src/Application/WebApplication.php#L516
        // Joomla was the outcome of a fork of Mambo on 17 August 2005 - https://en.wikipedia.org/wiki/Joomla
        if (isset($headers['expires']) && $headers['expires'] === 'Wed, 17 Aug 2005 00:00:00 GMT') {
            return true;
        }

        return false;
    }
}
