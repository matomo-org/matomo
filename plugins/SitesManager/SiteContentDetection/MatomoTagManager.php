<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\Piwik;
use Piwik\SiteContentDetector;
use Piwik\Url;

class MatomoTagManager extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return Piwik::translate('SitesManager_SiteWithoutDataMatomoTagManager');
    }

    public static function getIcon(): string
    {
        return './plugins/SitesManager/images/mtm.png';
    }

    public static function getContentType(): int
    {
        return self::TYPE_TRACKER;
    }

    public static function getPriority(): int
    {
        return 10;
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $tests = ['/matomo ?tag ?manager/i', '/_mtm\.push/'];
        foreach ($tests as $test) {
            if (preg_match($test, $data) === 1) {
                return true;
            }
        }

        return false;
    }

    public function getRecommendationDetails(SiteContentDetector $detector): array
    {
        $details = parent::getRecommendationDetails($detector);
        if (!$detector->wasDetected(self::getId())) {
            $details['text'] = Piwik::translate('SitesManager_SetupMatomoTracker');
        }
        return $details;
    }

    public function renderInstructionsTab(SiteContentDetector $detector): string
    {
        return '<h3>' . Piwik::translate('SitesManager_SiteWithoutDataMatomoTagManager') . '</h3>
            <p>' . Piwik::translate(
            'SitesManager_SiteWithoutDataMatomoTagManagerNotActive',
            ['<a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/tag-manager/') . '" rel="noreferrer noopener" target="_blank">', '</a>']
        ) . '</p>';
    }
}
