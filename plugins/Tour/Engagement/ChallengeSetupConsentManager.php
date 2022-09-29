<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Tour\Engagement;

use Piwik\Common;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Site;

class ChallengeSetupConsentManager extends Challenge
{

    private $consentManagerId = null;
    private $consentManagerName = null;
    private $isConnected = false;

    public function __construct($siteData = null)
    {

        parent::__construct();

        if ($siteData === null) {
            // Grab the current site main page as a string
            $idSite = Common::getRequestVar('idSite', null, 'int');
            if (!$idSite) {
                return;
            }

            $url = Site::getMainUrlFor($idSite);
            if (!$url) {
                return;
            }

            $siteData = Http::sendHttpRequestBy('curl', $url, 60, null, null,
                null, 0, false, true);

        }

        // Loop the consent manager definitions and attempt to detect based on string matching
        $defs = $this->getConsentManagerDefinitions();
        foreach ($defs as $consentManagerId => $consentManagerDef) {
            foreach ($consentManagerDef['detectStrings'] as $dStr) {
                if (strpos($siteData, $dStr) !== false) {
                    $this->consentManagerId = $consentManagerId;
                    break 2;
                }
            }
        }

        // If a consent manager was detected then perform an additional check to see if it has been connected to Matomo
        if ($this->consentManagerId !== null) {

            if ($defs && array_key_exists($this->consentManagerId, $defs)) {

                $this->consentManagerName = $defs[$this->consentManagerId]['name'];

                foreach ($defs[$this->consentManagerId]['connectedStrings'] as $cStr) {
                    if (strpos($siteData, $cStr) !== false) {
                        $this->isConnected = true;
                        break;
                    }
                }
            }
        }

    }

    private function getConsentManagerDefinitions() : array
    {
        return [

            'osano' => [
                'name' => 'Osano',
                'detectStrings' => ['osano.com'],
                'connectedStrings' => ["Osano.cm.addEventListener('osano-cm-consent-changed', (change) => { console.log('cm-change'); consentSet(change); });"],
                'url' => 'https://matomo.org/faq/how-to/#using-osano-consent-manager-with-matomo',
                ],

            'cookiebot' => [
                'name' => 'Cookiebot',
                'detectStrings' => ['cookiebot.com'],
                'connectedStrings' => ["typeof _paq === 'undefined' || typeof Cookiebot === 'undefined'"],
                'url' => 'https://matomo.org/faq/how-to/#using-cookiebot-consent-manager-with-matomo',
                ],

            'cookieyes' => [
                'name' => 'CookieYes',
                'detectStrings' => ['cookieyes.com'],
                'connectedStrings' => ['document.addEventListener("cookieyes_consent_update", function (eventData)'],
                'url' => 'https://matomo.org/faq/how-to/#using-cookieyes-consent-manager-with-matomo',
                ],

            // Note: tarte au citron pro is configured server side so we cannot tell if it has been connected by
            // crawling the website, however setup of Matomo with the pro version is automatic, so displaying the guide
            // link for pro isn't necessary. Only the open source version is detected by this definition.
            'tarteaucitron' => [
                'name' => 'Tarte au Citron',
                'detectStrings' => ['tarteaucitron.js'],
                'connectedStrings' => ['tarteaucitron.user.matomoHost'],
                'url' => 'https://matomo.org/faq/how-to/#using-tarte-au-citron-consent-manager-with-matomo',
                ],

            'klaro' => [
                'name' => 'Klaro',
                'detectStrings' => ['klaro.js', 'kiprotect.com'],
                'connectedStrings' => ['KlaroWatcher()', "title: 'Matomo',"],
                'url' => 'https://matomo.org/faq/how-to/#using-klaro-consent-manager-with-matomo',
                ],

            'complianz' => [
                'name' => 'Complianz',
                'detectStrings' => ['complianz-gdpr'],
                'connectedStrings' => ["if (!cmplz_in_array( 'statistics', consentedCategories )) {
		_paq.push(['forgetCookieConsentGiven']);"],
                'url' => 'https://matomo.org/faq/how-to/#using-complianz-for-wordpress-consent-manager-with-matomo',
                ],
            ];
    }

    public function getName()
    {
        return Piwik::translate('Tour_ConnectConsentManager', [$this->consentManagerName]);
    }

    public function getDescription()
    {
        return Piwik::translate('Tour_ConnectConsentManagerIntro', [$this->consentManagerName]);
    }

    public function getId()
    {
        return 'setup_consent_manager';
    }

    public function getConsentManagerId()
    {
        return $this->consentManagerId;
    }

    public function isCompleted()
    {

        if (!$this->consentManagerName) {
            return true;
        }

        return $this->isConnected;
    }

    public function isDisabled()
    {
        return ($this->consentManagerId === null);
    }

    public function getUrl()
    {
        if ($this->consentManagerId === null) {
            return '';
        }

        return $this->getConsentManagerDefinitions()[$this->consentManagerId]['url'];

    }

}