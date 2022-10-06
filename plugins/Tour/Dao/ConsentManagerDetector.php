<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Dao;

use Piwik\Common;
use Piwik\Http;
use Piwik\Site;

class ConsentManagerDetector
{

    public $consentManagerId;               // Id of the detected consent manager, eg. 'osano'
    public $consentManagerName;             // Display name of the detected consent manager, eg. 'Osano'
    public $consentManagerUrl;              // Url for the configuration guide for the detected consent manager
    public $isConnected;                    // True if the detected consent manager is already connected with Matomo

    /**
     * Construct a new ConsentManagerDetector class, this will query the site and populate the class properties with
     * the details of any detected consent manager
     *
     * @param string|null $siteData  String containing the site data to search, if blank then data will be retrieved
     *                               from the current request site via cURL
     * @param int $timeOut           How long to wait for the site to response, defaults to 60 seconds
     */
    public function __construct(?string $siteData = null, int $timeOut = 60)
    {
         if ($siteData === null) {
            // Grab the current site main page as a string
            if (!isset($_REQUEST['idSite'])) {
                return;
            }
            $idSite = Common::getRequestVar('idSite', null, 'int');
            if (!$idSite) {
                return;
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

        if ($siteData === null) {
            return;
        }

        // Loop the consent manager definitions and attempt to detect based on string matching
        $defs = ConsentManagerDetector::getConsentManagerDefinitions();
        if (!$defs) {
            return;
        }

        foreach ($defs as $consentManagerId => $consentManagerDef) {
            foreach ($consentManagerDef['detectStrings'] as $dStr) {
                if (strpos($siteData, $dStr) !== false && array_key_exists($consentManagerId, $defs)) {
                    $this->consentManagerId = $consentManagerId;
                    $this->consentManagerName = $consentManagerDef['name'];
                    $this->consentManagerUrl = $consentManagerDef['url'];
                    break 2;
                }
            }
        }

        // If a consent manager was detected then perform an additional check to see if it has been connected to Matomo
        foreach ($defs[$this->consentManagerId]['connectedStrings'] as $cStr) {
            if (strpos($siteData, $cStr) !== false) {
                $this->isConnected = true;
                break;
            }
        }

    }

    /**
     * Return an array of consent manager definitions which can be used to detect their prescence and show guide links
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

}