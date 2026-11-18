<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager;

use Exception;
use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\Config;
use Piwik\Http\JsonResponse;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Policy\PolicyManager;
use Piwik\Plugins\SitesManager\SiteContentDetection\Matomo;
use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Piwik\Plugins\SitesManager\SiteContentDetection\WordPress;
use Piwik\SiteContentDetector;
use Piwik\Session;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\View;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    private SiteContentDetector $siteContentDetector;

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

        $pluginManager = Manager::getInstance();
        $rollUpEnabled = $pluginManager->isPluginLoaded('RollUpReporting')
            && $pluginManager->isPluginActivated('RollUpReporting');
        $privacyManagerEnabled = $pluginManager->isPluginLoaded('PrivacyManager')
            && $pluginManager->isPluginActivated('PrivacyManager');

        return $this->renderTemplate('index', [
            'rollUpEnabled' => $rollUpEnabled,
            'privacyManagerEnabled' => $privacyManagerEnabled,
        ]);
    }

    public function globalSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->renderTemplate(
            'globalSettings',
            [
                'commonSensitiveQueryParams' => Config::getInstance()->SitesManager['CommonPIIParams'],
            ]
        );
    }

    public function getGlobalSettings()
    {
        Piwik::checkUserHasSomeViewAccess();

        $response = new ResponseBuilder(Common::getRequestVar('format'));

        $globalSettings = $this->getGlobalSettingValues();
        // stored as an instance-wide option, so it is never site specific
        $globalSettings['exclusionTypeForQueryParamsPolicyControlled'] = PolicyManager::getCompliancePoliciesControllingASetting(
            API::OPTION_EXCLUDE_TYPE_QUERY_PARAMS_GLOBAL,
            null,
            PolicyManager::SETTING_TYPE_OPTION
        );

        return $response->getResponse($globalSettings);
    }

    /**
     * @return array<string, mixed>
     */
    private function getGlobalSettingValues(): array
    {
        $api = API::getInstance();

        return [
            'keepURLFragmentsGlobal' => $api->getKeepURLFragmentsGlobal(),
            'defaultCurrency' => $api->getDefaultCurrency(),
            'searchKeywordParametersGlobal' => $api->getSearchKeywordParametersGlobal(),
            'searchCategoryParametersGlobal' => $api->getSearchCategoryParametersGlobal(),
            'defaultTimezone' => $api->getDefaultTimezone(),
            'excludedIpsGlobal' => $api->getExcludedIpsGlobal(),
            'excludedQueryParametersGlobal' => $api->getExcludedQueryParametersGlobal(),
            'excludedUserAgentsGlobal' => $api->getExcludedUserAgentsGlobal(),
            'excludedReferrersGlobal' => $api->getExcludedReferrersGlobal(),
            'exclusionTypeForQueryParams' => $api->getExclusionTypeForQueryParams(),
        ];
    }

    /**
     * Records Global settings when user submit changes
     */
    public function setGlobalSettings()
    {
        $response = new ResponseBuilder(Common::getRequestVar('format'));
        $settingsBeforeSave = null;
        $saveException = null;

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
            $exclusionTypeForQueryParams = Common::getRequestVar('exclusionTypeForQueryParams', $default = "");

            $settingsBeforeSave = $this->getGlobalSettingValues();

            $api = API::getInstance();
            $api->setDefaultTimezone($timezone);
            $api->setDefaultCurrency($currency);
            $api->setGlobalExcludedIps($excludedIps);
            $api->setGlobalExcludedUserAgents($excludedUserAgents);
            $api->setGlobalExcludedReferrers($excludedReferrers);
            $api->setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters);
            $api->setKeepURLFragmentsGlobal($keepURLFragments);
            $api->setGlobalQueryParamExclusion($exclusionTypeForQueryParams, $excludedQueryParameters);
        } catch (Exception $e) {
            $saveException = $e;
        }

        try {
            if ($settingsBeforeSave !== null) {
                $settingsAfterSave = $this->getGlobalSettingValues();

                if ($saveException === null || $settingsAfterSave !== $settingsBeforeSave) {
                    /**
                     * Triggered after the global website settings were saved on the global settings page.
                     *
                     * The event is posted once for each save, also if no value changed. If the save fails part
                     * way, the values saved before the failure stay saved. The event is then posted only if
                     * these values changed a setting. It is not posted when one of the `SitesManager.setGlobal*`,
                     * `SitesManager.setKeepURLFragmentsGlobal`, `SitesManager.setDefaultCurrency` or
                     * `SitesManager.setDefaultTimezone` API methods is called directly.
                     *
                     * **Example**
                     *
                     *     Piwik::addAction('SitesManager.globalSettingsUpdated', function ($settings) {
                     *         Log::info('Global excluded IPs: ' . $settings['excludedIpsGlobal']);
                     *     });
                     *
                     * @param array $settings The current global website settings after the save, with the same
                     *                        keys as the `SitesManager.getGlobalSettings` controller action
                     *                        returns, but without the compliance policy information.
                     */
                    Piwik::postEvent('SitesManager.globalSettingsUpdated', [$settingsAfterSave]);
                }
            }
        } catch (Exception $e) {
            $saveException = $saveException ?? $e;
        }

        if ($saveException !== null) {
            return $response->getResponseException($saveException);
        }

        return $response->getResponse();
    }

    /**
     * Dismisses the no-data message and redirects back to the dashboard. Used by the standalone
     * empty-site page's link (a full-page navigation) and as the reporting UI's fallback when its
     * Ajax dismissal fails. The reporting UI itself dismisses via {@see self::dismissNoDataMessage()}.
     */
    public function ignoreNoDataMessage()
    {
        Piwik::checkUserHasSomeViewAccess();

        $this->markNoDataMessageIgnored();

        $url = Url::getCurrentUrlWithoutQueryString() . Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome', 'action' => 'index'));
        Url::redirectToUrl($url);
    }

    /**
     * Dismisses the no-data message for the reporting UI, which stays on the page. Returns JSON.
     */
    #[JsonResponse]
    public function dismissNoDataMessage(): string
    {
        Piwik::checkUserHasSomeViewAccess();

        $this->markNoDataMessageIgnored();

        return json_encode(['result' => 'success']);
    }

    private function markNoDataMessageIgnored(): void
    {
        $session = new Session\SessionNamespace('siteWithoutData');
        $session->ignoreMessage = true;
        $session->setExpirationSeconds($oneHour = 60 * 60);
    }

    public function siteWithoutData()
    {
        $this->checkSitePermission();

        return $this->renderTemplateAs('siteWithoutData', [
            'inviteUserLink'               => $this->getInviteUserLink(),
            'showInviteTeamMemberLink'     => $this->shouldShowInviteTeamMemberLink(),
            'afterTrackingMethodsContent'  => $this->getAfterTrackingMethodsContent(),
            'hideWhatIsNew'                => true,
        ], $viewType = 'basic');
    }

    /**
     * Ajax endpoint for the reporting UI gate: returns a JSON boolean (true = show the tracker-setup
     * screen). Requires view access. See SitesManager::shouldShowEmptySiteMessage().
     */
    #[JsonResponse]
    public function getSiteEmptyState(): string
    {
        $this->checkSitePermission();

        return json_encode(SitesManager::shouldShowEmptySiteMessage((int) $this->idSite));
    }

    #[JsonResponse]
    public function getTrackingMethodsForSite(): string
    {
        $this->checkSitePermission();

        $this->siteContentDetector->detectContent([], $this->idSite);

        $trackingMethods = [];
        $instructionUrls = [];
        $othersInstructions = [];

        foreach ($this->siteContentDetector->getSiteContentDetectionsByType() as $detections) {
            foreach ($detections as $obj) {
                $tabContent            = $obj->renderInstructionsTab($this->siteContentDetector);
                $othersInstruction     = $obj->renderOthersInstruction($this->siteContentDetector);
                $instructionUrl        = $obj->getInstructionUrl();
                $isRecommended         = $obj->isRecommended($this->siteContentDetector);
                $recommendationDetails = $obj->getRecommendationDetails($this->siteContentDetector);

                /**
                 * Event that can be used to manipulate the content of a certain tab on the no data page
                 *
                 * @param string $tabContent  Content of the tab
                 * @param SiteContentDetector $detector  Instance of SiteContentDetector, holding current detection results
                 */
                Piwik::postEvent('Template.siteWithoutDataTab.' . $obj::getId() . '.content', [&$tabContent, $this->siteContentDetector]);
                /**
                 * Event that can be used to manipulate the content of a record on the others tab on the no data page
                 *
                 * @param string $othersInstruction  Content of the record
                 * @param SiteContentDetector $detector  Instance of SiteContentDetector, holding current detection results
                 */
                Piwik::postEvent('Template.siteWithoutDataTab.' . $obj::getId() . '.others', [&$othersInstruction, $this->siteContentDetector]);

                if (!empty($tabContent)) {
                    $trackingMethods[] = [
                        'id'                   => $obj::getId(),
                        'name'                 => $obj::getName(),
                        'type'                 => $obj::getContentType(),
                        'content'              => $tabContent,
                        'icon'                 => $obj::getIcon(),
                        'priority'             => $obj::getPriority(),
                        'wasDetected'          => $this->siteContentDetector->wasDetected($obj::getId()),
                        'isRecommended'        => $isRecommended,
                        'recommendationTitle'  => $recommendationDetails['title'],
                        'recommendationText'   => $recommendationDetails['text'],
                        'recommendationButton' => $recommendationDetails['button'],
                    ];
                }

                if (!empty($othersInstruction)) {
                    $othersInstructions[] = [
                        'id'                => $obj::getId(),
                        'name'              => $obj::getName(),
                        'type'              => $obj::getContentType(),
                        'othersInstruction' => $othersInstruction,
                    ];
                }

                if (!empty($instructionUrl)) {
                    $instructionUrls[] = [
                        'id'             => $obj::getId(),
                        'name'           => $obj::getName(),
                        'type'           => $obj::getContentType(),
                        'instructionUrl' => $obj::getInstructionUrl(),
                    ];
                }
            }
        }

        usort($trackingMethods, function ($a, $b) {
            if ($a['isRecommended'] === $b['isRecommended']) {
                return $a['priority'] === $b['priority'] ? 0 : ($a['priority'] < $b['priority'] ? -1 : 1);
            }

            return $a['isRecommended'] ? -1 : 1;
        });

        usort($othersInstructions, function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        usort($instructionUrls, function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        // add integration and others tab
        $trackingMethods[] = [
            'id'                   => 'Integrations',
            'name'                 => Piwik::translate('SitesManager_Integrations'),
            'type'                 => SiteContentDetectionAbstract::TYPE_OTHER,
            'content'              => $this->renderIntegrationsTab($instructionUrls),
            'icon'                 => './plugins/SitesManager/images/integrations.svg',
            'priority'             => 10000,
            'wasDetected'          => false,
            'isRecommended'        => false,
            'recommendationTitle'  => '',
            'recommendationText'   => '',
            'recommendationButton' => '',
        ];
        $trackingMethods[] = [
            'id'                   => 'Other',
            'name'                 => Piwik::translate('SitesManager_SiteWithoutDataOtherWays'),
            'type'                 => SiteContentDetectionAbstract::TYPE_OTHER,
            'content'              => $this->renderOthersTab($othersInstructions),
            'icon'                 => './plugins/SitesManager/images/others.svg',
            'priority'             => 10001,
            'wasDetected'          => false,
            'isRecommended'        => false,
            'recommendationTitle'  => '',
            'recommendationText'   => '',
            'recommendationButton' => '',
        ];

        $recommendedMethod = null;
        $matomoIndex = null;

        foreach ($trackingMethods as $index => $tab) {
            // Note: We recommend the first method that is recommended (after sorting by priority)
            if (true === $tab['isRecommended']) {
                $recommendedMethod = $tab;
                unset($trackingMethods[$index]);
                break;
            }

            if ($tab['id'] === Matomo::getId()) {
                $matomoIndex = $index;
            }
        }

        // fall back to javascript code recommendation if nothing was detected
        if (null === $recommendedMethod && null !== $matomoIndex) {
            $recommendedMethod = $trackingMethods[$matomoIndex];
            unset($trackingMethods[$matomoIndex]);
        }

        return json_encode([
            'trackingMethods' => $trackingMethods,
            'recommendedMethod' => $recommendedMethod,
            // The standalone page gets these as template variables; the SPA gate has to fetch them.
            'ctaContent' => $this->renderSiteWithoutDataCta(),
            'afterTrackingMethodsContent' => $this->getAfterTrackingMethodsContent(),
        ]);
    }

    private function renderSiteWithoutDataCta(): string
    {
        $view = new View('@SitesManager/_siteWithoutDataCta');
        $view->inviteUserLink = $this->getInviteUserLink();
        $view->showInviteTeamMemberLink = $this->shouldShowInviteTeamMemberLink();
        $view->sendHeadersWhenRendering = false;
        return $view->render();
    }

    private function getAfterTrackingMethodsContent(): string
    {
        $content = '';

        /**
         * Triggered on the no data page after the list of tracking methods and the section
         * allowing users to temporarily hide the page.
         *
         * This event can be used to render additional content at the bottom of the no data page.
         * The content is shown on the standalone page as well as when the page is embedded in the
         * reporting UI.
         *
         * @param string $content Additional HTML content to render after the tracking methods.
         */
        Piwik::postEvent('Template.siteWithoutData.afterTrackingMethods', [&$content]);

        return $content;
    }

    private function shouldShowInviteTeamMemberLink(): bool
    {
        $showInviteTeamMemberLink = true;

        /**
         * Triggered before rendering the invite team member link on the no data page.
         *
         * This event can be used to hide the link, for example if inviting users is handled
         * outside of Matomo.
         *
         * @param bool $showInviteTeamMemberLink Whether the invite team member link should be shown. Defaults to `true`.
         */
        Piwik::postEvent('SitesManager.siteWithoutData.showInviteTeamMemberLink', [&$showInviteTeamMemberLink]);

        return $showInviteTeamMemberLink;
    }

    private function getGoogleAnalyticsImporterInstruction()
    {
        $googleAnalyticsImporterInstruction = [];

        if (!Manager::getInstance()->isPluginLoaded('GoogleAnalyticsImporter')) {
            $googleAnalyticsImporterInstruction = [
                'id'                => 'GoogleAnalyticsImporter',
                'name'              => Piwik::translate('CoreAdminHome_ImportFromGoogleAnalytics'),
                'type'              => SiteContentDetectionAbstract::TYPE_OTHER,
                'othersInstruction' => Piwik::translate(
                    'CoreAdminHome_ImportFromGoogleAnalyticsDescription',
                    [Url::getExternalLinkTag('https://plugins.matomo.org/GoogleAnalyticsImporter'), '</a>']
                ),
            ];
        }

        /**
         * @ignore
         */
        Piwik::postEvent('SitesManager.siteWithoutData.customizeImporterMessage', [&$googleAnalyticsImporterInstruction]);

        return $googleAnalyticsImporterInstruction;
    }

    private function renderIntegrationsTab($instructionUrls): string
    {
        $view = new View('@SitesManager/_integrationsTab');
        $view->instruction = $this->getCmsInstruction();
        $view->instructionUrls = $instructionUrls;
        $view->trackers = $this->siteContentDetector->getDetectsByType(SiteContentDetectionAbstract::TYPE_TRACKER);
        $view->idSite = $this->idSite;
        $view->matomoUrl = Url::getCurrentUrlWithoutFileName();
        return $view->render();
    }

    private function renderOthersTab($othersInstructions): string
    {
        array_unshift(
            $othersInstructions,
            [
                'id'                => 'ImageTracking',
                'name'              => Piwik::translate('CoreAdminHome_ImageTracking'),
                'type'              => SiteContentDetectionAbstract::TYPE_OTHER,
                'othersInstruction' => Piwik::translate(
                    'SitesManager_ImageTrackingDescription',
                    [Url::getExternalLinkTag('https://matomo.org/docs/tracking-api/reference/'), '</a>']
                ),
            ],
            [
                'id'                => 'LogAnalytics',
                'name'              => Piwik::translate('SitesManager_LogAnalytics'),
                'type'              => SiteContentDetectionAbstract::TYPE_OTHER,
                'othersInstruction' => Piwik::translate(
                    'SitesManager_LogAnalyticsDescription',
                    [Url::getExternalLinkTag('https://matomo.org/log-analytics/'), '</a>']
                ),
            ],
            [
                'id'                => 'MobileAppsAndSDKs',
                'name'              => Piwik::translate('SitesManager_MobileAppsAndSDKs'),
                'type'              => SiteContentDetectionAbstract::TYPE_OTHER,
                'othersInstruction' => Piwik::translate(
                    'SitesManager_MobileAppsAndSDKsDescription',
                    [Url::getExternalLinkTag('https://matomo.org/integrate/#programming-language-platforms-and-frameworks'), '</a>']
                ),
            ],
            [
                'id'                => 'HttpTrackingApi',
                'name'              => Piwik::translate('CoreAdminHome_HttpTrackingApi'),
                'type'              => SiteContentDetectionAbstract::TYPE_OTHER,
                'othersInstruction' => Piwik::translate(
                    'CoreAdminHome_HttpTrackingApiDescription',
                    [Url::getExternalLinkTag('https://developer.matomo.org/api-reference/tracking-api'), '</a>']
                ),
            ]
        );

        $googleAnalyticsImporterInstruction = $this->getGoogleAnalyticsImporterInstruction();

        if (!empty($googleAnalyticsImporterInstruction)) {
            $othersInstructions[] = $googleAnalyticsImporterInstruction;
        }

        $view = new View('@SitesManager/_othersTab');
        $view->othersInstructions = $othersInstructions;
        $view->idSite = $this->idSite;
        $view->matomoUrl = Url::getCurrentUrlWithoutFileName();
        return $view->render();
    }

    private function getCmsInstruction()
    {
        $detectedCMSes = $this->siteContentDetector->getDetectsByType(SiteContentDetectionAbstract::TYPE_CMS);

        if (
            empty($detectedCMSes)
            || $this->siteContentDetector->wasDetected(WordPress::getId())
        ) {
            return '';
        }

        $detectedCms = null;

        foreach ($detectedCMSes as $detected) {
            $detectedCms = $this->siteContentDetector->getSiteContentDetectionById($detected);

            if (null !== $detectedCms && !empty($detectedCms::getInstructionUrl())) {
                break;
            }
        }

        if (null === $detectedCms || empty($detectedCms::getInstructionUrl())) {
            return '';
        }

        return Piwik::translate(
            'SitesManager_SiteWithoutDataDetectedSite',
            [
                $detectedCms::getName(),
                Url::getExternalLinkTag($detectedCms::getInstructionUrl()),
                '</a>',
            ]
        );
    }

    private function getInviteUserLink()
    {
        $request = \Piwik\Request::fromRequest();
        $idSite = $request->getIntegerParameter('idSite', 0);
        if (!$idSite || !Piwik::isUserHasAdminAccess($idSite)) {
            return Url::addCampaignParametersToMatomoLink('https://matomo.org/faq/general/manage-users/#imanadmin-creating-users');
        }

        return SettingsPiwik::getPiwikUrl() . 'index.php?' . Url::getQueryStringFromParameters([
                'idSite' => $idSite,
                'module' => 'UsersManager',
                'action' => 'index',
                'showadduser' => 1,
            ]);
    }
}
