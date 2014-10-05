<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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
 * Returns the main url of the social network the given url matches
 *
 * @param string  $url
 *
 * @return string
 */
function getSocialMainUrl($url)
{
    $social  = getSocialNetworkFromDomain($url);
    foreach (Common::getSocialUrls() as $domain => $name) {

        if ($name == $social) {

            return $domain;
        }
    }
    return $url;
}

/**
 * Get's social network name from URL.
 *
 * @param string $url
 * @return string
 */
function getSocialNetworkFromDomain($url)
{
    foreach (Common::getSocialUrls() as $domain => $name) {

        if (preg_match('/(^|[\.\/])'.$domain.'([\.\/]|$)/', $url)) {

            return $name;
        }
    }

    return Piwik::translate('General_Unknown');
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
    foreach (Common::getSocialUrls() as $domain => $name) {

        if (preg_match('/(^|[\.\/])'.$domain.'([\.\/]|$)/', $url) && ($socialName === false || $name == $socialName)) {

            return true;
        }
    }

    return false;
}

/**
 * Return social network logo path by URL
 *
 * @param string $domain
 * @return string path
 * @see plugins/Referrers/images/socials/
 */
function getSocialsLogoFromUrl($domain)
{
    $social = getSocialNetworkFromDomain($domain);
    $socialNetworks = Common::getSocialUrls();

    $filePattern = 'plugins/Referrers/images/socials/%s.png';

    foreach ($socialNetworks as $domainKey => $name) {
        if ($social == $socialNetworks[$domainKey] && file_exists(PIWIK_INCLUDE_PATH . '/' . sprintf($filePattern, $domainKey))) {
            return sprintf($filePattern, $domainKey);
        }
    }

    return sprintf($filePattern, 'xx');
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
 * @see \Piwik\Plugins\Referrers\getSearchEngineUrlFromUrlAndKeyword
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
