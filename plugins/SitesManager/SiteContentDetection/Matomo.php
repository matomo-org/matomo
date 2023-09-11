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
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Site;
use Piwik\SiteContentDetector;
use Piwik\Translation\Translator;
use Piwik\Url;
use Piwik\View;

class Matomo extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return Piwik::translate('CoreAdminHome_TrackingCode');
    }

    public static function getContentType(): string
    {
        return self::TYPE_TRACKER;
    }

    public static function getPriority(): int
    {
        return 1;
    }

    public function isDetected(?string $data = null, ?array $headers = null): bool
    {
        $tests = ['/matomo\.js/i', '/piwik\.js/i', '/_paq\.push/i'];
        foreach ($tests as $test) {
            if (preg_match($test, $data) === 1) {
                return true;
            }
        }

        return false;
    }

    public function shouldShowInstructionTab(SiteContentDetector $detector = null): bool
    {
        return true;
    }

    public function renderInstructionsTab(SiteContentDetector $detector): string
    {
        $view = new View('@SitesManager/_matomoTabInstructions');
        $dntChecker = new DoNotTrackHeaderChecker();
        $maxCustomVariables = 0;
        $matomoUrl = Url::getCurrentUrlWithoutFileName();
        $idSite = \Piwik\Request::fromRequest()->getIntegerParameter('idSite');
        $jsTag = Request::processRequest('SitesManager.getJavascriptTag', ['idSite' => $idSite, 'piwikUrl' => $matomoUrl]);

        if (Manager::getInstance()->isPluginActivated('CustomVariables')) {
            $maxCustomVariables = CustomVariables::getNumUsableCustomVariables();
        }

        $view->jsTag = $jsTag;
        $view->isJsTrackerInstallCheckAvailable = Manager::getInstance()->isPluginActivated('JsTrackerInstallCheck');
        $view->serverSideDoNotTrackEnabled = $dntChecker->isActive();
        $view->maxCustomVariables = $maxCustomVariables;
        $view->defaultSiteDecoded = [
            'id' => $idSite,
            'name' => Common::unsanitizeInputValue(Site::getNameFor($idSite)),
        ];
        $view->assign($this->getNotification($detector));
        return $view->render();
    }

    private function getNotification(SiteContentDetector $detector = null): array
    {
        if (empty($detector)) {
            return [];
        }

        $isNotificationsMerged = false;
        $consentManagerName = null;
        $bannerMessage = '';
        $guides = [];
        $message = [];
        $ga3Used = $detector->wasDetected(GoogleAnalytics3::getId());
        $ga4Used = $detector->wasDetected(GoogleAnalytics4::getId());

        if ($ga3Used || $ga4Used) {
            $message[0] = 'Google Analytics ';
            $ga3GuideUrl =  '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-3-to-matomo/" target="_blank" rel="noreferrer noopener">Google Analytics 3</a>';
            $ga4GuideUrl =  '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-4-to-matomo/" target="_blank" rel="noreferrer noopener">Google Analytics 4</a>';
            if ($ga3Used && $ga4Used) {
                $isNotificationsMerged = true;
                $guides[] = $ga3GuideUrl;
                $guides[] = $ga4GuideUrl;
                $message[0] .= '3 & 4';
            } else {
                $message[0] .= ($ga3Used ? 3 : 4);
                $guides[] = ($ga3Used ? $ga3GuideUrl : $ga4GuideUrl);
            }
        }

        $consentManagers = $detector->getDetectsByType(SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER);
        if (!empty($consentManagers)) {
            $consentManagerId = reset($consentManagers);
            $consentManager = $detector->getSiteContentDetectionById($consentManagerId);
            $consentManagerName = $consentManager::getName();
            $consentManagerUrl = $consentManager::getInstructionUrl();
            $consentManagerIsConnected = in_array($consentManagerId, $detector->connectedConsentManagers);
        }

        if (!empty($message) && $consentManagerName) {
            $isNotificationsMerged = true;
            $message[] = $consentManagerName;
            $guides[] =  '<a href="' . $consentManagerUrl . '" target="_blank" rel="noreferrer noopener">' . $consentManagerName . '</a>';
        }

        if (!empty($message)) {
            $bannerMessage = StaticContainer::get(Translator::class)->createAndListing($message);
        }

        if ($isNotificationsMerged && $bannerMessage) {
            $info = [
                'isNotificationsMerged' => true,
                'notificationMessage' => '<p class="fw-bold">' . Piwik::translate('SitesManager_MergedNotificationLine1', [$bannerMessage]) . '</p><p>' . Piwik::translate('SitesManager_MergedNotificationLine2', [(implode(' / ', $guides))]) . '</p>'
            ];

            if (!empty($consentManagerIsConnected)) {
                $info['notificationMessage'] .= '<p>' . Piwik::translate('SitesManager_ConsentManagerConnected', [$consentManagerName]) . '</p>';
            }
        } else {
            $info['isNotificationsMerged'] = false;
            if (!empty($consentManagerName) ) {
                $info['notificationMessage'] = '<p>' . Piwik::translate('PrivacyManager_ConsentManagerDetected', [$consentManagerName, '<a href="' . $consentManagerUrl . '" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
                if (!empty($consentManagerIsConnected)) {
                    $info['notificationMessage'] .= '<p>' . Piwik::translate('SitesManager_ConsentManagerConnected', [$consentManagerName]) . '</p>';
                }
            } elseif ($detector->wasDetected(GoogleAnalytics3::getId())) {
                $info['notificationMessage'] = '<p>' . Piwik::translate('SitesManager_GADetected', ['Google Analytics 3', 'GA', '', '', '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-3-to-matomo/" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
            } elseif ($detector->wasDetected(GoogleAnalytics4::getId())) {
                $info['notificationMessage'] = '<p>' . Piwik::translate('SitesManager_GADetected', ['Google Analytics 4', 'GA', '', '', '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-4-to-matomo/" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
            }
        }

        return $info;
    }
}
