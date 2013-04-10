<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Provider
 */

/**
 * Return hostname portion of a domain name
 *
 * @param string $in
 * @return string Host name, IP (if IP address didn't resolve), or Unknown
 */
function Piwik_getHostnameName($in)
{
    if (empty($in)) {
        return Piwik_Translate('General_Unknown');
    }
    if (strtolower($in) === 'ip') {
        return "IP";
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
function Piwik_getHostnameUrl($in)
{
    if ($in == Piwik_DataTable::LABEL_SUMMARY_ROW) {
        return false;
    }
    if (empty($in)
        || strtolower($in) === 'ip'
    ) {
        // link to "what does 'IP' mean?"
        return "http://piwik.org/faq/general/#faq_52";
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
function Piwik_Provider_getPrettyProviderName( $in )
{
    $providerName = Piwik_getHostnameName($in);

    $prettyNames = Piwik_Common::getProviderNames();

    if(is_array($prettyNames)
        && array_key_exists(strtolower($providerName), $prettyNames))
    {
        $providerName = $prettyNames[strtolower($providerName)];
    }

    return $providerName;
}
