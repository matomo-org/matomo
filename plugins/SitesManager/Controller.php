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
use Piwik\DataTable\Renderer\Json;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\SitesManager\SiteContentDetection\Matomo;
use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Piwik\Plugins\SitesManager\SiteContentDetection\WordPress;
use Piwik\SiteContentDetector;
use Piwik\Session;
use Piwik\SettingsPiwik;
use Piwik\Url;
use Piwik\View;

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

        return $this->renderTemplate(
            'globalSettings',
            [
                'commonSensitiveQueryParams' => Config::getInstance()->SitesManager['CommonPIIParams']
            ]
        );
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
        $globalSettings['exclusionTypeForQueryParams'] = API::getInstance()->getExclusionTypeForQueryParams();

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
            $exclusionTypeForQueryParams = Common::getRequestVar('exclusionTypeForQueryParams', $default = "");

            $api = API::getInstance();
            $api->setDefaultTimezone($timezone);
            $api->setDefaultCurrency($currency);
            $api->setGlobalExcludedIps($excludedIps);
            $api->setGlobalExcludedUserAgents($excludedUserAgents);
            $api->setGlobalExcludedReferrers($excludedReferrers);
            $api->setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters);
            $api->setKeepURLFragmentsGlobal($keepURLFragments);
            $api->setGlobalQueryParamExclusion($exclusionTypeForQueryParams, $excludedQueryParameters);

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

        return $this->renderTemplateAs('siteWithoutData', [
            'inviteUserLink' => $this->getInviteUserLink(),
            'hideWhatIsNew'  => true,
        ], $viewType = 'basic');
    }

    public function getTrackingMethodsForSite()
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

        Json::sendHeaderJSON();
        echo json_encode([
            'trackingMethods' => $trackingMethods,
            'recommendedMethod' => $recommendedMethod
        ]);
        exit;
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
                    ['<a href="' . Url::addCampaignParametersToMatomoLink('https://plugins.matomo.org/GoogleAnalyticsImporter') . '" rel="noopener noreferrer" target="_blank">', '</a>']
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
                    ['<a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/tracking-api/reference/') . '" rel="noreferrer noopener" target="_blank">', '</a>']
                ),
            ],
            [
                'id'                => 'LogAnalytics',
                'name'              => Piwik::translate('SitesManager_LogAnalytics'),
                'type'              => SiteContentDetectionAbstract::TYPE_OTHER,
                'othersInstruction' => Piwik::translate(
                    'SitesManager_LogAnalyticsDescription',
                    ['<a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/log-analytics/') . '" rel="noreferrer noopener" target="_blank">', '</a>']
                ),
            ],
            [
                'id'                => 'MobileAppsAndSDKs',
                'name'              => Piwik::translate('SitesManager_MobileAppsAndSDKs'),
                'type'              => SiteContentDetectionAbstract::TYPE_OTHER,
                'othersInstruction' => Piwik::translate(
                    'SitesManager_MobileAppsAndSDKsDescription',
                    ['<a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/integrate/#programming-language-platforms-and-frameworks') . '" rel="noreferrer noopener" target="_blank">', '</a>']
                ),
            ],
            [
                'id'                => 'HttpTrackingApi',
                'name'              => Piwik::translate('CoreAdminHome_HttpTrackingApi'),
                'type'              => SiteContentDetectionAbstract::TYPE_OTHER,
                'othersInstruction' => Piwik::translate(
                    'CoreAdminHome_HttpTrackingApiDescription',
                    ['<a href="' . Url::addCampaignParametersToMatomoLink('https://developer.matomo.org/api-reference/tracking-api') . '" rel="noreferrer noopener" target="_blank">', '</a>']
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
                '<a target="_blank" rel="noreferrer noopener" href="' . $detectedCms::getInstructionUrl() . '">',
                '</a>'
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
            ]);
    }
}
