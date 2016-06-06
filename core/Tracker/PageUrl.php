<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\UrlHelper;

class PageUrl
{

    /**
     * Map URL prefixes to integers.
     * @see self::normalizeUrl(), self::reconstructNormalizedUrl()
     */
    public static $urlPrefixMap = array(
        'http://www.'  => 1,
        'http://'      => 0,
        'https://www.' => 3,
        'https://'     => 2
    );

    /**
     * Given the Input URL, will exclude all query parameters set for this site
     *
     * @static
     * @param $originalUrl
     * @param $idSite
     * @return bool|string Returned URL is HTML entities decoded
     */
    public static function excludeQueryParametersFromUrl($originalUrl, $idSite)
    {
        $originalUrl = self::cleanupUrl($originalUrl);

        $parsedUrl = @parse_url($originalUrl);
        $parsedUrl = self::cleanupHostAndHashTag($parsedUrl, $idSite);
        $parametersToExclude = self::getQueryParametersToExclude($idSite);

        if (empty($parsedUrl['query'])) {
            if (empty($parsedUrl['fragment'])) {
                return UrlHelper::getParseUrlReverse($parsedUrl);
            }

            // Exclude from the hash tag as well
            $queryParameters = UrlHelper::getArrayFromQueryString($parsedUrl['fragment']);
            $parsedUrl['fragment'] = UrlHelper::getQueryStringWithExcludedParameters($queryParameters, $parametersToExclude);
            $url = UrlHelper::getParseUrlReverse($parsedUrl);

            return $url;
        }

        $queryParameters = UrlHelper::getArrayFromQueryString($parsedUrl['query']);
        $parsedUrl['query'] = UrlHelper::getQueryStringWithExcludedParameters($queryParameters, $parametersToExclude);
        $url = UrlHelper::getParseUrlReverse($parsedUrl);

        return $url;
    }

    /**
     * Returns the array of parameters names that must be excluded from the Query String in all tracked URLs
     * @static
     * @param $idSite
     * @return array
     */
    public static function getQueryParametersToExclude($idSite)
    {
        $campaignTrackingParameters = Common::getCampaignParameters();

        $campaignTrackingParameters = array_merge(
            $campaignTrackingParameters[0], // campaign name parameters
            $campaignTrackingParameters[1] // campaign keyword parameters
        );

        $website = Cache::getCacheWebsiteAttributes($idSite);
        $excludedParameters = self::getExcludedParametersFromWebsite($website);

        $parametersToExclude = array_merge($excludedParameters,
                                           self::getUrlParameterNamesToExcludeFromUrl(),
                                           $campaignTrackingParameters);

        /**
         * Triggered before setting the action url in Piwik\Tracker\Action so plugins can register
         * parameters to be excluded from the tracking URL (e.g. campaign parameters).
         *
         * @param array &$parametersToExclude An array of parameters to exclude from the tracking url.
         */
        Piwik::postEvent('Tracker.PageUrl.getQueryParametersToExclude', array(&$parametersToExclude));

        if (!empty($parametersToExclude)) {
            Common::printDebug('Excluding parameters "' . implode(',', $parametersToExclude) . '" from URL');
        }

        $parametersToExclude = array_map('strtolower', $parametersToExclude);
        return $parametersToExclude;
    }

    /**
     * Returns the list of URL query parameters that should be removed from the tracked URL query string.
     *
     * @return array
     */
    protected static function getUrlParameterNamesToExcludeFromUrl()
    {
        $paramsToExclude = Config::getInstance()->Tracker['url_query_parameter_to_exclude_from_url'];
        $paramsToExclude = explode(",", $paramsToExclude);
        $paramsToExclude = array_map('trim', $paramsToExclude);
        return $paramsToExclude;
    }

    /**
     * Returns true if URL fragments should be removed for a specific site,
     * false if otherwise.
     *
     * This function uses the Tracker cache and not the MySQL database.
     *
     * @param $idSite int The ID of the site to check for.
     * @return bool
     */
    public static function shouldRemoveURLFragmentFor($idSite)
    {
        $websiteAttributes = Cache::getCacheWebsiteAttributes($idSite);
        return empty($websiteAttributes['keep_url_fragment']);
    }

    /**
     * Cleans and/or removes the URL fragment of a URL.
     *
     * @param $urlFragment      string The URL fragment to process.
     * @param $idSite           int|bool  If not false, this function will check if URL fragments
     *                          should be removed for the site w/ this ID and if so,
     *                          the returned processed fragment will be empty.
     *
     * @return string The processed URL fragment.
     */
    public static function processUrlFragment($urlFragment, $idSite = false)
    {
        // if we should discard the url fragment for this site, return an empty string as
        // the processed url fragment
        if ($idSite !== false
            && PageUrl::shouldRemoveURLFragmentFor($idSite)
        ) {
            return '';
        } else {
            // Remove trailing Hash tag in ?query#hash#
            if (substr($urlFragment, -1) == '#') {
                $urlFragment = substr($urlFragment, 0, strlen($urlFragment) - 1);
            }
            return $urlFragment;
        }
    }

    /**
     * Will cleanup the hostname (some browser do not strolower the hostname),
     * and deal ith the hash tag on incoming URLs based on website setting.
     *
     * @param $parsedUrl
     * @param $idSite int|bool  The site ID of the current visit. This parameter is
     *                          only used by the tracker to see if we should remove
     *                          the URL fragment for this site.
     * @return array
     */
    protected static function cleanupHostAndHashTag($parsedUrl, $idSite = false)
    {
        if (empty($parsedUrl)) {
            return $parsedUrl;
        }

        if (!empty($parsedUrl['host'])) {
            $parsedUrl['host'] = Common::mb_strtolower($parsedUrl['host'], 'UTF-8');
        }

        if (!empty($parsedUrl['fragment'])) {
            $parsedUrl['fragment'] = PageUrl::processUrlFragment($parsedUrl['fragment'], $idSite);
        }

        return $parsedUrl;
    }

