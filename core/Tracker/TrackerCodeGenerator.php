<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\SettingsPiwik;
use Piwik\View;

/**
 * Generates the Javascript code to be inserted on every page of the website to track.
 */
class TrackerCodeGenerator
{
    /**
     * whether matomo.js|php should be forced over piwik.js|php
     * @var bool
     */
    private $shouldForceMatomoEndpoint = false;

    public function forceMatomoEndpoint()
    {
        $this->shouldForceMatomoEndpoint = true;
    }

    /**
     * @param int $idSite
     * @param string $piwikUrl http://path/to/piwik/site/
     * @param bool $mergeSubdomains
     * @param bool $groupPageTitlesByDomain
     * @param bool $mergeAliasUrls
     * @param array $visitorCustomVariables
     * @param array $pageCustomVariables
     * @param string $customCampaignNameQueryParam
     * @param string $customCampaignKeywordParam
     * @param bool $doNotTrack
     * @param bool $disableCookies
     * @param bool $trackNoScript
     * @param bool $crossDomain
     * @param bool $excludedQueryParams
     * @param array $excludedReferrers
     * @return string Javascript code.
     */
    public function generate(
        $idSite,
        $piwikUrl,
        $mergeSubdomains = false,
        $groupPageTitlesByDomain = false,
        $mergeAliasUrls = false,
        $visitorCustomVariables = null,
        $pageCustomVariables = null,
        $customCampaignNameQueryParam = null,
        $customCampaignKeywordParam = null,
        $doNotTrack = false,
        $disableCookies = false,
        $trackNoScript = false,
        $crossDomain = false,
        $excludedQueryParams = false,
        $excludedReferrers = []
    ) {
        // changes made to this code should be mirrored in plugins/CoreAdminHome/javascripts/jsTrackingGenerator.js var generateJsCode

        if (substr($piwikUrl, 0, 4) !== 'http') {
            $piwikUrl = 'http://' . $piwikUrl;
        }
        preg_match('~^(http|https)://(.*)$~D', $piwikUrl, $matches);
        $piwikUrl = rtrim(@$matches[2], "/");

        // Build optional parameters to be added to text
        $options = '';
        $optionsBeforeTrackerUrl = '';
        if ($groupPageTitlesByDomain) {
            $options .= '  _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);' . "\n";
        }
        if ($crossDomain) {
            // When enabling cross domain, we also need to call `setDomains`
            $mergeAliasUrls = true;
        }
        if ($mergeSubdomains || $mergeAliasUrls) {
            $options .= $this->getJavascriptTagOptions($idSite, $mergeSubdomains, $mergeAliasUrls);
        }

        if ($crossDomain) {
            $options .= '  _paq.push(["enableCrossDomainLinking"]);' . "\n";
        }

        if (Manager::getInstance()->isPluginActivated('CustomVariables')) {
            $maxCustomVars = CustomVariables::getNumUsableCustomVariables();

            if ($visitorCustomVariables && count($visitorCustomVariables) > 0) {
                $options .= '  // you can set up to ' . $maxCustomVars . ' custom variables for each visitor' . "\n";
                $index   = 1;
                foreach ($visitorCustomVariables as $visitorCustomVariable) {
                    if (empty($visitorCustomVariable)) {
                        continue;
                    }

                    $options .= sprintf(
                        '  _paq.push(["setCustomVariable", %d, %s, %s, "visit"]);%s',
                        $index++,
                        json_encode($visitorCustomVariable[0]),
                        json_encode($visitorCustomVariable[1]),
                        "\n"
                    );
                }
            }
            if ($pageCustomVariables && count($pageCustomVariables) > 0) {
                $options .= '  // you can set up to ' . $maxCustomVars . ' custom variables for each action (page view, download, click, site search)' . "\n";
                $index   = 1;
                foreach ($pageCustomVariables as $pageCustomVariable) {
                    if (empty($pageCustomVariable)) {
                        continue;
                    }
                    $options .= sprintf(
                        '  _paq.push(["setCustomVariable", %d, %s, %s, "page"]);%s',
                        $index++,
                        json_encode($pageCustomVariable[0]),
                        json_encode($pageCustomVariable[1]),
                        "\n"
                    );
                }
            }
        }

        if ($customCampaignNameQueryParam) {
            $options .= '  _paq.push(["setCampaignNameKey", '
                . json_encode($customCampaignNameQueryParam) . ']);' . "\n";
        }
        if ($customCampaignKeywordParam) {
            $options .= '  _paq.push(["setCampaignKeywordKey", '
                . json_encode($customCampaignKeywordParam) . ']);' . "\n";
        }
        if ($doNotTrack) {
            $options .= '  _paq.push(["setDoNotTrack", true]);' . "\n";
        }

        // Add any excluded query parameters to the tracker options
        if ($excludedQueryParams) {
            if (!is_array($excludedQueryParams)) {
                $excludedQueryParams = explode(',', $excludedQueryParams);
            }
            $options .= '  _paq.push(["setExcludedQueryParams", ' . json_encode($excludedQueryParams) . ']);' . "\n";
        }

        // Add any ignored referrer to the tracker options
        if ($excludedReferrers) {
            if (!is_array($excludedReferrers)) {
                $excludedReferrers = explode(',', $excludedReferrers);
            }

            $options .= '  _paq.push(["setExcludedReferrers", ' . json_encode($excludedReferrers) . ']);' . "\n";
        }

        if ($disableCookies) {
            $options .= '  _paq.push(["disableCookies"]);' . "\n";
        }

        $codeImpl = array(
            'idSite'                  => $idSite,
            // TODO why sanitizeInputValue() and not json_encode?
            'piwikUrl'                => Common::sanitizeInputValue($piwikUrl),
            'options'                 => $options,
            'optionsBeforeTrackerUrl' => $optionsBeforeTrackerUrl,
            'protocol'                => '//',
            'loadAsync'               => true,
            'trackNoScript'           => $trackNoScript,
            'matomoJsFilename'        => $this->getJsTrackerEndpoint(),
            'matomoPhpFilename'       => $this->getPhpTrackerEndpoint(),
        );

        if (SettingsPiwik::isHttpsForced()) {
            $codeImpl['protocol'] = 'https://';
        }

        $parameters = compact('mergeSubdomains', 'groupPageTitlesByDomain', 'mergeAliasUrls', 'visitorCustomVariables',
            'pageCustomVariables', 'customCampaignNameQueryParam', 'customCampaignKeywordParam',
            'doNotTrack');

        /**
         * Triggered when generating JavaScript tracking code server side. Plugins can use
         * this event to customise the JavaScript tracking code that is displayed to the
         * user.
         *
         * @param array &$codeImpl An array containing snippets of code that the event handler
         *                         can modify. Will contain the following elements:
         *
         *                         - **idSite**: The ID of the site being tracked.
         *                         - **piwikUrl**: The tracker URL to use.
         *                         - **options**: A string of JavaScript code that customises
         *                                        the JavaScript tracker.
         *                         - **optionsBeforeTrackerUrl**: A string of Javascript code that customises
         *                                        the JavaScript tracker inside of anonymous function before
         *                                        adding setTrackerUrl into paq.
         *                         - **protocol**: Piwik url protocol.
         *                         - **loadAsync**: boolean whether piwik.js should be loaded synchronous or asynchronous
         *
         *                         The **httpsPiwikUrl** element can be set if the HTTPS
         *                         domain is different from the normal domain.
         * @param array $parameters The parameters supplied to `TrackerCodeGenerator::generate()`.
         */
        Piwik::postEvent('Tracker.getJavascriptCode', array(&$codeImpl, $parameters));

        $setTrackerUrl = 'var u="' . $codeImpl['protocol'] . '{$piwikUrl}/";';

        if (!empty($codeImpl['httpsPiwikUrl'])) {
            $setTrackerUrl = 'var u=((document.location.protocol === "https:") ? "https://{$httpsPiwikUrl}/" : "http://{$piwikUrl}/");';
            $codeImpl['httpsPiwikUrl'] = rtrim($codeImpl['httpsPiwikUrl'], "/");
        }
        $codeImpl = array('setTrackerUrl' => htmlentities($setTrackerUrl, ENT_COMPAT | ENT_HTML401, 'UTF-8')) + $codeImpl;

        $view = new View('@Morpheus/javascriptCode');
        $view->disableCacheBuster();
        $view->loadAsync = $codeImpl['loadAsync'];
        $view->trackNoScript = $codeImpl['trackNoScript'];
        $jsCode = $view->render();
        $jsCode = htmlentities($jsCode, ENT_COMPAT | ENT_HTML401, 'UTF-8');

        foreach ($codeImpl as $keyToReplace => $replaceWith) {
            $jsCode = str_replace('{$' . $keyToReplace . '}', $replaceWith, $jsCode);
        }

        return $jsCode;
    }

