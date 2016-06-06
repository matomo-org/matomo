<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Singleton;
use Piwik\UrlHelper;

/**
 * Contains methods to access search engine definition data.
 */
class SearchEngine extends Singleton
{
    const OPTION_STORAGE_NAME = 'SearchEngineDefinitions';

    /** @var string location of definition file (relative to PIWIK_INCLUDE_PATH) */
    const DEFINITION_FILE = '/vendor/piwik/searchengine-and-social-list/SearchEngines.yml';

    protected $definitionList = null;

    /**
     * Returns list of search engines by URL
     *
     * @return array  Array of ( URL => array( searchEngineName, keywordParameter, path, charset ) )
     */
    public function getDefinitions()
    {
        $cache   = Cache::getEagerCache();
        $cacheId = 'SearchEngine-' . self::OPTION_STORAGE_NAME;

        if ($cache->contains($cacheId)) {
            $list = $cache->fetch($cacheId);
        } else {
            $list = $this->loadDefinitions();
            $cache->save($cacheId, $list);
        }

        return $list;
    }

    private function loadDefinitions()
    {
        if (empty($this->definitionList)) {
            // Read first from the auto-updated list in database
            $list = Option::get(self::OPTION_STORAGE_NAME);

            if ($list) {
                $this->definitionList = unserialize(base64_decode($list));
            } else {
                // Fallback to reading the bundled list
                $yml                  = file_get_contents(PIWIK_INCLUDE_PATH . self::DEFINITION_FILE);
                $this->definitionList = $this->loadYmlData($yml);
                Option::set(self::OPTION_STORAGE_NAME, base64_encode(serialize($this->definitionList)));
            }
        }

        Piwik::postEvent('Referrer.addSearchEngineUrls', array(&$this->definitionList));

        $this->convertLegacyDefinitions();

        return $this->definitionList;
    }

    /**
     * @deprecated remove in 3.0
     */
    protected function convertLegacyDefinitions()
    {
        foreach ($this->definitionList as $url => $definition) {
            if (!array_key_exists('name', $definition) && isset($definition[0]) && isset($definition[1])) {
                $this->definitionList[$url] = array(
                    'name' => $definition[0],
                    'params' => $definition[1],
                    'backlink' => @$definition[2],
                    'charsets' => @$definition[3]
                );
            }
        }

    }

    /**
     * Parses the given YML string and caches the resulting definitions
     *
     * @param string $yml
     * @return array
     */
    public function loadYmlData($yml)
    {
        $searchEngines = \Spyc::YAMLLoadString($yml);

        $this->definitionList = $this->transformData($searchEngines);

        return $this->definitionList;
    }

    protected function transformData($searchEngines)
    {
        $urlToInfo = array();

        foreach ($searchEngines as $name => $info) {
            if (empty($info) || !is_array($info)) {
                continue;
            }

            foreach ($info as $urlDefinitions) {
                foreach ($urlDefinitions['urls'] as $url) {
                    $searchEngineData = $urlDefinitions;
                    unset($searchEngineData['urls']);
                    $searchEngineData['name'] = $name;
                    $urlToInfo[$url]          = $searchEngineData;
                }
            }
        }

        return $urlToInfo;
    }

    /**
     * Returns list of search engines by name
     *
     * @return array  Array of ( searchEngineName => URL )
     */
    public function getNames()
    {
        $cacheId   = 'SearchEngine.getSearchEngineNames';
        $cache     = Cache::getTransientCache();
        $nameToUrl = $cache->fetch($cacheId);

        if (empty($nameToUrl)) {
            $searchEngines = $this->getDefinitions();

            $nameToUrl = array();
            foreach ($searchEngines as $url => $info) {
                if (!isset($nameToUrl[$info['name']])) {
                    $nameToUrl[$info['name']] = $url;
                }
            }
            $cache->save($cacheId, $nameToUrl);
        }

        return $nameToUrl;
    }

