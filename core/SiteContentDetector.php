<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Matomo\Cache\Lazy;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\SitesManager\SiteContentDetection\ConsentManagerDetectionAbstract;
use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleAnalytics3;
use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleAnalytics4;
use Piwik\Plugins\SitesManager\SiteContentDetection\GoogleTagManager;
use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Piwik\Plugins\SitesManager\SitesManager;

/**
 * This class provides detection functions for specific content on a site. It can be used to easily detect the
 * presence of known third party code.
 *
 * Note: Calling the detect() method will create a HTTP request to the site to retrieve data, only the main site URL
 * will be checked
 *
 * Usage:
 *
 * $contentDetector = new SiteContentDetector();
 * $contentDetector->detectContent([GoogleAnalytics3::getId()]);
 * if ($contentDetector->ga3) {
 *      // site is using GA3
 * }
 *
 * @api
 */
class SiteContentDetector
{
    /**
     * @var array<string, ?array<string, SiteContentDetectionAbstract>>
     */
    public $detectedContent = [
        SiteContentDetectionAbstract::TYPE_TRACKER => [],
        SiteContentDetectionAbstract::TYPE_CMS => [],
        SiteContentDetectionAbstract::TYPE_JS_FRAMEWORK => [],
        SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER => [],
        SiteContentDetectionAbstract::TYPE_OTHER => [],
    ];

    public $connectedConsentManagers = [];

    private $siteResponse = [
        'data' => '',
        'headers' => []
    ];

    /** @var Lazy */
    private $cache;

    public function __construct(?Lazy $cache = null)
    {
        if ($cache === null) {
            $this->cache = Cache::getLazyCache();
        } else {
            $this->cache = $cache;
        }
    }


    /**
     * @return array<string, SiteContentDetectionAbstract[]>
     */
    public static function getSiteContentDetectionsByType()
    {
        $instancesByType = [];
        $classes = self::getAllSiteContentDetectionClasses();

        foreach ($classes as $className) {
           $instancesByType[$className::getContentType()][] = StaticContainer::get($className);
        }

        return $instancesByType;
    }