    public function getJsTrackerEndpoint()
    {
        $name = 'matomo.js';
        if ($this->shouldPreferPiwikEndpoint()) {
            $name = 'piwik.js';
        }
        return $name;
    }

    public function getPhpTrackerEndpoint()
    {
        $name = 'matomo.php';
        if ($this->shouldPreferPiwikEndpoint()) {
            $name = 'piwik.php';
        }
        return $name;
    }

    public function shouldPreferPiwikEndpoint()
    {
        if ($this->shouldForceMatomoEndpoint) {
            return false;
        }

        // only since 3.7.0 we use the default matomo.js|php... for all other installs we need to keep BC
        return DbHelper::wasMatomoInstalledBeforeVersion('3.7.0-b1');
    }

    private function getJavascriptTagOptions($idSite, $mergeSubdomains, $mergeAliasUrls)
    {
        try {
            $websiteUrls = APISitesManager::getInstance()->getSiteUrlsFromId($idSite);
        } catch (\Exception $e) {
            return '';
        }
        // We need to parse_url to isolate hosts
        $websiteHosts = array();
        $firstHost = null;
        foreach ($websiteUrls as $site_url) {
            if (empty($site_url)) {
                continue;
            }
            
            $referrerParsed = parse_url($site_url);

            if (!isset($firstHost) && isset($referrerParsed['host'])) {
                $firstHost = $referrerParsed['host'];
            }

            if (isset($referrerParsed['host'])) {
                $url = $referrerParsed['host'];
            } else {
                $url = '';
            }
            if (!empty($referrerParsed['path'])) {
                $url .= $referrerParsed['path'];
            }
            
            if (!empty($url)) {
                $websiteHosts[] = $url;
            }
        }
        $options = '';
        if ($mergeSubdomains && !empty($firstHost)) {
            $options .= '  _paq.push(["setCookieDomain", "*.' . $firstHost . '"]);' . "\n";
        }
        if ($mergeAliasUrls && !empty($websiteHosts)) {
            $urls = '["*.' . implode('","*.', $websiteHosts) . '"]';
            $options .= '  _paq.push(["setDomains", ' . $urls . ']);' . "\n";
        }
        return $options;
    }

    /**
     * When including the JS tracking code in a mailto link, we need to strip the surrounding HTML tags off. This
     * ensures consistent behaviour between mail clients that render the mailto body as plain text (as in the
     * spec), and those which try to render it as HTML and therefore hide the tags.
     * @param string $jsTrackingCode JS tracking code as returned from the generate() function.
     * @return string
     */
    public static function stripTags($jsTrackingCode)
    {
        // Strip off open and close <script> tag and comments so that JS will be displayed in ALL mail clients
        return trim(strip_tags(html_entity_decode($jsTrackingCode)));
    }
}
