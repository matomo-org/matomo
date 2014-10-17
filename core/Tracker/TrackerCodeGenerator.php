<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\SitesManager\API as APISitesManager;

/**
 * Generates the Javascript code to be inserted on every page of the website to track.
 */
class TrackerCodeGenerator
{
    /**
     * @param int $idSite
     * @param string $piwikUrl http://path/to/piwik/site/
     * @param bool $mergeSubdomains
     * @param bool $groupPageTitlesByDomain
     * @param bool $mergeAliasUrls
     * @param bool $visitorCustomVariables
     * @param bool $pageCustomVariables
     * @param bool $customCampaignNameQueryParam
     * @param bool $customCampaignKeywordParam
     * @param bool $doNotTrack
     * @param bool $disableCookies
     * @return string Javascript code.
     */
    public function generate($idSite, $piwikUrl, $mergeSubdomains = false, $groupPageTitlesByDomain = false,
                             $mergeAliasUrls = false, $visitorCustomVariables = false, $pageCustomVariables = false,
                             $customCampaignNameQueryParam = false, $customCampaignKeywordParam = false,
                             $doNotTrack = false, $disableCookies = false)
    {
        // changes made to this code should be mirrored in plugins/CoreAdminHome/javascripts/jsTrackingGenerator.js var generateJsCode
        $jsCode = file_get_contents(PIWIK_INCLUDE_PATH . "/plugins/Morpheus/templates/javascriptCode.tpl");
        $jsCode = htmlentities($jsCode);
        if (substr($piwikUrl, 0, 4) !== 'http') {
            $piwikUrl = 'http://' . $piwikUrl;
        }
        preg_match('~^(http|https)://(.*)$~D', $piwikUrl, $matches);
        $piwikUrl = rtrim(@$matches[2], "/");

        // Build optional parameters to be added to text
        $options = '';
        $optionsBeforeTrackerUrl = '';
        if ($groupPageTitlesByDomain) {
            $options .= '  _paq.push(["setDocumentTitle", document.domain + "/" + document.title]);' . PHP_EOL;
        }
        if ($mergeSubdomains || $mergeAliasUrls) {
            $options .= $this->getJavascriptTagOptions($idSite, $mergeSubdomains, $mergeAliasUrls);
        }
        $maxCustomVars = CustomVariables::getMaxCustomVariables();

        if ($visitorCustomVariables) {
            $options .= '  // you can set up to ' . $maxCustomVars . ' custom variables for each visitor' . PHP_EOL;
            $index = 1;
            foreach ($visitorCustomVariables as $visitorCustomVariable) {
                if (empty($visitorCustomVariable)) {
                    continue;
                }

                $options .= '  _paq.push(["setCustomVariable", ' . $index++ . ', "' . $visitorCustomVariable[0] . '", "' . $visitorCustomVariable[1] . '", "visit"]);' . PHP_EOL;
            }
        }
        if ($pageCustomVariables) {
            $options .= '  // you can set up to ' . $maxCustomVars . ' custom variables for each action (page view, download, click, site search)' . PHP_EOL;
            $index = 1;
            foreach ($pageCustomVariables as $pageCustomVariable) {
                if (empty($pageCustomVariable)) {
                    continue;
                }
                $options .= '  _paq.push(["setCustomVariable", ' . $index++ . ', "' . $pageCustomVariable[0] . '", "' . $pageCustomVariable[1] . '", "page"]);' . PHP_EOL;
            }
        }
        if ($customCampaignNameQueryParam) {
            $options .= '  _paq.push(["setCampaignNameKey", "' . $customCampaignNameQueryParam . '"]);' . PHP_EOL;
        }
        if ($customCampaignKeywordParam) {
            $options .= '  _paq.push(["setCampaignKeywordKey", "' . $customCampaignKeywordParam . '"]);' . PHP_EOL;
        }
        if ($doNotTrack) {
            $options .= '  _paq.push(["setDoNotTrack", true]);' . PHP_EOL;
        }
        if ($disableCookies) {
            $options .= '  _paq.push(["disableCookies"]);' . PHP_EOL;
        }

        $codeImpl = array(
            'idSite'                  => $idSite,
            'piwikUrl'                => Common::sanitizeInputValue($piwikUrl),
            'options'                 => $options,
            'optionsBeforeTrackerUrl' => $optionsBeforeTrackerUrl,
            'protocol'                => '//'
        );
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
         *
         *                         The **httpsPiwikUrl** element can be set if the HTTPS
         *                         domain is different from the normal domain.
         * @param array $parameters The parameters supplied to `TrackerCodeGenerator::generate()`.
         */
        Piwik::postEvent('Piwik.getJavascriptCode', array(&$codeImpl, $parameters));

        $setTrackerUrl = 'var u="' . $codeImpl['protocol'] . '{$piwikUrl}/";';

        if (!empty($codeImpl['httpsPiwikUrl'])) {
            $setTrackerUrl = 'var u=((document.location.protocol === "https:") ? "https://{$httpsPiwikUrl}/" : "http://{$piwikUrl}/");';
            $codeImpl['httpsPiwikUrl'] = rtrim($codeImpl['httpsPiwikUrl'], "/");
        }
        $codeImpl = array('setTrackerUrl' => htmlentities($setTrackerUrl)) + $codeImpl;

        foreach ($codeImpl as $keyToReplace => $replaceWith) {
            $jsCode = str_replace('{$' . $keyToReplace . '}', $replaceWith, $jsCode);
        }

        return $jsCode;
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
        foreach ($websiteUrls as $site_url) {
            $referrerParsed = parse_url($site_url);
            $websiteHosts[] = $referrerParsed['host'];
        }
        $options = '';
        if ($mergeSubdomains && !empty($websiteHosts)) {
            $options .= '  _paq.push(["setCookieDomain", "*.' . $websiteHosts[0] . '"]);' . PHP_EOL;
        }
        if ($mergeAliasUrls && !empty($websiteHosts)) {
            $urls = '["*.' . implode('","*.', $websiteHosts) . '"]';
            $options .= '  _paq.push(["setDomains", ' . $urls . ']);' . PHP_EOL;
        }
        return $options;
    }
}