    /**
     * Returns definitions for the given search engine host
     *
     * @param string $host
     * @return array
     */
    public function getDefinitionByHost($host)
    {
        $searchEngines = $this->getDefinitions();

        if (!array_key_exists($host, $searchEngines)) {
            return array();
        }

        return $searchEngines[$host];
    }

    /**
     * Extracts a keyword from a raw not encoded URL.
     * Will only extract keyword if a known search engine has been detected.
     * Returns the keyword:
     * - in UTF8: automatically converted from other charsets when applicable
     * - strtolowered: "QUErY test!" will return "query test!"
     * - trimmed: extra spaces before and after are removed
     *
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
    public function extractInformationFromUrl($referrerUrl)
    {
        $referrerParsed = @parse_url($referrerUrl);
        $referrerHost   = '';
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

        $query = '';
        if (isset($referrerParsed['query'])) {
            $query = $referrerParsed['query'];
        }

        // Google Referrers URLs sometimes have the fragment which contains the keyword
        if (!empty($referrerParsed['fragment'])) {
            $query .= '&' . $referrerParsed['fragment'];
        }

        $referrerHost = $this->getEngineHostFromUrl($referrerHost, $referrerPath, $query);

        if (empty($referrerHost)) {
            return false;
        }

        $definitions = $this->getDefinitionByHost($referrerHost);

        $searchEngineName = $definitions['name'];
        $variableNames    = $definitions['params'];

        $key = null;
        if ($searchEngineName === 'Google Images'
            || ($searchEngineName === 'Google' && strpos($referrerUrl, '/imgres') !== false)
        ) {
            if (strpos($query, '&prev') !== false) {
                $query = urldecode(trim(UrlHelper::getParameterFromQueryString($query, 'prev')));
                $query = str_replace('&', '&amp;', strstr($query, '?'));
            }
            $searchEngineName = 'Google Images';
        } elseif ($searchEngineName === 'Google'
            && (strpos($query, '&as_') !== false || strpos($query, 'as_') === 0)
        ) {
            $keys = array();
            $key  = UrlHelper::getParameterFromQueryString($query, 'as_q');
            if (!empty($key)) {
                array_push($keys, $key);
            }
            $key = UrlHelper::getParameterFromQueryString($query, 'as_oq');
            if (!empty($key)) {
                array_push($keys, str_replace('+', ' OR ', $key));
            }
            $key = UrlHelper::getParameterFromQueryString($query, 'as_epq');
            if (!empty($key)) {
                array_push($keys, "\"$key\"");
            }
            $key = UrlHelper::getParameterFromQueryString($query, 'as_eq');
            if (!empty($key)) {
                array_push($keys, "-$key");
            }
            $key = trim(urldecode(implode(' ', $keys)));
        }

        if ($searchEngineName === 'Google') {
            // top bar menu
            $tbm = UrlHelper::getParameterFromQueryString($query, 'tbm');
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
                    $key = UrlHelper::getParameterFromQueryString($query, $variableName);
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
                            || $searchEngineName == 'Ixquick'
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
        if ($key === null || $key === '') {
            return false;
        }

        if (!empty($key)) {
            if (!empty($definitions['charsets'])) {
                $key = $this->convertCharset($key, $definitions['charsets']);
            }
            $key = Common::mb_strtolower($key);
        }

        return array(
            'name'     => $searchEngineName,
            'keywords' => $key,
        );
    }

    protected function getEngineHostFromUrl($host, $path, $query)
    {
        $searchEngines = $this->getDefinitions();

        $hostPattern = UrlHelper::getLossyUrl($host);
        /*
         * Try to get the best matching 'host' in definitions
         * 1. check if host + path matches an definition
         * 2. check if host only matches
         * 3. check if host pattern + path matches
         * 4. check if host pattern matches
         * 5. special handling
         */
        if (array_key_exists($host . $path, $searchEngines)) {
            $host = $host . $path;
        } elseif (array_key_exists($host, $searchEngines)) {
            // no need to change host
        } elseif (array_key_exists($hostPattern . $path, $searchEngines)) {
            $host = $hostPattern . $path;
        } elseif (array_key_exists($hostPattern, $searchEngines)) {
            $host = $hostPattern;
        } elseif (!array_key_exists($host, $searchEngines)) {
            if (!strncmp($query, 'cx=partner-pub-', 15)) {
                // Google custom search engine
                $host = 'google.com/cse';
            } elseif (!strncmp($path, '/pemonitorhosted/ws/results/', 28)) {
                // private-label search powered by InfoSpace Metasearch
                $host = 'wsdsold.infospace.com';
            } elseif (strpos($host, '.images.search.yahoo.com') != false) {
                // Yahoo! Images
                $host = 'images.search.yahoo.com';
            } elseif (strpos($host, '.search.yahoo.com') != false) {
                // Yahoo!
                $host = 'search.yahoo.com';
            } else {
                return false;
            }
        }

