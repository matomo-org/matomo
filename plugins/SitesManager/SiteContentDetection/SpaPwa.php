<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Piwik;

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

    public function detectSiteByContent(?string $data = null, ?array $headers = null): bool
    {
        return false;
    }

    public function shouldShowInstructionTab(array $detections = []): bool
    {
        return true;
    }

    public function renderInstructionsTab(array $detections = []): string
    {
        return '';
    }

    public function renderOthersInstruction(): string
    {
        return sprintf(
            '<p>%s</p>',
            Piwik::translate(
                'SitesManager_SiteWithoutDataSinglePageApplicationDescription',
                [
                    '<a target="_blank" rel="noreferrer noopener" href="https://developer.matomo.org/guides/spa-tracking">',
                    '</a>',
                ]
            )
        );
    }
}
