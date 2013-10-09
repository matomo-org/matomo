<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Referrers
 */
namespace Piwik\Plugins\Referrers;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\UrlHelper;

/**
 * Returns path component from a URL
 *
 * @param string $url
 * @return string path
 */
function getPathFromUrl($url)
{
    $path = UrlHelper::getPathAndQueryFromUrl($url);
    if (empty($path)) {
        return 'index';
    }
    return $path;
}

/**
 * Returns the last parts of the domain of a URL.
 *
 * @param string $url e.g. http://www.facebook.com/?sdlfk=lksdfj
 * @return string|false e.g. facebook.com
 */
function cleanSocialUrl($url)
{
    $segment = '[^.:\/]+';
    preg_match('/(?:https?:\/\/)?(?:' . $segment . '\.)?(' . $segment . '(?:\.' . $segment . ')+)/', $url, $matches);
    return isset($matches[1]) ? $matches[1] : false;
}

/**
 * Get's social network name from URL.
 *
 * @param string $url
 * @return string
 */
function getSocialNetworkFromDomain($url)
{
    $domain = cleanSocialUrl($url);

    if (isset($GLOBALS['Piwik_socialUrl'][$domain])) {
        return $GLOBALS['Piwik_socialUrl'][$domain];
    } else {
        return Piwik::translate('General_Unknown');
    }
}

/**
 * Returns true if a URL belongs to a social network, false if otherwise.
 *
 * @param string $url The URL to check.
 * @param string|bool $socialName The social network's name to check for, or false to check
 *                                 for any.
 * @return bool
 */
function isSocialUrl($url, $socialName = false)
{
    $domain = cleanSocialUrl($url);

    if (isset($GLOBALS['Piwik_socialUrl'][$domain])
        && ($socialName === false
            || $GLOBALS['Piwik_socialUrl'][$domain] == $socialName)
    ) {
        return true;
    }

    return false;
}

/* Return social network logo path by URL
 *
 * @param string $url
 * @return string path
 * @see plugins/Referrers/images/socials/
 */
function getSocialsLogoFromUrl($domain)
{
    $domain = cleanSocialUrl($domain);

    if (isset($GLOBALS['Piwik_socialUrl'][$domain])) {
        // image names are by first domain in list, so make sure we use the first if $domain isn't it
        $firstDomain = $domain;
        foreach ($GLOBALS['Piwik_socialUrl'] as $domainKey => $name) {
            if ($name == $GLOBALS['Piwik_socialUrl'][$domain]) {
                $firstDomain = $domainKey;
                break;
            }
        }

        $pathWithCode = 'plugins/Referrers/images/socials/' . $firstDomain . '.png';
        return $pathWithCode;
    } else {
        return 'plugins/Referrers/images/socials/xx.png';
    }
}

/**
 * Return search engine URL by name
 *
 * @see core/DataFiles/SearchEnginges.php
 *
 * @param string $name
 * @return string URL
 */
function getSearchEngineUrlFromName($name)
{
    $searchEngineNames = Common::getSearchEngineNames();
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
function getSearchEngineHostFromUrl($url)
{
    $url = substr($url, strpos($url, '//') + 2);
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
function getSearchEngineLogoFromUrl($url)
{
    $pathInPiwik = 'plugins/Referrers/images/searchEngines/%s.png';
    $pathWithCode = sprintf($pathInPiwik, getSearchEngineHostFromUrl($url));
    $absolutePath = PIWIK_INCLUDE_PATH . '/' . $pathWithCode;
    if (file_exists($absolutePath)) {
        return $pathWithCode;
    }
    return sprintf($pathInPiwik, 'xx');
}

/**
 * Return search engine host and path in URL
 *
 * @param string $url
 * @return string host
 */
function getSearchEngineHostPathFromUrl($url)
{
    $url = substr($url, strpos($url, '//') + 2);
    return $url;
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
function getSearchEngineUrlFromUrlAndKeyword($url, $keyword)
{
    if ($keyword === API::LABEL_KEYWORD_NOT_DEFINED) {
        return 'http://piwik.org/faq/general/#faq_144';
    }
    $searchEngineUrls = Common::getSearchEngineUrls();
    $keyword = urlencode($keyword);
    $keyword = str_replace(urlencode('+'), urlencode(' '), $keyword);
    $path = @$searchEngineUrls[getSearchEngineHostPathFromUrl($url)][2];
    if (empty($path)) {
        return false;
    }
    $path = str_replace("{k}", $keyword, $path);
    return $url . (substr($url, -1) != '/' ? '/' : '') . $path;
}

/**
 * Return search engine URL for keyword and URL
 *
 * @see Piwik_getSearchEngineUrlFromUrlAndKeyword(getSearchEngineUrlFromUrlAndKeyword
 *
 * @param string $keyword Keyword, e.g., web+analytics
 * @param string $url Domain name, e.g., search.piwik.org
 * @return string URL, e.g., http://search.piwik.org/q=web+analytics
 */
function getSearchEngineUrlFromKeywordAndUrl($keyword, $url)
{
    return getSearchEngineUrlFromUrlAndKeyword($url, $keyword);
}

/**
 * Return translated referrer type
 *
 * @param string $label
 * @return string Referrer type
 */
function getReferrerTypeLabel($label)
{
    $indexTranslation = '';
    switch ($label) {
        case Common::REFERRER_TYPE_DIRECT_ENTRY:
            $indexTranslation = 'Referrers_DirectEntry';
            break;
        case Common::REFERRER_TYPE_SEARCH_ENGINE:
            $indexTranslation = 'Referrers_SearchEngines';
            break;
        case Common::REFERRER_TYPE_WEBSITE:
            $indexTranslation = 'Referrers_Websites';
            break;
        case Common::REFERRER_TYPE_CAMPAIGN:
            $indexTranslation = 'Referrers_Campaigns';
            break;
        default:
            // case of newsletter, partners, before Piwik 0.2.25
            $indexTranslation = 'General_Others';
            break;
    }
    return Piwik::translate($indexTranslation);
}

/**
 * Works in both directions
 * @param string $name
 * @throws \Exception
 * @return string
 */
function getReferrerTypeFromShortName($name)
{
    $map = array(
        Common::REFERRER_TYPE_SEARCH_ENGINE => 'search',
        Common::REFERRER_TYPE_WEBSITE       => 'website',
        Common::REFERRER_TYPE_DIRECT_ENTRY  => 'direct',
        Common::REFERRER_TYPE_CAMPAIGN      => 'campaign',
    );
    if (isset($map[$name])) {
        return $map[$name];
    }
    if ($found = array_search($name, $map)) {
        return $found;
    }
    throw new \Exception("Referrer type '$name' is not valid.");
}

/**
 * Returns a URL w/o the protocol type.
 *
 * @param string $url
 * @return string
 */
function removeUrlProtocol($url)
{
    if (preg_match('/^[a-zA-Z_-]+:\/\//', $url, $matches)) {
        return substr($url, strlen($matches[0]));
    }
    return $url;
}