    /**
     * Converts Matrix URL format
     * from http://example.org/thing;paramA=1;paramB=6542
     * to   http://example.org/thing?paramA=1&paramB=6542
     *
     * @param string $originalUrl
     * @return string
     */
    public static function convertMatrixUrl($originalUrl)
    {
        $posFirstSemiColon = strpos($originalUrl, ";");

        if (false === $posFirstSemiColon) {
            return $originalUrl;
        }

        $posQuestionMark = strpos($originalUrl, "?");
        $replace = (false === $posQuestionMark);

        if ($posQuestionMark > $posFirstSemiColon) {
            $originalUrl = substr_replace($originalUrl, ";", $posQuestionMark, 1);
            $replace = true;
        }

        if ($replace) {
            $originalUrl = substr_replace($originalUrl, "?", strpos($originalUrl, ";"), 1);
            $originalUrl = str_replace(";", "&", $originalUrl);
        }

        return $originalUrl;
    }

    /**
     * Clean up string contents (filter, truncate, ...)
     *
     * @param string $string Dirty string
     * @return string
     */
    public static function cleanupString($string)
    {
        $string = trim($string);
        $string = str_replace(array("\n", "\r", "\0"), '', $string);

        $limit = Config::getInstance()->Tracker['page_maximum_length'];
        $clean = substr($string, 0, $limit);
        return $clean;
    }

    protected static function reencodeParameterValue($value, $encoding)
    {
        if (is_string($value)) {
            $decoded = urldecode($value);
            if (function_exists('mb_check_encoding')
                && @mb_check_encoding($decoded, $encoding)) {
                $value = urlencode(mb_convert_encoding($decoded, 'UTF-8', $encoding));
            }
        }

        return $value;
    }

    protected static function reencodeParametersArray($queryParameters, $encoding)
    {
        foreach ($queryParameters as &$value) {
            if (is_array($value)) {
                $value = self::reencodeParametersArray($value, $encoding);
            } else {
                $value = PageUrl::reencodeParameterValue($value, $encoding);
            }
        }

        return $queryParameters;
    }

    /**
     * Checks if query parameters are of a non-UTF-8 encoding and converts the values
     * from the specified encoding to UTF-8.
     * This method is used to workaround browser/webapp bugs (see #3450). When
     * browsers fail to encode query parameters in UTF-8, the tracker will send the
     * charset of the page viewed and we can sometimes work around invalid data
     * being stored.
     *
     * @param array $queryParameters Name/value mapping of query parameters.
     * @param bool|string $encoding of the HTML page the URL is for. Used to workaround
     *                                      browser bugs & mis-coded webapps. See #3450.
     *
     * @return array
     */
    public static function reencodeParameters(&$queryParameters, $encoding = false)
    {
        if (function_exists('mb_check_encoding')) {
            // if query params are encoded w/ non-utf8 characters (due to browser bug or whatever),
            // encode to UTF-8.
            if (strtolower($encoding) != 'utf-8'
                && $encoding != false
            ) {
                Common::printDebug("Encoding page URL query parameters to $encoding.");

                $queryParameters = PageUrl::reencodeParametersArray($queryParameters, $encoding);
            }
        } else {
            Common::printDebug("Page charset supplied in tracking request, but mbstring extension is not available.");
        }

        return $queryParameters;
    }

    public static function cleanupUrl($url)
    {
        $url = Common::unsanitizeInputValue($url);
        $url = PageUrl::cleanupString($url);
        $url = PageUrl::convertMatrixUrl($url);

        return $url;
    }

    /**
     * Build the full URL from the prefix ID and the rest.
     *
     * @param string $url
     * @param integer $prefixId
     * @return string
     */
    public static function reconstructNormalizedUrl($url, $prefixId)
    {
        $map = array_flip(self::$urlPrefixMap);

        if ($prefixId !== null && isset($map[$prefixId])) {
            $fullUrl = $map[$prefixId] . $url;
        } else {
            $fullUrl = $url;
        }

        // Clean up host & hash tags, for URLs
        $parsedUrl = @parse_url($fullUrl);
        $parsedUrl = PageUrl::cleanupHostAndHashTag($parsedUrl);
        $url       = UrlHelper::getParseUrlReverse($parsedUrl);

        if (!empty($url)) {
            return $url;
        }

        return $fullUrl;
    }

    /**
     * Extract the prefix from a URL.
     * Return the prefix ID and the rest.
     *
     * @param string $url
     * @return array
     */
    public static function normalizeUrl($url)
    {
        foreach (self::$urlPrefixMap as $prefix => $id) {
            if (strtolower(substr($url, 0, strlen($prefix))) == $prefix) {
                return array(
                    'url'      => substr($url, strlen($prefix)),
                    'prefixId' => $id
                );
            }
        }

        return array('url' => $url, 'prefixId' => null);
    }

    public static function getUrlIfLookValid($url)
    {
        $url = PageUrl::cleanupString($url);

        if (!UrlHelper::isLookLikeUrl($url)) {
            Common::printDebug("WARNING: URL looks invalid and is discarded");

            return false;
        }

        return $url;
    }

    private static function getExcludedParametersFromWebsite($website)
    {
        if (isset($website['excluded_parameters'])) {
            return $website['excluded_parameters'];
        }

        return array();
    }

    public static function urldecodeValidUtf8($value)
    {
        $value = urldecode($value);
        if (function_exists('mb_check_encoding')
            && !@mb_check_encoding($value, 'utf-8')
        ) {
            return urlencode($value);
        }
        return $value;
    }
}
