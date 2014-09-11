<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

/**
 * Contains less commonly needed URL helper methods.
 *
 */
class UrlHelper
{
    /**
     * Converts an array of query parameter name/value mappings into a query string.
     * Parameters that are in `$parametersToExclude` will not appear in the result.
     *
     * @static
     * @param $queryParameters Array of query parameters, eg, `array('site' => '0', 'date' => '2012-01-01')`.
     * @param $parametersToExclude Array of query parameter names that shouldn't be
     *                             in the result query string, eg, `array('date', 'period')`.
     * @return string A query string, eg, `"?site=0"`.
     * @api
     */
    public static function getQueryStringWithExcludedParameters($queryParameters, $parametersToExclude)
    {
        $validQuery = '';
        $separator = '&';
        foreach ($queryParameters as $name => $value) {
            // decode encoded square brackets
            $name = str_replace(array('%5B', '%5D'), array('[', ']'), $name);

            if (!in_array(strtolower($name), $parametersToExclude)) {
                if (is_array($value)) {
                    foreach ($value as $param) {
                        if ($param === false) {
                            $validQuery .= $name . '[]' . $separator;
                        } else {
                            $validQuery .= $name . '[]=' . $param . $separator;
                        }
                    }
                } else if ($value === false) {
                    $validQuery .= $name . $separator;
                } else {
                    $validQuery .= $name . '=' . $value . $separator;
                }
            }
        }
        $validQuery = substr($validQuery, 0, -strlen($separator));
        return $validQuery;
    }

    /**
     * Reduce URL to more minimal form.  2 letter country codes are
     * replaced by '{}', while other parts are simply removed.
     *
     * Examples:
     *   www.example.com -> example.com
     *   search.example.com -> example.com
     *   m.example.com -> example.com
     *   de.example.com -> {}.example.com
     *   example.de -> example.{}
     *   example.co.uk -> example.{}
     *
     * @param string $url
     * @return string
     */
    public static function getLossyUrl($url)
    {
        static $countries;
        if (!isset($countries)) {
            $countries = implode('|', array_keys(Common::getCountriesList(true)));
        }

        return preg_replace(
            array(
                 '/^(w+[0-9]*|search)\./',
                 '/(^|\.)m\./',
                 '/(\.(com|org|net|co|it|edu))?\.(' . $countries . ')(\/|$)/',
                 '/(^|\.)(' . $countries . ')\./',
            ),
            array(
                 '',
                 '$1',
                 '.{}$4',
                 '$1{}.',
            ),
            $url);
    }

    /**
     * Returns true if the string passed may be a URL ie. it starts with protocol://.
     * We don't need a precise test here because the value comes from the website
     * tracked source code and the URLs may look very strange.
     *
     * @param string $url
     * @return bool
     */
    public static function isLookLikeUrl($url)
    {
        return preg_match('~^(ftp|news|http|https)?://(.*)$~D', $url, $matches) !== 0
        && strlen($matches[2]) > 0;
    }

