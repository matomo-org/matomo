<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
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
	if(empty($in))
	{
		return html_entity_decode(Piwik_Translate('General_Unknown'), ENT_COMPAT, 'UTF-8');
	}
	if(strtolower($in) === 'ip')
	{
		return "IP";
	}
	if(($positionDot = strpos($in, '.')) !== false)
	{
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
	if(empty($in)
		|| strtolower($in) === 'ip')
	{
		// link to "what does 'IP' mean?"
		return "http://piwik.org/faq/general/#faq_52";
	}
	return "http://www.".$in."/";
}
