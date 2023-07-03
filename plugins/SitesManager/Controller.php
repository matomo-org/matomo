<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager;

use Exception;
use Piwik\API\Request;
use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugin;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Plugins\SitesManager\SiteContentDetection\Cloudflare;
use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleAnalytics3;
use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleAnalytics4;
use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleTagManager;
use Piwik\Plugins\SitesManager\SiteContentDetection\ReactJs;
use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Piwik\Plugins\SitesManager\SiteContentDetection\VueJs;
use Piwik\Plugins\SitesManager\SiteContentDetection\Wordpress;
use Piwik\Site;
use Piwik\SiteContentDetector;
use Piwik\Session;
use Piwik\SettingsPiwik;
use Piwik\Tracker\TrackerCodeGenerator;
use Piwik\Translation\Translator;
use Piwik\Url;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    /** @var SiteContentDetector */
    private $siteContentDetector;

    public function __construct(SiteContentDetector $siteContentDetector)
    {
        $this->siteContentDetector = $siteContentDetector;

        parent::__construct();
    }

    /**
     * Main view showing listing of websites and settings
     */
    public function index()
    {
        Piwik::checkUserHasSomeAdminAccess();
        SitesManager::dieIfSitesAdminIsDisabled();

        return $this->renderTemplate('index');
    }

    public function globalSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->renderTemplate('globalSettings');
    }

    public function getGlobalSettings()
    {
        Piwik::checkUserHasSomeViewAccess();

        $response = new ResponseBuilder(Common::getRequestVar('format'));

        $globalSettings = [];
        $globalSettings['keepURLFragmentsGlobal'] = API::getInstance()->getKeepURLFragmentsGlobal();
        $globalSettings['defaultCurrency'] = API::getInstance()->getDefaultCurrency();
        $globalSettings['searchKeywordParametersGlobal'] = API::getInstance()->getSearchKeywordParametersGlobal();
        $globalSettings['searchCategoryParametersGlobal'] = API::getInstance()->getSearchCategoryParametersGlobal();
        $globalSettings['defaultTimezone'] = API::getInstance()->getDefaultTimezone();
        $globalSettings['excludedIpsGlobal'] = API::getInstance()->getExcludedIpsGlobal();
        $globalSettings['excludedQueryParametersGlobal'] = API::getInstance()->getExcludedQueryParametersGlobal();
        $globalSettings['excludedUserAgentsGlobal'] = API::getInstance()->getExcludedUserAgentsGlobal();
        $globalSettings['excludedReferrersGlobal'] = API::getInstance()->getExcludedReferrersGlobal();

        return $response->getResponse($globalSettings);
    }

    /**
     * Records Global settings when user submit changes
     */
    public function setGlobalSettings()
    {
        $response = new ResponseBuilder(Common::getRequestVar('format'));

        try {
            $this->checkTokenInUrl();
            $timezone = Common::getRequestVar('timezone', false);
            $excludedIps = Common::getRequestVar('excludedIps', false);
            $excludedQueryParameters = Common::getRequestVar('excludedQueryParameters', false);
            $excludedUserAgents = Common::getRequestVar('excludedUserAgents', false);
            $excludedReferrers = Common::getRequestVar('excludedReferrers', false);
            $currency = Common::getRequestVar('currency', false);
            $searchKeywordParameters = Common::getRequestVar('searchKeywordParameters', $default = "");
            $searchCategoryParameters = Common::getRequestVar('searchCategoryParameters', $default = "");
            $keepURLFragments = Common::getRequestVar('keepURLFragments', $default = 0);

            $api = API::getInstance();
            $api->setDefaultTimezone($timezone);
            $api->setDefaultCurrency($currency);
            $api->setGlobalExcludedQueryParameters($excludedQueryParameters);
            $api->setGlobalExcludedIps($excludedIps);
            $api->setGlobalExcludedUserAgents($excludedUserAgents);
            $api->setGlobalExcludedReferrers($excludedReferrers);
            $api->setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters);
            $api->setKeepURLFragmentsGlobal($keepURLFragments);

            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }

        return $toReturn;
    }

    public function ignoreNoDataMessage()
    {
        Piwik::checkUserHasSomeViewAccess();

        $session = new Session\SessionNamespace('siteWithoutData');
        $session->ignoreMessage = true;
        $session->setExpirationSeconds($oneHour = 60 * 60);

        $url = Url::getCurrentUrlWithoutQueryString() . Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome', 'action' => 'index'));
        Url::redirectToUrl($url);
    }

    public function siteWithoutData()
    {
        $this->checkSitePermission();

        $javascriptGenerator = new TrackerCodeGenerator();
        $javascriptGenerator->forceMatomoEndpoint();
        $piwikUrl = Url::getCurrentUrlWithoutFileName();

        $jsTag = Request::processRequest('SitesManager.getJavascriptTag', ['idSite' => $this->idSite, 'piwikUrl' => $piwikUrl]);

        // Strip off open and close <script> tag and comments so that JS will be displayed in ALL mail clients
        $rawJsTag = TrackerCodeGenerator::stripTags($jsTag);

        $showMatomoLinks = true;
        /**
         * @ignore
         */
        Piwik::postEvent('SitesManager.showMatomoLinksInTrackingCodeEmail', [&$showMatomoLinks]);

        $trackerCodeGenerator = new TrackerCodeGenerator();
        $trackingUrl = trim(SettingsPiwik::getPiwikUrl(), '/') . '/' . $trackerCodeGenerator->getPhpTrackerEndpoint();

        $emailTemplateData = [
            'jsTag' => $rawJsTag,
            'showMatomoLinks' => $showMatomoLinks,
            'trackingUrl' => $trackingUrl,
            'idSite' => $this->idSite,
            'consentManagerName' => false,
        ];

        $this->siteContentDetector->detectContent();
        if ($this->siteContentDetector->consentManagerId) {
            $emailTemplateData['consentManagerName'] = $this->siteContentDetector->consentManagerName;
            $emailTemplateData['consentManagerUrl'] = $this->siteContentDetector->consentManagerUrl;
        }
        $emailTemplateData['cms'] = $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_CMS];
        $emailTemplateData['jsFrameworks'] = $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_JS_FRAMEWORK];
        $emailTemplateData['trackers'] = $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_TRACKER];

        $emailContent = $this->renderTemplateAs('@SitesManager/_trackingCodeEmail', $emailTemplateData, $viewType = 'basic');
        $inviteUserLink = $this->getInviteUserLink();

        return $this->renderTemplateAs('siteWithoutData', [
            'siteName'                                            => $this->site->getName(),
            'idSite'                                              => $this->idSite,
            'piwikUrl'                                            => $piwikUrl,
            'emailBody'                                           => $emailContent,
            'siteWithoutDataStartTrackingTranslationKey'          => StaticContainer::get('SitesManager.SiteWithoutDataStartTrackingTranslation'),
            'inviteUserLink'                                      => $inviteUserLink
        ], $viewType = 'basic');
    }

    public function siteWithoutDataTabs()
    {
        $this->checkSitePermission();

        $piwikUrl = Url::getCurrentUrlWithoutFileName();
        $jsTag = Request::processRequest('SitesManager.getJavascriptTag', ['idSite' => $this->idSite, 'piwikUrl' => $piwikUrl]);
        $maxCustomVariables = 0;

        if (Plugin\Manager::getInstance()->isPluginActivated('CustomVariables')) {
            $maxCustomVariables = CustomVariables::getNumUsableCustomVariables();
        }

        $showMatomoLinks = true;
        /**
         * @ignore
         */
        Piwik::postEvent('SitesManager.showMatomoLinksInTrackingCodeEmail', [&$showMatomoLinks]);

        $googleAnalyticsImporterMessage = '';
        if (Manager::getInstance()->isPluginLoaded('GoogleAnalyticsImporter')) {
            $googleAnalyticsImporterMessage = '<h3>' . Piwik::translate('CoreAdminHome_ImportFromGoogleAnalytics') . '</h3>'
                . '<p>' . Piwik::translate('CoreAdminHome_ImportFromGoogleAnalyticsDescription', ['<a href="https://plugins.matomo.org/GoogleAnalyticsImporter" rel="noopener noreferrer" target="_blank">', '</a>']) . '</p>'
                . '<p></p>';

            /**
             * @ignore
             */
            Piwik::postEvent('SitesManager.siteWithoutData.customizeImporterMessage', [&$googleAnalyticsImporterMessage]);
        }

        $this->siteContentDetector->detectContent([SiteContentDetector::ALL_CONTENT], $this->idSite);
        $dntChecker = new DoNotTrackHeaderChecker();

        $templateData = [
            'siteName'      => $this->site->getName(),
            'idSite'        => $this->idSite,
            'jsTag'         => $jsTag,
            'piwikUrl'      => $piwikUrl,
            'showMatomoLinks' => $showMatomoLinks,
            'cms' => $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_CMS],
            'trackers' => $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_TRACKER],
            'jsFrameworks' => $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_JS_FRAMEWORK],
            'instruction' => $this->getCmsInstruction(),
            'googleAnalyticsImporterMessage' => $googleAnalyticsImporterMessage,
            'consentManagerName' => false,
            'defaultSiteDecoded' => [
                'id' => $this->idSite,
                'name' => Common::unsanitizeInputValue(Site::getNameFor($this->idSite)),
            ],
            'maxCustomVariables' => $maxCustomVariables,
            'serverSideDoNotTrackEnabled' => $dntChecker->isActive(),
            'isJsTrackerInstallCheckAvailable' => Manager::getInstance()->isPluginActivated('JsTrackerInstallCheck'),
        ];

        if ($this->siteContentDetector->consentManagerId) {
            $templateData['consentManagerName'] = $this->siteContentDetector->consentManagerName;
            $templateData['consentManagerUrl'] = $this->siteContentDetector->consentManagerUrl;
            $templateData['consentManagerIsConnected'] = $this->siteContentDetector->isConnected;
        }

        $templateData['tabs'] = [];
        $templateData['instructionUrls'] = [];
        $templateData['othersInstructions'] = [];

        foreach ($this->siteContentDetector->getSiteContentDetectionsByType() as $detections) {
            foreach ($detections as $obj) {
                $tabContent        = $obj->renderInstructionsTab();
                $othersInstruction = $obj->renderOthersInstruction();
                $instructionUrl    = $obj->getInstructionUrl();

                Piwik::postEvent('Template.siteWithoutDataTab.' . $obj::getId() . '.content', [&$tabContent]);

                if (!empty($tabContent) && in_array($obj::getId(), $this->siteContentDetector->detectedContent[$obj::getContentType()])) {
                    $templateData['tabs'][] = [
                        'id'                => $obj::getId(),
                        'name'              => $obj::getName(),
                        'type'              => $obj::getContentType(),
                        'content'           => $tabContent,
                        'priority'          => $obj::getPriority(),
                    ];
                }

                if (!empty($othersInstruction)) {
                    $templateData['othersInstructions'][] = [
                        'id'                => $obj::getId(),
                        'name'              => $obj::getName(),
                        'type'              => $obj::getContentType(),
                        'othersInstruction' => $obj->renderOthersInstruction(),
                    ];
                }

                if (!empty($instructionUrl)) {
                    $templateData['instructionUrls'][] = [
                        'id'             => $obj::getId(),
                        'name'           => $obj::getName(),
                        'type'           => $obj::getContentType(),
                        'instructionUrl' => $obj::getInstructionUrl(),
                    ];
                }
            }
        }

        usort($templateData['tabs'], function($a, $b) {
            return strcmp($a['priority'], $b['priority']);
        });

        usort($templateData['othersInstructions'], function($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        usort($templateData['instructionUrls'], function($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        $templateData['activeTab'] = $this->getActiveTabOnLoad($templateData);
        $this->mergeMultipleNotification($templateData);

        return $this->renderTemplateAs('_siteWithoutDataTabs', $templateData, $viewType = 'basic');
    }

    private function getCmsInstruction()
    {
        if (empty($this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_CMS])
            || in_array(Wordpress::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_CMS])) {
            return '';
        }

        $detectedCms = $this->siteContentDetector->getSiteContentDetectionById(reset($this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_CMS]));

        if (null === $detectedCms) {
            return '';
        }

        Piwik::translate(
            'SitesManager_SiteWithoutDataDetectedSite',
            [
                $detectedCms::getName(),
                '<a target="_blank" rel="noreferrer noopener" href="' . $detectedCms::getInstructionUrl() . '">',
                '</a>'
            ]
        );
    }

    private function getActiveTabOnLoad($templateData)
    {
        $tabToDisplay = '';

        if (!empty(in_array(GoogleTagManager::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_TRACKER]))) {
            $tabToDisplay = GoogleTagManager::getId();
        } else if (in_array(Wordpress::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_CMS])) {
            $tabToDisplay = Wordpress::getId();
        } else if (in_array(Cloudflare::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_CMS])) {
            $tabToDisplay = Cloudflare::getId();
        } else if (in_array(VueJs::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_JS_FRAMEWORK])) {
            $tabToDisplay = VueJs::getId();
        } else if (in_array(ReactJs::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_TRACKER]) && Manager::getInstance()->isPluginActivated('TagManager')) {
            $tabToDisplay = ReactJs::getId();
        } else if (!empty($templateData['consentManagerName'])) {
            $tabToDisplay = 'consentManager';
        }

        return $tabToDisplay;
    }

    private function getInviteUserLink()
    {
        $request = \Piwik\Request::fromRequest();
        $idSite = $request->getIntegerParameter('idSite', 0);
        if (!$idSite || !Piwik::isUserHasAdminAccess($idSite)) {
            return 'https://matomo.org/faq/general/manage-users/#imanadmin-creating-users';
        }

        return SettingsPiwik::getPiwikUrl() . 'index.php?' . Url::getQueryStringFromParameters([
                'idSite' => $idSite,
                'module' => 'UsersManager',
                'action' => 'index',
            ]);
    }

    private function mergeMultipleNotification(&$templateData)
    {
        $isNotificationsMerged = false;
        $bannerMessage = '';
        $guides = [];
        $message = [];
        $ga3Used = in_array(GoogleAnalytics3::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_TRACKER]);
        $ga4Used = in_array(GoogleAnalytics4::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_TRACKER]);

        if ($ga3Used || $ga4Used) {
            $message[0] = 'Google Analytics ';
            $ga3GuideUrl =  '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-3-to-matomo/" target="_blank" rel="noreferrer noopener">Google Analytics 3</a>';
            $ga4GuideUrl =  '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-4-to-matomo/" target="_blank" rel="noreferrer noopener">Google Analytics 4</a>';
            if ($templateData['ga3Used'] && $templateData['ga4Used']) {
                $isNotificationsMerged = true;
                $guides[] = $ga3GuideUrl;
                $guides[] = $ga4GuideUrl;
                $message[0] .= '3 & 4';
            } else {
                $message[0] .= ($ga3Used ? 3 : 4);
                $guides[] = ($ga3Used ? $ga3GuideUrl : $ga4GuideUrl);
            }
        }

        if (!empty($message) && $templateData['consentManagerName']) {
            $isNotificationsMerged = true;
            $message[] = $templateData['consentManagerName'];
            $guides[] =  '<a href="' . $templateData['consentManagerUrl'] . '" target="_blank" rel="noreferrer noopener">' . $templateData['consentManagerName'] . '</a>';
        }

        if (!empty($message)) {
            $bannerMessage = StaticContainer::get(Translator::class)->createAndListing($message);
        }

        if ($isNotificationsMerged && $bannerMessage) {
            $info = [
                'isNotificationsMerged' => $isNotificationsMerged,
                'notificationMessage' => '<p class="fw-bold">' . Piwik::translate('SitesManager_MergedNotificationLine1', [$bannerMessage]) . '</p><p>' . Piwik::translate('SitesManager_MergedNotificationLine2', [(implode(' / ', $guides))]) . '</p>'
            ];

            if (!empty($templateData['consentManagerIsConnected'])) {
                $info['notificationMessage'] .= '<p>' . Piwik::translate('SitesManager_ConsentManagerConnected', [$templateData['consentManagerName']]) . '</p>';
            }
        } else {
            $info = $this->getSingleNotifications($templateData);
        }

        $templateData = array_merge($templateData, $info);
    }

    private function getSingleNotifications(&$templateData)
    {
        $info = ['isNotificationsMerged' => false, 'notificationMessage' => ''];
        if (!empty($templateData['consentManagerName']) ) {
            $info['notificationMessage'] = '<p>' . Piwik::translate('PrivacyManager_ConsentManagerDetected', [$templateData['consentManagerName'], '<a href="' . $templateData['consentManagerUrl'] . '" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
            if (!empty($templateData['consentManagerIsConnected'])) {
                $info['notificationMessage'] .= '<p>' . Piwik::translate('SitesManager_ConsentManagerConnected', [$templateData['consentManagerName']]) . '</p>';
            }
        } else if (in_array(GoogleAnalytics3::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_TRACKER])) {
            $info['notificationMessage'] = '<p>' . Piwik::translate('SitesManager_GADetected', ['Google Analytics 3', 'GA', '', '', '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-3-to-matomo/" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
        } else if (in_array(GoogleAnalytics4::getId(), $this->siteContentDetector->detectedContent[SiteContentDetectionAbstract::TYPE_TRACKER])) {
            $info['notificationMessage'] = '<p>' . Piwik::translate('SitesManager_GADetected', ['Google Analytics 4', 'GA', '', '', '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-4-to-matomo/" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
        }

        return $info;
    }
}
