<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
     * Elements of the tracking code that are JavaScript rather than a value, and are therefore not
     * stripped of the characters a value must not contain.
     */
    private const CODE_ELEMENTS = ['options', 'optionsBeforeTrackerUrl'];

    /**
     * Characters that would end the JavaScript string or the HTML attribute a value is placed in. The
     * generated code is used as it is once copied, so a value has to be safe on its own.
     */
    private const CHARACTERS_BREAKING_THE_CODE = '~[\x00-\x20\x7f"\'<>`{$}\\\\]~';

    /**
     * whether matomo.js|php should be forced over piwik.js|php
     */
    private bool $shouldForceMatomoEndpoint = false;

    public function forceMatomoEndpoint()
    {
        $this->shouldForceMatomoEndpoint = true;
    }

    /**
     * @param int $idSite
     * @param string $piwikUrl https://path/to/piwik/site/
     * @param bool $mergeSubdomains
     * @param bool $groupPageTitlesByDomain
     * @param bool $mergeAliasUrls
     * @param array|null $visitorCustomVariables
     * @param array|null $pageCustomVariables
     * @param string|null $customCampaignNameQueryParam
     * @param string|null $customCampaignKeywordParam
     * @param bool $doNotTrack
     * @param bool $disableCookies
     * @param bool $trackNoScript
     * @param bool $crossDomain
     * @param string|string[]|false $excludedQueryParams
     * @param string|string[] $excludedReferrers
     * @param bool $disableCampaignParameters
     * @return string The JavaScript tracking code, HTML escaped for direct embedding.
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
        $excludedReferrers = [],
        $disableCampaignParameters = false
    ) {
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

            if (is_array($visitorCustomVariables) && count($visitorCustomVariables) > 0) {
                $options .= '  // you can set up to ' . $maxCustomVars . ' custom variables for each visitor' . "\n";
                $index   = 1;
                foreach ($visitorCustomVariables as $visitorCustomVariable) {
                    if (empty($visitorCustomVariable)) {
                        continue;
                    }

                    $options .= sprintf(
                        '  _paq.push(["setCustomVariable", %d, %s, %s, "visit"]);%s',
                        $index++,
                        self::jsonEncodeValue($visitorCustomVariable[0]),
                        self::jsonEncodeValue($visitorCustomVariable[1]),
                        "\n"
                    );
                }
            }
            if (is_array($pageCustomVariables) && count($pageCustomVariables) > 0) {
                $options .= '  // you can set up to ' . $maxCustomVars . ' custom variables for each action (page view, download, click, site search)' . "\n";
                $index   = 1;
                foreach ($pageCustomVariables as $pageCustomVariable) {
                    if (empty($pageCustomVariable)) {
                        continue;
                    }
                    $options .= sprintf(
                        '  _paq.push(["setCustomVariable", %d, %s, %s, "page"]);%s',
                        $index++,
                        self::jsonEncodeValue($pageCustomVariable[0]),
                        self::jsonEncodeValue($pageCustomVariable[1]),
                        "\n"
                    );
                }
            }
        }

        if ($disableCampaignParameters) {
            $options .= '  _paq.push(["disableCampaignParameters"]);' . "\n";
        }

        if ($customCampaignNameQueryParam) {
            $options .= '  _paq.push(["setCampaignNameKey", '
                . self::jsonEncodeValue($customCampaignNameQueryParam) . ']);' . "\n";
        }

        if ($customCampaignKeywordParam) {
            $options .= '  _paq.push(["setCampaignKeywordKey", '
                . self::jsonEncodeValue($customCampaignKeywordParam) . ']);' . "\n";
        }

        if ($doNotTrack) {
            $options .= '  _paq.push(["setDoNotTrack", true]);' . "\n";
        }

        // Add any excluded query parameters to the tracker options
        if ($excludedQueryParams) {
            if (!is_array($excludedQueryParams)) {
                $excludedQueryParams = explode(',', $excludedQueryParams);
            }
            $options .= '  _paq.push(["setExcludedQueryParams", '
                . self::jsonEncodeValue($excludedQueryParams) . ']);' . "\n";
        }

        // Add any ignored referrer to the tracker options
        if ($excludedReferrers) {
            if (!is_array($excludedReferrers)) {
                $excludedReferrers = explode(',', $excludedReferrers);
            }

            $options .= '  _paq.push(["setExcludedReferrers", '
                . self::jsonEncodeValue($excludedReferrers) . ']);' . "\n";
        }

        if ($disableCookies) {
            $options .= '  _paq.push(["disableCookies"]);' . "\n";
        }

        // a plugin can add or change any element through the event below
        /** @var array<string, mixed> $codeImpl */
        $codeImpl = array(
            'idSite'                  => $idSite,
            'piwikUrl'                => $piwikUrl,
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

        $parameters = compact(
            'mergeSubdomains',
            'groupPageTitlesByDomain',
            'mergeAliasUrls',
            'visitorCustomVariables',
            'pageCustomVariables',
            'customCampaignNameQueryParam',
            'customCampaignKeywordParam',
            'doNotTrack',
            'disableCampaignParameters'
        );

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
         *
         *                         Every element is HTML escaped before it is substituted into the
         *                         tracking code, so a handler must not escape its value itself, and
         *                         every element has to stay a scalar value, as anything else throws.
         *
         *                         Values are used as given, so pass them unsanitized. Except for
         *                         **options** and **optionsBeforeTrackerUrl**, which are JavaScript,
         *                         characters that would end the string or attribute an element sits
         *                         in are removed, or encoded in the two URLs.
         * @param array $parameters The parameters supplied to `TrackerCodeGenerator::generate()`.
         */
        Piwik::postEvent('Tracker.getJavascriptCode', array(&$codeImpl, $parameters));

        foreach ($codeImpl as $key => $value) {
            if (null !== $value && !is_scalar($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'The %s element of the tracking code must be a scalar value, %s given.',
                    $key,
                    gettype($value)
                ));
            }
        }

        // built into the code here rather than substituted, so it has to be stripped up front
        $codeImpl['protocol'] = self::stripCharactersBreakingTheCode((string) $codeImpl['protocol']);

        // the only place the URL is normalised, as normalising decodes and is not idempotent
        $codeImpl['piwikUrl'] = self::normalizeTrackerUrl($codeImpl['piwikUrl']);

        // the noscript URL carries the id in its query string, encoded, but from the stripped value
        // so that both requests report the same site
        $codeImpl['idSite'] = self::stripCharactersBreakingTheCode((string) $codeImpl['idSite']);
        $codeImpl['idSiteUrlEncoded'] = urlencode($codeImpl['idSite']);

        $setTrackerUrl = 'var u="' . $codeImpl['protocol'] . '{$piwikUrl}/";';

        // normalised before it is tested, as one left without a host by it must not be used
        $httpsPiwikUrl = self::normalizeTrackerUrl($codeImpl['httpsPiwikUrl'] ?? '');

        if ('' !== $httpsPiwikUrl) {
            $codeImpl['httpsPiwikUrl'] = $httpsPiwikUrl;
            $setTrackerUrl = 'var u=((document.location.protocol === "https:") ? "https://{$httpsPiwikUrl}/" : "http://{$piwikUrl}/");';
        }

        $view = new View('@Morpheus/javascriptCode');
        $view->disableCacheBuster();
        $view->loadAsync = $codeImpl['loadAsync'];
        $view->trackNoScript = $codeImpl['trackNoScript'];
        $jsCode = $view->render();
        $jsCode = self::escapeForHtml($jsCode);

        // the rendered template is escaped as a whole, so every value substituted afterwards needs to be
        // escaped on its own
        $replacements = [];
        foreach ($codeImpl as $keyToReplace => $replaceWith) {
            $value = (string) $replaceWith;

            if (!in_array($keyToReplace, self::CODE_ELEMENTS, true)) {
                $value = self::stripCharactersBreakingTheCode($value);
            }

            $replacements['{$' . $keyToReplace . '}'] = self::escapeForHtml($value);
        }

        // resolved first, as it contains the piwikUrl and httpsPiwikUrl placeholders itself
        $jsCode = str_replace('{$setTrackerUrl}', self::escapeForHtml($setTrackerUrl), $jsCode);

        // single pass, so that a value containing a placeholder is not expanded any further
        return strtr($jsCode, $replacements);
    }

    /**
     * Removes the characters that would end the JavaScript string or the HTML attribute a value is placed
     * in. Used for the ids and filenames of the tracking code, which have no encoding of their own. The
     * tracker URL is encoded instead, as an apostrophe is valid in a path.
     */
    private static function stripCharactersBreakingTheCode(string $value): string
    {
        return (string) preg_replace(self::CHARACTERS_BREAKING_THE_CODE, '', $value);
    }

    private static function escapeForHtml(string $value): string
    {
        return htmlspecialchars($value, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * JSON encodes a value for use inside the tracking code. Values may be stored sanitized, so they are
     * unsanitized first, as json_encode can only escape quotes it actually sees.
     *
     * @param mixed $value
     */
    private static function jsonEncodeValue($value): string
    {
        return (string) json_encode(
            Common::unsanitizeInputValues($value),
            JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }

    /**
     * Returns the given Matomo URL without its protocol and trailing slashes, as the generated tracking
     * code adds the protocol itself.
     *
     * @param string|null $piwikUrl
     */
    public static function normalizeTrackerUrl($piwikUrl): string
    {
        $piwikUrl = trim(Common::unsanitizeInputValue((string) $piwikUrl));
        $piwikUrl = (string) preg_replace('~^[a-z][a-z0-9+.-]*://~i', '', $piwikUrl);
        // encoded rather than removed, so that a path containing one of them still resolves
        $piwikUrl = (string) preg_replace_callback(
            self::CHARACTERS_BREAKING_THE_CODE,
            static function (array $matches): string {
                return rawurlencode($matches[0]);
            },
            $piwikUrl
        );

        return rtrim($piwikUrl, '/');
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
            $websiteUrls = Common::unsanitizeInputValues(
                APISitesManager::getInstance()->getSiteUrlsFromId($idSite)
            );
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
            $options .= '  _paq.push(["setCookieDomain", '
                . json_encode(
                    '*.' . $firstHost,
                    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE
                ) . ']);' . "\n";
        }
        if ($mergeAliasUrls && !empty($websiteHosts)) {
            $urls = array_map(static function ($host) {
                return '*.' . $host;
            }, $websiteHosts);
            $options .= '  _paq.push(["setDomains", '
                . json_encode(
                    $urls,
                    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE
                ) . ']);' . "\n";
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
        return trim(strip_tags(
            html_entity_decode($jsTrackingCode, ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE, 'UTF-8')
        ));
    }
}
