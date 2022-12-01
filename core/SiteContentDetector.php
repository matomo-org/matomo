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
 * $contentDetector->detectContent([SiteContentDetector::GA3]);
 * if ($contentDetector->ga3) {
 *      // site is using GA3
 * }
 *
 */
class SiteContentDetector
{
    // Content types
    const ALL_CONTENT = 1;
    const CONSENT_MANAGER = 2;
    const GA3 = 3;
    const GA4 = 4;
    const GTM = 5;

    // Detection detail
    public $consentManagerId;       // Id of the detected consent manager, eg. 'osano'
    public $consentManagerName;     // Display name of the detected consent manager, eg. 'Osano'
    public $consentManagerUrl;      // Url for the configuration guide for the detected consent manager
    public $isConnected = false;    // True if the detected consent manager is already connected with Matomo
    public $ga3;                    // True if GA3 was detected on the site
    public $ga4;                    // True if GA4 was detected on the site
    public $gtm;                    // True if GTM was detected on the site

    private $siteData;

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
     * Reset the detection properties
     *
     * @return void
     */
    private function resetDetectionProperties(): void
    {
        $this->consentManagerId = null;
        $this->consentManagerUrl = null;
        $this->consentManagerName = null;
        $this->isConnected = false;
        $this->ga3 = false;
        $this->ga4 = false;
        $this->gtm = false;
    }

    /**
     * This will query the site and populate the class properties with
     * the details of the detected content
     *
     * @param array       $detectContent Array of content type for which to check, defaults to all, limiting this list
     *                                   will speed up the detection check
     * @param ?int        $idSite        Override the site ID, will use the site from the current request if null
     * @param string|null $siteData      String containing the site data to search, if blank then data will be retrieved
     *                                   from the current request site via an http request
     * @param int         $timeOut       How long to wait for the site to response, defaults to 5 seconds
     * @return void
     */
    public function detectContent(array $detectContent = [SiteContentDetector::ALL_CONTENT],
                                  ?int $idSite = null, ?string $siteData = null, int $timeOut = 5): void
    {
        $this->resetDetectionProperties();

        // If site data was passed in, then just run the detection checks against it and return.
        if ($siteData) {
            $this->siteData = $siteData;
            $this->detectionChecks($detectContent);
            return;
        }

        // Get the site id from the request object if not explicitly passed
        if ($idSite === null) {
            if (!isset($_REQUEST['idSite'])) {
                return;
            }

            $idSite = Common::getRequestVar('idSite', null, 'int');

            if (!$idSite) {
                return;
            }
        }

        // Check and load previously cached site content detection data if it exists
        $cacheKey = 'SiteContentDetector_' . $idSite;
        $requiredProperties = $this->getRequiredProperties($detectContent);
        $siteContentDetectionCache = $this->cache->fetch($cacheKey);

        if ($siteContentDetectionCache !== false) {
            if ($this->checkCacheHasRequiredProperties($requiredProperties, $siteContentDetectionCache)) {
                $this->loadRequiredPropertiesFromCache($requiredProperties, $siteContentDetectionCache);
                return;
            }
        }

        // No cache hit, no passed data, so make a request for the site content
        $siteData = $this->requestSiteData($idSite, $timeOut);

        // Abort if still no site data
        if ($siteData === null || $siteData === '') {
            return;
        }

        $this->siteData = $siteData;

        // We now have site data to analyze, so run the detection checks
        $this->detectionChecks($detectContent);

        // A request was made to get this data and it isn't currently cached, so write it to the cache now
        $cacheLife = (60 * 60 * 24 * 7);
        $this->savePropertiesToCache($cacheKey, $requiredProperties, $cacheLife);
    }

