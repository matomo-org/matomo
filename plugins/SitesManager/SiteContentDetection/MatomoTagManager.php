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

class MatomoTagManager extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return Piwik::translate('SitesManager_SiteWithoutDataMatomoTagManager');
    }

    public static function getContentType(): string
    {
        return self::TYPE_TRACKER;
    }

    public static function getPriority(): int
    {
        return 10;
    }

    public function detectSiteByContent(?string $data = null, ?array $headers = null): bool
    {
        $tests = ['/matomo ?tag ?manager/i', '/_mtm\.push/'];
        foreach ($tests as $test) {
            if (preg_match($test, $data) === 1) {
                return true;
            }
        }

        return false;
    }

    public function shouldShowInstructionTab(array $detections = []): bool
    {
        return true;
    }

    public function renderInstructionsTab(array $detections = []): string
    {
        return '<h3>' . Piwik::translate('SitesManager_SiteWithoutDataMatomoTagManager') . '</h3>
            <p>' . Piwik::translate( 'SitesManager_SiteWithoutDataMatomoTagManagerNotActive', ['<a href="https://matomo.org/docs/tag-manager/" rel="noreferrer noopener" target="_blank">', '</a>']) . '</p>';
    }

    public function renderOthersInstruction(): string
    {
        return sprintf(
            '<p>%s</p>',
            Piwik::translate(
                'SitesManager_SiteWithoutDataGoogleTagManagerDescription',
                [
                    '<a target="_blank" rel="noreferrer noopener" href="https://matomo.org/faq/new-to-piwik/how-do-i-use-matomo-analytics-within-gtm-google-tag-manager">',
                    '</a>'
                ]
            )
        );
    }
}
