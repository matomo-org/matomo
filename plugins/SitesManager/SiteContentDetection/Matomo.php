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
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Site;
use Piwik\SiteContentDetector;
use Piwik\Url;
use Piwik\View;

class Matomo extends SiteContentDetectionAbstract
{
    public static function getName(): string
    {
        return Piwik::translate('CoreAdminHome_JavaScriptCode');
    }

    public static function getIcon(): string
    {
        return './plugins/SitesManager/images/code.svg';
    }

    public static function getContentType(): int
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
        $view->notificationMessage = $this->getConsentManagerNotification($detector);
        return $view->render();
    }

    public function isRecommended(SiteContentDetector $detector): bool
    {
        return false; // do not recommend this, as it's used as fall back
    }

    public function getRecommendationDetails(SiteContentDetector $detector): array
    {
        $details = parent::getRecommendationDetails($detector);
        $details['text'] = Piwik::translate('SitesManager_SetupMatomoTracker');
        return $details;
    }

    private function getConsentManagerNotification(SiteContentDetector $detector): string
    {
        $notificationMessage = '';
        $consentManagerName = null;
        $consentManagers = $detector->getDetectsByType(SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER);
        if (!empty($consentManagers)) {
            $consentManagerId = reset($consentManagers);
            $consentManager = $detector->getSiteContentDetectionById($consentManagerId);
            $consentManagerName = $consentManager::getName();
            $consentManagerUrl = $consentManager::getInstructionUrl();
            $consentManagerIsConnected = in_array($consentManagerId, $detector->connectedConsentManagers);
        }

        if (!empty($consentManagerName) ) {
            $notificationMessage = '<p>' . Piwik::translate('PrivacyManager_ConsentManagerDetected', [$consentManagerName, '<a href="' . $consentManagerUrl . '" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
            if (!empty($consentManagerIsConnected)) {
                $notificationMessage .= '<p>' . Piwik::translate('SitesManager_ConsentManagerConnected', [$consentManagerName]) . '</p>';
            }
        }

        return $notificationMessage;
    }
}
