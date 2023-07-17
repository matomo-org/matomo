<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager\SiteContentDetection;

use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Url;
use Piwik\View;

class GoogleTagManager extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return Piwik::translate('SitesManager_SiteWithoutDataGoogleTagManager');
    }

    public static function getContentType(): string
    {
        return self::TYPE_TRACKER;
    }

    public static function getInstructionUrl(): ?string
    {
        return 'https://matomo.org/faq/new-to-piwik/how-do-i-use-matomo-analytics-within-gtm-google-tag-manager/';
    }

    public static function getPriority(): int
    {
        return 20;
    }

    public function detectByContent(?string $data = null, ?array $headers = null): bool
    {
        $needle = 'gtm.start';

        if (strpos($data, $needle) !== false) {
            return true;
        }

        if (strpos($data, 'gtm.js') !== false) {
            return true;
        }

        $tests = ["/googletagmanager/i"];
        foreach ($tests as $test) {
            if (preg_match($test, $data) === 1) {
                return true;
            }
        }

        return false;
    }

    public function shouldHighlightTabIfShown(): bool
    {
        return true;
    }

    public function renderInstructionsTab(array $detections = []): string
    {
        $piwikUrl = Url::getCurrentUrlWithoutFileName();
        $jsTag = Request::processRequest(
            'SitesManager.getJavascriptTag',
            [
                'idSite' => \Piwik\Request::fromRequest()->getIntegerParameter('idSite'),
                'piwikUrl' => $piwikUrl
            ]
        );
        $view = new View('@SitesManager/_gtmTabInstructions');
        $view->jsTag = $jsTag;
        $view->sendHeadersWhenRendering = false;
        return $view->render();
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