    /**
     * @param string $id
     * @return SiteContentDetectionAbstract|null
     */
    public function getSiteContentDetectionById(string $id): ?SiteContentDetectionAbstract
    {
        $classes = $this->getAllSiteContentDetectionClasses();

        foreach ($classes as $className) {
            if ($className::getId() === $id) {
                return StaticContainer::get($className);
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    protected static function getAllSiteContentDetectionClasses(): array
    {
        return Plugin\Manager::getInstance()->findMultipleComponents('SiteContentDetection', SiteContentDetectionAbstract::class);
    }

    /**
     * Reset the detections
     *
     * @return void
     */
    private function resetDetections(): void
    {
        $this->detectedContent          = [
            SiteContentDetectionAbstract::TYPE_TRACKER => [],
            SiteContentDetectionAbstract::TYPE_CMS => [],
            SiteContentDetectionAbstract::TYPE_JS_FRAMEWORK => [],
            SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER => [],
            SiteContentDetectionAbstract::TYPE_OTHER => [],
        ];
        $this->connectedConsentManagers = [];
    }

    /**
     * This will query the site and populate the class properties with
     * the details of the detected content
     *
     * @param array       $detectContent Array of content type for which to check, defaults to all, limiting this list
     *                                   will speed up the detection check
     * @param ?int        $idSite        Override the site ID, will use the site from the current request if null
     * @param ?array      $siteResponse  String containing the site data to search, if blank then data will be retrieved
     *                                   from the current request site via an http request
     * @param int         $timeOut       How long to wait for the site to response, defaults to 5 seconds
     * @return void
     */
    public function detectContent(array $detectContent = [],
                                  ?int $idSite = null, ?array $siteResponse = null, int $timeOut = 5): void
    {
        $this->resetDetections();

        // If site data was passed in, then just run the detection checks against it and return.
        if ($siteResponse) {
            $this->siteResponse = $siteResponse;
            $this->detectionChecks($detectContent);
            return;
        }

        // Get the site id from the request object if not explicitly passed
        if ($idSite === null) {
            $idSite = Request::fromRequest()->getIntegerParameter('idSite', 0);

            if (!$idSite) {
                return;
            }
        }

        $url = Site::getMainUrlFor($idSite);

        // Check and load previously cached site content detection data if it exists
        $cacheKey = 'SiteContentDetection_' . md5($url);
        $siteContentDetectionCache = $this->cache->fetch($cacheKey);

        if ($siteContentDetectionCache !== false) {
            $cachedSiteContentDetection = $siteContentDetectionCache;
            if ($this->checkCacheHasRequiredProperties($detectContent, $cachedSiteContentDetection)) {
                $this->detectedContent = $cachedSiteContentDetection;
                return;
            }
        }

        // No cache hit, no passed data, so make a request for the site content
        $siteResponse = $this->requestSiteResponse($url, $timeOut);

        // Abort if still no site data
        if (empty($siteResponse['data'])) {
            return;
        }

        $this->siteResponse = $siteResponse;

        // We now have site data to analyze, so run the detection checks
        $this->detectionChecks($detectContent);

        // A request was made to get this data and it isn't currently cached, so write it to the cache now
        $cacheLife = (60 * 60 * 24 * 7);
        $this->savePropertiesToCache($cacheKey, $this->detectedContent, $cacheLife);
    }

    /**
     * Returns if the detection with the provided id was detected or not
     *
     * Note: self::detectContent needs to be called before.
     *
     * @param string $detectionClassId
     * @return bool
     */
    public function wasDetected(string $detectionClassId): bool
    {
        foreach ($this->detectedContent as $type => $detectedClassIds) {
            if (array_key_exists($detectionClassId, $detectedClassIds)) {
                return $detectedClassIds[$detectionClassId] ?? false;
            }
        }

        return false;
    }

    public function getDetectsByType(string $type): array
    {
        $detected = [];

        foreach ($this->detectedContent[$type] as $objId => $wasDetected) {
            if (true === $wasDetected) {
                $detected[] = $objId;
            }
        }


        return $detected;
    }

    /**
     * Checks that all required properties are in the cache array
     *
     * @param array $properties
     * @param array $cache
     *
     * @return bool
     */
    private function checkCacheHasRequiredProperties(array $properties, array $cache): bool
    {
        // todo implement

        return true;
    }

    /**
     * Save properties to the cache
     *
     * @param string $cacheKey
     * @param array  $detectionTypes
     * @param int    $cacheLife
     *
     * @return void
     */
    private function savePropertiesToCache(string $cacheKey, array $detectionTypes, int $cacheLife): void
    {
        $cacheData = [];

        // Load any existing cached values
        $siteContentDetectionCache = $this->cache->fetch($cacheKey);

        if (is_array($siteContentDetectionCache)) {
            $cacheData = $siteContentDetectionCache;
        }

        foreach ($detectionTypes as $type) {
            $cacheData[$type] = $this->detectedContent[$type];
        }

        $this->cache->save($cacheKey, $cacheData, $cacheLife);
    }

    /**
     * Run various detection checks for site content
     *
     * @param array $detectContent    Array of detection types used to filter the checks that are run
     *
     * @return void
     */
    private function detectionChecks($detectContent): void
    {
        $detections = $this->getSiteContentDetectionsByType();

        foreach ($detections as $type => $typeDetections) {
            foreach ($typeDetections as $typeDetection) {
                $this->detectedContent[$type][$typeDetection::getId()] = null;

                if (in_array($type, $detectContent) ||
                    in_array($typeDetection::getId(), $detectContent) ||
                    empty($detectContent))
                {
                    $this->detectedContent[$type][$typeDetection::getId()] = false;

                    if ($typeDetection->detectByContent($this->siteResponse['data'], $this->siteResponse['headers'])) {
                        if ($typeDetection instanceof ConsentManagerDetectionAbstract
                            && $typeDetection->checkIsConnected($this->siteResponse['data'], $this->siteResponse['headers']) ) {
                            $this->connectedConsentManagers[] = $typeDetection::getId();
                        }
                        $this->detectedContent[$type][$typeDetection::getId()] = true;
                    }
                }
            }
        }
    }

    /**
     * Retrieve data from the specified site using an HTTP request
     *
     * @param string $url
     * @param int $timeOut
     *
     * @return array
     */
    private function requestSiteResponse(string $url, int $timeOut): array
    {
        if (!$url) {
            return [];
        }

        // If internet features are disabled, we don't try to fetch any site content
        if (0 === (int) GeneralConfig::getConfigValue('enable_internet_features')) {
            return [];
        }

        $siteData = [];

        try {
            $siteData = Http::sendHttpRequestBy(Http::getTransportMethod(), $url, $timeOut, null, null,
                null, 0, false, true, false, true);
        } catch (\Exception $e) {
        }

        return $siteData;
    }

    /**
     * Return an array of consent manager definitions which can be used to detect their presence on the site and show
     * the associated guide links
     *
     * Note: This list is also used to display the known / supported consent managers on the "Ask for Consent" page
     * For adding a new consent manager to this page, it needs to be added here. If a consent manager can't be detected
     * automatically, simply leave the detections empty.
     *
     * @return array[]
     */
    public static function getKnownConsentManagers(): array
    {
        $detections = self::getSiteContentDetectionsByType();
        $cmDetections = $detections[SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER];

        $consentManagers = [];

        foreach ($cmDetections as $detection) {
            $consentManagers[$detection::getId()] = [
                'name' => $detection::getName(),
                'instructionUrl' => $detection::getInstructionUrl(),
            ];
        }

        return $consentManagers;
    }
}
