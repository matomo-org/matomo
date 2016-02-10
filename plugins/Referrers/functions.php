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
 * Return translated referrer type
 *
 * @param string $label
 * @return string Referrer type
 */
function getReferrerTypeLabel($label)
{
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
