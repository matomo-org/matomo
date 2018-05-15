<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;

/**
 * Return hostname portion of a domain name
 *
 * @param string $in
 * @return string Host name, IP (if IP address didn't resolve), or Unknown
 */
function getHostnameName($in)
{
    if (empty($in) || strtolower($in) === 'ip') {
        return Piwik::translate('General_Unknown');
    }
    if (($positionDot = strpos($in, '.')) !== false) {
        return ucfirst(substr($in, 0, $positionDot));
    }
    return $in;
}

/**
 * Return URL for a given domain name
 *
 * @param string $in hostname
 * @return string URL
 */
function getHostnameUrl($in)
{
    if ($in == DataTable::LABEL_SUMMARY_ROW || empty($in) || strtolower($in) === 'ip') {
        return null;
    }

    // if the name looks like it can be used in a URL, use it in one, otherwise link to startpage
    if (preg_match("/^[-a-zA-Z0-9_.]+$/", $in)) {
        return "http://www." . $in . "/";
    } else {
        return "https://startpage.com/do/search?q=" . urlencode($in);
    }
}

/**
 * Return a pretty provider name for a given domain name
 *
 * @param string $in hostname
 * @return string Real ISP name, IP (if IP address didn't resolve), or Unknown
 */
function getPrettyProviderName($in)
{
    $providerName = getHostnameName($in);

    $prettyNames = Common::getProviderNames();

    if (is_array($prettyNames)
        && array_key_exists(strtolower($providerName), $prettyNames)
    ) {
        $providerName = $prettyNames[strtolower($providerName)];
    }

    return $providerName;
}
