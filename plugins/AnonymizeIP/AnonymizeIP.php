<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
			'name' => 'AnonymizeIP',
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
			'Tracker.saveVisitorInformation' => 'anonymizeVisitorIpAddress',
		);
	}

	/**
	 * Internal function to mask portions of the visitor IP address
	 *
	 * @param $ip Unsigned long representation of IP address
	 * @param $maskLength Number of octets to reset
	 */
	static public function applyIPMask($ip, $maskLength)
	{
		$maskedIP = pack('V', (float)$ip);

		switch($maskLength) {
			case 4:
				$maskedIP[3] = "\0";
			case 3:
				$maskedIP[2] = "\0";
			case 2:
				$maskedIP[1] = "\0";
			case 1:
				$maskedIP[0] = "\0";
			case 0:
			default:
		}

		$res = unpack('V', $maskedIP);
		return sprintf("%u", $res[1]);
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
