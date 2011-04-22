<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_AnonymizeIP
 */

/**
 * Anonymize visitor IP addresses to comply with the privacy laws/guidelines in countries, such as Germany.
 *
 * @package Piwik_AnonymizeIP
 */
class Piwik_AnonymizeIP extends Piwik_Plugin
{
	/**
	 * Get plugin information
	 */
	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('AnonymizeIP_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
			'TrackerPlugin' => true,
		);
	}

	/**
	 * Get list of hooks to register
	 */
	public function getListHooksRegistered()
	{
		return array(
			'Tracker.Visit.setVisitorIp' => 'setVisitorIpAddress',
			'Tracker.saveVisitorInformation' => 'anonymizeVisitorIpAddress',
		);
	}

	/**
	 * Internal function to mask portions of the visitor IP address
	 *
	 * @param string $ip IP address in network address format
	 * @param int $maskLength Number of octets to reset
	 */
	static public function applyIPMask($ip, $maskLength)
	{
		// in case mbstring overloads strlen and substr functions
		$strlen = function_exists('mb_orig_strlen') ? 'mb_orig_strlen' : 'strlen';
		$i = $strlen($ip);
		if($maskLength > $i)
		{
			$maskLength = $i;
		}

		while($maskLength-- > 0)
		{
			$ip[--$i] = chr(0);
		}

		return $ip;
	}

	/**
	 * Hook on Tracker.Visit.setVisitorIp to anonymize visitor IP addresses
	 */
	function setVisitorIpAddress($notification)
	{
		$ip =& $notification->getNotificationObject();
		$ip = self::applyIPMask($ip, Piwik_Tracker_Config::getInstance()->Tracker['ip_address_pre_mask_length']);
	}

	/**
	 * Hook on Tracker.saveVisitorInformation to anonymize visitor IP addresses
	 */
	function anonymizeVisitorIpAddress($notification)
	{
		$visitorInfo =& $notification->getNotificationObject();
		$visitorInfo['location_ip'] = self::applyIPMask($visitorInfo['location_ip'], Piwik_Tracker_Config::getInstance()->Tracker['ip_address_mask_length']);
	}
}