    /**
     * Returns an array of properties required by the detect content array
     *
     * @param array $detectContent
     *
     * @return array
     */
    private function getRequiredProperties(array $detectContent): array
    {
        $requiredProperties = [];

        if (in_array(SiteContentDetector::CONSENT_MANAGER, $detectContent) || in_array(SiteContentDetector::ALL_CONTENT, $detectContent)) {
            $requiredProperties = array_merge($requiredProperties, ['consentManagerId', 'consentManagerName', 'consentManagerUrl', 'isConnected']);
        }

        if (in_array(SiteContentDetector::GA3, $detectContent) || in_array(SiteContentDetector::ALL_CONTENT, $detectContent)) {
            $requiredProperties[] = 'ga3';
        }

        if (in_array(SiteContentDetector::GA4, $detectContent) || in_array(SiteContentDetector::ALL_CONTENT, $detectContent)) {
            $requiredProperties[] = 'ga4';
        }

        if (in_array(SiteContentDetector::GTM, $detectContent) || in_array(SiteContentDetector::ALL_CONTENT, $detectContent)) {
            $requiredProperties[] = 'gtm';
        }

        return $requiredProperties;
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
        foreach ($properties as $prop) {
            if (!array_key_exists($prop, $cache)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Load object properties from the cache array
     *
     * @param array $properties
     * @param array $cache
     *
     * @return void
     */
    private function loadRequiredPropertiesFromCache(array $properties, array $cache): void
    {
        foreach ($properties as $prop) {
            if (!array_key_exists($prop, $cache)) {
                continue;
            }

            $this->{$prop} = $cache[$prop];
        }
    }

    /**
     * Save properties to the cache
     *
     * @param string $cacheKey
     * @param array  $properties
     * @param int    $cacheLife
     *
     * @return void
     */
    private function savePropertiesToCache(string $cacheKey, array $properties, int $cacheLife): void
    {

        $cacheData = [];

        // Load any existing cached values
        $siteContentDetectionCache = $this->cache->fetch($cacheKey);

        if (is_array($siteContentDetectionCache)) {
            $cacheData = $siteContentDetectionCache;
        }

        foreach ($properties as $prop) {
            $cacheData[$prop] = $this->{$prop};
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
        if (in_array(SiteContentDetector::CONSENT_MANAGER, $detectContent) || in_array(SiteContentDetector::ALL_CONTENT, $detectContent)) {
            $this->detectConsentManager();
        }

        if (in_array(SiteContentDetector::GA3, $detectContent) || in_array(SiteContentDetector::ALL_CONTENT, $detectContent)) {
            $this->detectGA3();
        }

        if (in_array(SiteContentDetector::GA4, $detectContent) || in_array(SiteContentDetector::ALL_CONTENT, $detectContent)) {
            $this->detectGA4();
        }

        if (in_array(SiteContentDetector::GTM, $detectContent) || in_array(SiteContentDetector::ALL_CONTENT, $detectContent)) {
            $this->detectGTM();
        }
    }

    /**
     * Retrieve data from the specified site using an HTTP request
     *
     * @param int $idSite
     * @param int $timeOut
     *
     * @return string|null
     */
    private function requestSiteData(int $idSite, int $timeOut): ?string
    {
        // If internet features are disabled, we don't try to fetch any site content
        if (0 === (int) GeneralConfig::getConfigValue('enable_internet_features')) {
            return null;
        }

        $url = Site::getMainUrlFor($idSite);

        if (!$url) {
            return null;
        }

        $siteData = null;

        try {
            $siteData = Http::sendHttpRequestBy(Http::getTransportMethod(), $url, $timeOut, null, null,
                null, 0, false, true);
        } catch (\Exception $e) {
        }

        return $siteData;
    }

    /**
     * Detect known consent managers in the site data
     *
     * Populate this object's properties with the results
     *
     * @return void
     */
    private function detectConsentManager(): void
    {
        $defs = SiteContentDetector::getConsentManagerDefinitions();

        if (!$defs) {
            return;
        }

        foreach ($defs as $consentManagerId => $consentManagerDef) {
            foreach ($consentManagerDef['detectStrings'] as $dStr) {
                if (strpos($this->siteData, $dStr) !== false && array_key_exists($consentManagerId, $defs)) {
                    $this->consentManagerId = $consentManagerId;
                    $this->consentManagerName = $consentManagerDef['name'];
                    $this->consentManagerUrl = $consentManagerDef['url'];
                    break 2;
                }
            }
        }

        if (!isset($defs[$this->consentManagerId]['connectedStrings'])) {
            return;
        }

        // If a consent manager was detected then perform an additional check to see if it has been connected to Matomo
        foreach ($defs[$this->consentManagerId]['connectedStrings'] as $cStr) {
            if (strpos($this->siteData, $cStr) !== false) {
                $this->isConnected = true;
                break;
            }
        }
    }

    /**
     * Detect GA3 usage from the site data
     *
     * @return void
     */
    private function detectGA3(): void
    {
        if (strpos($this->siteData, '(i,s,o,g,r,a,m)') !== false) {
             $this->ga3 = true;
        }
    }

    /**
     * Detect GA4 usage from the site data
     *
     * @return void
     */
    private function detectGA4(): void
    {
        if (strpos($this->siteData, 'gtag.js') !== false) {
             $this->ga4 = true;
        }
    }

    /**
     * Detect GTM usage from the site data
     *
     * @return void
     */
    private function detectGTM(): void
    {
        if (strpos($this->siteData, 'gtm.js') !== false) {
             $this->gtm = true;
        }
    }

    /**
     * Return an array of consent manager definitions which can be used to detect their presence on the site and show
     * the associated guide links
     *
     * @return array[]
     */
    public static function getConsentManagerDefinitions(): array
    {
        return [

            'osano' => [
                'name' => 'Osano',
                'detectStrings' => ['osano.com'],
                'connectedStrings' => ["Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });"],
                'url' => 'https://matomo.org/faq/how-to/using-osano-consent-manager-with-matomo',
                ],

            'cookiebot' => [
                'name' => 'Cookiebot',
                'detectStrings' => ['cookiebot.com'],
                'connectedStrings' => ["typeof _paq === 'undefined' || typeof Cookiebot === 'undefined'"],
                'url' => 'https://matomo.org/faq/how-to/using-cookiebot-consent-manager-with-matomo',
                ],

            'cookieyes' => [
                'name' => 'CookieYes',
                'detectStrings' => ['cookieyes.com'],
                'connectedStrings' => ['document.addEventListener("cookieyes_consent_update", function (eventData)'],
                'url' => 'https://matomo.org/faq/how-to/using-cookieyes-consent-manager-with-matomo',
                ],

            // Note: tarte au citron pro is configured server side so we cannot tell if it has been connected by
            // crawling the website, however setup of Matomo with the pro version is automatic, so displaying the guide
            // link for pro isn't necessary. Only the open source version is detected by this definition.
            'tarteaucitron' => [
                'name' => 'Tarte au Citron',
                'detectStrings' => ['tarteaucitron.js'],
                'connectedStrings' => ['tarteaucitron.user.matomoHost'],
                'url' => 'https://matomo.org/faq/how-to/using-tarte-au-citron-consent-manager-with-matomo',
                ],

            'klaro' => [
                'name' => 'Klaro',
                'detectStrings' => ['klaro.js', 'kiprotect.com'],
                'connectedStrings' => ['KlaroWatcher()', "title: 'Matomo',"],
                'url' => 'https://matomo.org/faq/how-to/using-klaro-consent-manager-with-matomo',
                ],

            'complianz' => [
                'name' => 'Complianz',
                'detectStrings' => ['complianz-gdpr'],
                'connectedStrings' => ["if (!cmplz_in_array( 'statistics', consentedCategories )) {
		_paq.push(['forgetCookieConsentGiven']);"],
                'url' => 'https://matomo.org/faq/how-to/using-complianz-for-wordpress-consent-manager-with-matomo',
                ],
            ];
    }
}
