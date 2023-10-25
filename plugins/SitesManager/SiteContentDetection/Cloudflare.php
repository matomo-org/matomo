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
use Piwik\Request;
use Piwik\SiteContentDetector;
use Piwik\Url;
use Piwik\View;

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

    public static function getInstructionUrl(): ?string
    {
        return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-my-cloudflare-setup/');
    }

    public static function getPriority(): int
    {
        return 40;
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

    public function renderInstructionsTab(SiteContentDetector $detector): string
    {
        $view     = new View("@SitesManager/_cloudflareTabInstructions");
        $view->idSite = Request::fromRequest()->getIntegerParameter('idSite');
        $view->wasDetected = $detector->wasDetected(self::getId());
        $view->sendHeadersWhenRendering = false;
        return $view->render();
    }

    public function renderOthersInstruction(SiteContentDetector $detector): string
    {
        if ($detector->wasDetected(self::getId())) {
            return ''; // don't show on others page if tab is being displayed
        }

        return sprintf(
            '<p>%s</p>',
            Piwik::translate(
                'SitesManager_SiteWithoutDataCloudflareDescription',
                [
                    '<a target="_blank" rel="noreferrer noopener" href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/new-to-piwik/how-do-i-install-the-matomo-tracking-code-on-my-cloudflare-setup/') . '">',
                    '</a>'
                ]
            )
        );
    }
}
