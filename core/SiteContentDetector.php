<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Container\StaticContainer;

/**
 * This class provides detection functions for specific content on a site. It can be used to easily detect the
 * presence of known third party code.
 *
 * Note: Calling the detect() method will create a HTTP request to the site to retrieve data, only the main site URL
 * will be checked
 *
 * Usage:
 *
 * $contentDetector = SiteContentDetector::getInstance();
 * $contentDetector->detectContent([SiteContentDetector::GA3]);
 * if ($contentDetector->ga3) {
 *      // site is using GA3
 * }
 *
 */
class SiteContentDetector
{

    const TEST_SETTINGS_OPTION_NAME = 'site_content_detector.test_settings';

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
    private $siteId;

    /**
     * @return SiteContentDetector
     */
    public static function getInstance(): SiteContentDetector
    {
        return StaticContainer::get('Piwik\SiteContentDetector');
    }

    /**
     * Reset the detection properties
     */
    private function resetDetectionProperties() : void
    {
        $this->consentManagerId = null;
        $this->consentManagerUrl = null;
        $this->consentManagerName = null;
        $this->isConnected;
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
     *                                   from the current request site via cURL
     * @param int         $timeOut       How long to wait for the site to response, defaults to 60 seconds
     */
    public function detectContent(array $detectContent = [SiteContentDetector::ALL_CONTENT],
                                  ?int $idSite = null, ?string $siteData = null, int $timeOut = 60)
    {

        // Return test data if option set
        if ($this->checkForTestData()) {
            return;
        }

        // If the site data was already retrieved and stored in this object and it is for the same site id and we're
        // not being passed a specific sitedata parameter then avoid making another request and just return
        if ($siteData === null && $this->siteData != null && $idSite == $this->siteId) {
            return;
        }

        $this->resetDetectionProperties();

        // No site data was passed or previously retrieved, so grab the current site main page as a string
        if ($siteData === null) {


            if ($idSite === null) {
                if (!isset($_REQUEST['idSite'])) {
                    return;
                }
                $idSite = Common::getRequestVar('idSite', null, 'int');
                if (!$idSite) {
                    return;
                }
            }

            $url = Site::getMainUrlFor($idSite);
            if (!$url) {
                return;
            }

            try {
                $siteData = Http::sendHttpRequestBy('curl', $url, $timeOut, null, null,
                    null, 0, false, true);
            } catch (\Exception $e) {
            }

        }

        // Abort if still no site data
        if ($siteData === null || $siteData === '') {
            return;
        }

        $this->siteData = $siteData;
        $this->siteId = $idSite;

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
     * Detect known consent managers in the site data
     *
     * Populate this object's properties with the results
     *
     * @return void
     */
    private function detectConsentManager() : void
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
    private function detectGA3() : void
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
    private function detectGA4() : void
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
    private function detectGTM() : void
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
    public static function getConsentManagerDefinitions() : array
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

    /**
     * Check if test data should be returned instead of checking a real site
     *
     * The option will provide the object properties that should be set
     *
     * @return bool  True if test settings were used
     */
    private function checkForTestData() : bool
    {
        $testData = null;
        try {
            $optionValue = Option::get(self::TEST_SETTINGS_OPTION_NAME);
            $testData = json_decode($optionValue, true);
        } catch (\Exception $e) {
            $optionValue = false;
        }
        if (!$optionValue || $testData === null) {
            return false;
        }

        // Apply test settings
        foreach ($testData as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            }
        }

        return true;
    }

    /**
     * Set test data to be returned by detect()
     *
     * Should be removed by clearTestData() after use
     *
     * @param array $testData
     */
    public function setTestData(array $testData) : void
    {
        Option::set(self::TEST_SETTINGS_OPTION_NAME, json_encode($testData));
    }

    /**
     * Clear the test data option
     */
    public function clearTestData() : void
    {
        Option::delete(self::TEST_SETTINGS_OPTION_NAME);
    }

}