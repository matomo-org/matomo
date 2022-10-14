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
use Piwik\Intl\Data\Provider\RegionDataProvider;

/**
 * Contains less commonly needed URL helper methods.
 *
 */
class UrlHelper
{
    private static $validLinkProtocols = [
        'http',
        'https',
        'tel',
        'sms',
        'mailto',
        'callto',
    ];

    /**
    * Checks if a string matches/is equal to one of the patterns/strings.
    *
    * @static
    * @param $test String to test.
    * @param $patterns Array of strings or regexs.
    *
    * @return true if $test matches or is equal to one of the regex/string in $patterns, false otherwise.
    */
    protected static function in_array_matches_regex($test, $patterns)
    {
        foreach($patterns as $val) {
            if(@preg_match($val, null) === false) {
                if( strcasecmp($val, $test) === 0 ) {
                    return true;
                }
            } else {
                if( preg_match($val, $test) === 1 ) {
                    return true;
                }
            }
        }
        return false;
    }

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

            if (!self::in_array_matches_regex(strtolower($name), $parametersToExclude)) {
                if (is_array($value)) {
                    foreach ($value as $param) {
                        if ($param === false) {
                            $validQuery .= $name . '[]' . $separator;
                        } else {
                            $validQuery .= $name . '[]=' . $param . $separator;
                        }
                    }
                } elseif ($value === false) {
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
            /** @var RegionDataProvider $regionDataProvider */
            $regionDataProvider = StaticContainer::get('Piwik\Intl\Data\Provider\RegionDataProvider');
            $countries = implode('|', array_keys($regionDataProvider->getCountryList(true)));
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
     * @api
     * @param string $url
     * @return bool
     */
    public static function isLookLikeUrl($url)
    {
        return $url && preg_match('~^(([[:alpha:]][[:alnum:]+.-]*)?:)?//(.*)$~D', $url, $matches) !== 0
            && strlen($matches[3]) > 0
            && !preg_match('/^(javascript:|vbscript:|data:)/i', $matches[1])
            ;
    }

    public static function isLookLikeSafeUrl($url)
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $url)) {
            return false;
        }

        if (strpos($url, ':') === false) {
            return true;
        }

        $protocol = explode(':', $url, 2)[0];
        return preg_match('/^(' . implode('|', self::$validLinkProtocols) . ')$/i', $protocol);
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
        if (empty($urlQuery)) {
            return array();
        }

        // TODO: this method should not use a cache. callers should instead have their own cache, configured through DI.
        //       one undesirable side effect of using a cache here, is that this method can now init the StaticContainer, which makes setting
        //       test environment for RequestCommand more complicated.
        $cache    = Cache::getTransientCache();
        $cacheKey = 'arrayFromQuery' . $urlQuery;

        if ($cache->contains($cacheKey)) {
            return $cache->fetch($cacheKey);
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
            } elseif (!empty($name)) {
                $nameToValue[$name] = $value;
            }
        }

        $cache->save($cacheKey, $nameToValue);

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
            if (substr($parsedUrl['path'], 0, 1) == '/') {
                $parsedUrl['path'] = substr($parsedUrl['path'], 1);
            }
            $result .= $parsedUrl['path'];
        }
        if (isset($parsedUrl['query'])) {
            $result .= '?' . $parsedUrl['query'];
        }
        return $result;
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
