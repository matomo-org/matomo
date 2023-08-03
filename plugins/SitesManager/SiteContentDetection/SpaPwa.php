<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\SiteContentDetector;

class SpaPwa extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return 'SPA / PWA';
    }

    public static function getContentType(): string
    {
        return self::TYPE_JS_FRAMEWORK;
    }

    public static function getPriority(): int
    {
        return 70;
    }

    public function detectByContent(?string $data = null, ?array $headers = null): bool
    {
        return false;
    }

    public function shouldShowInstructionTab(SiteContentDetector $detector = null): bool
    {
        return true;
    }

    public function renderInstructionsTab(SiteContentDetector $detector = null): string
    {
        return '';
    }
}
