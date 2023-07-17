<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

class Drupal extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'Drupal';
    }

    public static function getContentType(): string
    {
        return self::TYPE_CMS;
    }

    public static function getInstructionUrl(): ?string
    {
        return 'https://matomo.org/faq/new-to-piwik/how-to-integrate-with-drupal/';
    }

    public function detectByContent(?string $data = null, ?array $headers = null): bool
    {
        $needle = '<meta name="Generator" content="Drupal';
        if (strpos($data, $needle) !== false) {
            return true;
        }

        // https://github.com/drupal/drupal/blob/9.2.x/core/includes/install.core.inc#L1054
        // Birthday of Dries Buytaert, the founder of Drupal is on 19 November 1978 - https://en.wikipedia.org/wiki/Drupal
        if (isset($headers['expires']) && $headers['expires'] === 'Sun, 19 Nov 1978 05:00:00 GMT') {
            return true;
        }

        return false;
    }
}