        return $host;
    }

    /**
     * Tries to convert the given string from one of the given charsets to UTF-8
     * @param string $string
     * @param array $charsets
     * @return string
     */
    protected function convertCharset($string, $charsets)
    {
        if (function_exists('iconv')
            && !empty($charsets)
        ) {
            $charset = $charsets[0];
            if (count($charsets) > 1
                && function_exists('mb_detect_encoding')
            ) {
                $charset = mb_detect_encoding($string, $charsets);
                if ($charset === false) {
                    $charset = $charsets[0];
                }
            }

            $newKey = @iconv($charset, 'UTF-8//IGNORE', $string);
            if (!empty($newKey)) {
                $string = $newKey;
            }
        }

        return $string;
    }

    /**
     * Return search engine URL by name
     *
     * @see core/DataFiles/SearchEnginges.php
     *
     * @param string $name
     * @return string URL
     */
    public function getUrlFromName($name)
    {
        $searchEngineNames = $this->getNames();
        if (isset($searchEngineNames[$name])) {
            $url = 'http://' . $searchEngineNames[$name];
        } else {
            $url = 'URL unknown!';
        }
        return $url;
    }

    /**
     * Return search engine host in URL
     *
     * @param string $url
     * @return string host
     */
    private function getHostFromUrl($url)
    {
        if (strpos($url, '//')) {
            $url = substr($url, strpos($url, '//') + 2);
        }
        if (($p = strpos($url, '/')) !== false) {
            $url = substr($url, 0, $p);
        }
        return $url;
    }


    /**
     * Return search engine logo path by URL
     *
     * @param string $url
     * @return string path
     * @see plugins/Referrers/images/searchEnginges/
     */
    public function getLogoFromUrl($url)
    {
        $pathInPiwik  = 'plugins/Referrers/images/searchEngines/%s.png';
        $pathWithCode = sprintf($pathInPiwik, $this->getHostFromUrl($url));
        $absolutePath = PIWIK_INCLUDE_PATH . '/' . $pathWithCode;
        if (file_exists($absolutePath)) {
            return $pathWithCode;
        }
        return sprintf($pathInPiwik, 'xx');
    }

    /**
     * Return search engine URL for URL and keyword
     *
     * @see core/DataFiles/SearchEnginges.php
     *
     * @param string $url Domain name, e.g., search.piwik.org
     * @param string $keyword Keyword, e.g., web+analytics
     * @return string URL, e.g., http://search.piwik.org/q=web+analytics
     */
    public function getBackLinkFromUrlAndKeyword($url, $keyword)
    {
        if ($keyword === API::LABEL_KEYWORD_NOT_DEFINED) {
            return 'http://piwik.org/faq/general/#faq_144';
        }
        $keyword = urlencode($keyword);
        $keyword = str_replace(urlencode('+'), urlencode(' '), $keyword);
        $host    = substr($url, strpos($url, '//') + 2);
        $definition = $this->getDefinitionByHost($host);
        if (empty($definition['backlink'])) {
            return false;
        }
        $path = str_replace("{k}", $keyword, $definition['backlink']);
        return $url . (substr($url, -1) != '/' ? '/' : '') . $path;
    }
}