    /**
     * Returns a URL created from the result of the [parse_url](http://php.net/manual/en/function.parse-url.php)
     * function.
     *
     * Copied from the PHP comments at [http://php.net/parse_url](http://php.net/parse_url).
     *
     * @param array $parsed Result of [parse_url](http://php.net/manual/en/function.parse-url.php).
     * @return false|string The URL or `false` if `$parsed` isn't an array.
     * @api
     */
    public static function getParseUrlReverse($parsed)
    {
        if (!is_array($parsed)) {
            return false;
        }

        $uri = !empty($parsed['scheme']) ? $parsed['scheme'] . ':' . (!strcasecmp($parsed['scheme'], 'mailto') ? '' : '//') : '';
        $uri .= !empty($parsed['user']) ? $parsed['user'] . (!empty($parsed['pass']) ? ':' . $parsed['pass'] : '') . '@' : '';
        $uri .= !empty($parsed['host']) ? $parsed['host'] : '';
        $uri .= !empty($parsed['port']) ? ':' . $parsed['port'] : '';

        if (!empty($parsed['path'])) {
            $uri .= (!strncmp($parsed['path'], '/', 1))
                ? $parsed['path']
                : ((!empty($uri) ? '/' : '') . $parsed['path']);
        }

        $uri .= !empty($parsed['query']) ? '?' . $parsed['query'] : '';
        $uri .= !empty($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
        return $uri;
    }

    /**
     * Returns a URL query string as an array.
     *
     * @param string $urlQuery The query string, eg, `'?param1=value1&param2=value2'`.
     * @return array eg, `array('param1' => 'value1', 'param2' => 'value2')`
     * @api
     */
    public static function getArrayFromQueryString($urlQuery)
    {
        if (strlen($urlQuery) == 0) {
            return array();
        }
        if ($urlQuery[0] == '?') {
            $urlQuery = substr($urlQuery, 1);
        }
        $separator = '&';

        $urlQuery = $separator . $urlQuery;
        //		$urlQuery = str_replace(array('%20'), ' ', $urlQuery);
        $referrerQuery = trim($urlQuery);

        $values = explode($separator, $referrerQuery);

        $nameToValue = array();

        foreach ($values as $value) {
            $pos = strpos($value, '=');
            if ($pos !== false) {
                $name = substr($value, 0, $pos);
                $value = substr($value, $pos + 1);
                if ($value === false) {
                    $value = '';
                }
            } else {
                $name = $value;
                $value = false;
            }
            if (!empty($name)) {
                $name = Common::sanitizeInputValue($name);
            }
            if (!empty($value)) {
                $value = Common::sanitizeInputValue($value);
            }

            // if array without indexes
            $count = 0;
            $tmp = preg_replace('/(\[|%5b)(]|%5d)$/i', '', $name, -1, $count);
            if (!empty($tmp) && $count) {
                $name = $tmp;
                if (isset($nameToValue[$name]) == false || is_array($nameToValue[$name]) == false) {
                    $nameToValue[$name] = array();
                }
                array_push($nameToValue[$name], $value);
            } else if (!empty($name)) {
                $nameToValue[$name] = $value;
            }
        }
        return $nameToValue;
    }

    /**
     * Returns the value of a single query parameter from the supplied query string.
     *
     * @param string $urlQuery The query string.
     * @param string $parameter The query parameter name to return.
     * @return string|null Parameter value if found (can be the empty string!), null if not found.
     * @api
     */
    public static function getParameterFromQueryString($urlQuery, $parameter)
    {
        $nameToValue = self::getArrayFromQueryString($urlQuery);
        if (isset($nameToValue[$parameter])) {
            return $nameToValue[$parameter];
        }
        return null;
    }

    /**
     * Returns the path and query string of a URL.
     *
     * @param string $url The URL.
     * @return string eg, `/test/index.php?module=CoreHome` if `$url` is `http://piwik.org/test/index.php?module=CoreHome`.
     * @api
     */
    public static function getPathAndQueryFromUrl($url)
    {
        $parsedUrl = parse_url($url);
        $result = '';
        if (isset($parsedUrl['path'])) {
            $result .= substr($parsedUrl['path'], 1);
        }
        if (isset($parsedUrl['query'])) {
            $result .= '?' . $parsedUrl['query'];
        }
        return $result;
    }

    /**
     * Extracts a keyword from a raw not encoded URL.
     * Will only extract keyword if a known search engine has been detected.
     * Returns the keyword:
     * - in UTF8: automatically converted from other charsets when applicable
     * - strtolowered: "QUErY test!" will return "query test!"
     * - trimmed: extra spaces before and after are removed
     *
     * Lists of supported search engines can be found in /core/DataFiles/SearchEngines.php
     * The function returns false when a keyword couldn't be found.
     *     eg. if the url is "http://www.google.com/partners.html" this will return false,
     *       as the google keyword parameter couldn't be found.
     *
     * @see unit tests in /tests/core/Common.test.php
     * @param string $referrerUrl URL referrer URL, eg. $_SERVER['HTTP_REFERER']
     * @return array|bool   false if a keyword couldn't be extracted,
     *                        or array(
     *                            'name' => 'Google',
     *                            'keywords' => 'my searched keywords')
     */
    public static function extractSearchEngineInformationFromUrl($referrerUrl)
    {
        $referrerParsed = @parse_url($referrerUrl);
        $referrerHost = '';
        if (isset($referrerParsed['host'])) {
            $referrerHost = $referrerParsed['host'];
        }
        if (empty($referrerHost)) {
            return false;
        }
        // some search engines (eg. Bing Images) use the same domain
        // as an existing search engine (eg. Bing), we must also use the url path
        $referrerPath = '';
        if (isset($referrerParsed['path'])) {
            $referrerPath = $referrerParsed['path'];
        }

        // no search query
        if (!isset($referrerParsed['query'])) {
            $referrerParsed['query'] = '';
        }
        $query = $referrerParsed['query'];

        // Google Referrers URLs sometimes have the fragment which contains the keyword
        if (!empty($referrerParsed['fragment'])) {
            $query .= '&' . $referrerParsed['fragment'];
        }

        $searchEngines = Common::getSearchEngineUrls();

        $hostPattern = self::getLossyUrl($referrerHost);
        /*
         * Try to get the best matching 'host' in definitions
         * 1. check if host + path matches an definition
         * 2. check if host only matches
         * 3. check if host pattern + path matches
         * 4. check if host pattern matches
         * 5. special handling
         */
        if (array_key_exists($referrerHost . $referrerPath, $searchEngines)) {
            $referrerHost = $referrerHost . $referrerPath;
        } elseif (array_key_exists($referrerHost, $searchEngines)) {
            // no need to change host
        } elseif (array_key_exists($hostPattern . $referrerPath, $searchEngines)) {
            $referrerHost = $hostPattern . $referrerPath;
        } elseif (array_key_exists($hostPattern, $searchEngines)) {
            $referrerHost = $hostPattern;
        } elseif (!array_key_exists($referrerHost, $searchEngines)) {
            if (!strncmp($query, 'cx=partner-pub-', 15)) {
                // Google custom search engine
                $referrerHost = 'google.com/cse';
            } elseif (!strncmp($referrerPath, '/pemonitorhosted/ws/results/', 28)) {
                // private-label search powered by InfoSpace Metasearch
                $referrerHost = 'wsdsold.infospace.com';
            } elseif (strpos($referrerHost, '.images.search.yahoo.com') != false) {
                // Yahoo! Images
                $referrerHost = 'images.search.yahoo.com';
            } elseif (strpos($referrerHost, '.search.yahoo.com') != false) {
                // Yahoo!
                $referrerHost = 'search.yahoo.com';
            } else {
                return false;
            }
        }
        $searchEngineName = $searchEngines[$referrerHost][0];
        $variableNames = null;
        if (isset($searchEngines[$referrerHost][1])) {
            $variableNames = $searchEngines[$referrerHost][1];
        }
        if (!$variableNames) {
            $searchEngineNames = Common::getSearchEngineNames();
            $url = $searchEngineNames[$searchEngineName];
            $variableNames = $searchEngines[$url][1];
        }
        if (!is_array($variableNames)) {
            $variableNames = array($variableNames);
        }

        $key = null;
        if ($searchEngineName === 'Google Images'
            || ($searchEngineName === 'Google' && strpos($referrerUrl, '/imgres') !== false)
        ) {
            if (strpos($query, '&prev') !== false) {
                $query = urldecode(trim(self::getParameterFromQueryString($query, 'prev')));
                $query = str_replace('&', '&amp;', strstr($query, '?'));
            }
            $searchEngineName = 'Google Images';
        } else if ($searchEngineName === 'Google'
            && (strpos($query, '&as_') !== false || strpos($query, 'as_') === 0)
        ) {
            $keys = array();
            $key = self::getParameterFromQueryString($query, 'as_q');
            if (!empty($key)) {
                array_push($keys, $key);
            }
            $key = self::getParameterFromQueryString($query, 'as_oq');
            if (!empty($key)) {
                array_push($keys, str_replace('+', ' OR ', $key));
            }
            $key = self::getParameterFromQueryString($query, 'as_epq');
            if (!empty($key)) {
                array_push($keys, "\"$key\"");
            }
            $key = self::getParameterFromQueryString($query, 'as_eq');
            if (!empty($key)) {
                array_push($keys, "-$key");
            }
            $key = trim(urldecode(implode(' ', $keys)));
        }

        if ($searchEngineName === 'Google') {
            // top bar menu
            $tbm = self::getParameterFromQueryString($query, 'tbm');
            switch ($tbm) {
                case 'isch':
                    $searchEngineName = 'Google Images';
                    break;
                case 'vid':
                    $searchEngineName = 'Google Video';
                    break;
                case 'shop':
                    $searchEngineName = 'Google Shopping';
                    break;
            }
        }

        if (empty($key)) {
            foreach ($variableNames as $variableName) {
                if ($variableName[0] == '/') {
                    // regular expression match
                    if (preg_match($variableName, $referrerUrl, $matches)) {
                        $key = trim(urldecode($matches[1]));
                        break;
                    }
                } else {
                    // search for keywords now &vname=keyword
                    $key = self::getParameterFromQueryString($query, $variableName);
                    $key = trim(urldecode($key));

                    // Special cases: empty or no keywords
                    if (empty($key)
                        && (
                            // Google search with no keyword
                            ($searchEngineName == 'Google'
                                && (empty($query) && (empty($referrerPath) || $referrerPath == '/') && empty($referrerParsed['fragment']))
                            )

                            // Yahoo search with no keyword
                            || ($searchEngineName == 'Yahoo!'
                                && ($referrerParsed['host'] == 'r.search.yahoo.com')
                            )

                            // empty keyword parameter
                            || strpos($query, sprintf('&%s=', $variableName)) !== false
                            || strpos($query, sprintf('?%s=', $variableName)) !== false

                            // search engines with no keyword
                            || $searchEngineName == 'Google Images'
                            || $searchEngineName == 'DuckDuckGo')
                    ) {
                        $key = false;
                    }
                    if (!empty($key)
                        || $key === false
                    ) {
                        break;
                    }
                }
            }
        }

        // $key === false is the special case "No keyword provided" which is a Search engine match
        if ($key === null
            || $key === ''
        ) {
            return false;
        }

        if (!empty($key)) {
            if (function_exists('iconv')
                && isset($searchEngines[$referrerHost][3])
            ) {
                // accepts string, array, or comma-separated list string in preferred order
                $charsets = $searchEngines[$referrerHost][3];
                if (!is_array($charsets)) {
                    $charsets = explode(',', $charsets);
                }

                if (!empty($charsets)) {
                    $charset = $charsets[0];
                    if (count($charsets) > 1
                        && function_exists('mb_detect_encoding')
                    ) {
                        $charset = mb_detect_encoding($key, $charsets);
                        if ($charset === false) {
                            $charset = $charsets[0];
                        }
                    }

                    $newkey = @iconv($charset, 'UTF-8//IGNORE', $key);
                    if (!empty($newkey)) {
                        $key = $newkey;
                    }
                }
            }

            $key = Common::mb_strtolower($key);
        }

        return array(
            'name'     => $searchEngineName,
            'keywords' => $key,
        );
    }

    /**
     * Returns the query part from any valid url and adds additional parameters to the query part if needed.
     *
     * @param string $url    Any url eg `"http://example.com/piwik/?foo=bar"`
     * @param array $additionalParamsToAdd    If not empty the given parameters will be added to the query.
     *
     * @return string eg. `"foo=bar&foo2=bar2"`
     * @api
     */
    public static function getQueryFromUrl($url, array $additionalParamsToAdd = array())
    {
        $url = @parse_url($url);
        $query = '';

        if (!empty($url['query'])) {
            $query .= $url['query'];
        }

        if (!empty($additionalParamsToAdd)) {
            if (!empty($query)) {
                $query .= '&';
            }

            $query .= Url::getQueryStringFromParameters($additionalParamsToAdd);
        }

        return $query;
    }

    public static function getHostFromUrl($url)
    {
        if (!UrlHelper::isLookLikeUrl($url)) {
            $url = "http://" . $url;
        }
        return parse_url($url, PHP_URL_HOST);
    }
}
