<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Provider;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Piwik;
use Zend_Validate_Hostname as HostnameValidator;

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
    
    // if the name is a valid hostname, return a URL - otherwise link to startpage
    $validator = new HostnameValidator;
    if ($validator->isValid($in)) {
        return "http://" . $in . "/";
    } else {
        return "https://startpage.com/do/search?q=" . urlencode(getPrettyProviderName($in));
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
