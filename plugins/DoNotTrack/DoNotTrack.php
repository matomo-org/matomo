<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_DoNotTrack
 */

/**
 * Ignore visits where user agent's request contains either:
 * - X-Do-Not-Track header (used by AdBlockPlus and NoScript)
 * - DNT header (used by Mozilla)
 *
 * @package Piwik_DoNotTrack
 */
class Piwik_DoNotTrack extends Piwik_Plugin
{
	/**
	 * Return information about this plugin.
	 *
	 * @see Piwik_Plugin
	 *
	 * @return array
	 */
	public function getInformation()
	{
		return array(
			'description' => 'Ignore visits with X-Do-Not-Track or DNT header',
			'author' => 'VIP Software Technologies Inc.',
			'author_homepage' => 'http://activeanalytics.com/',
			'version' => '0.3',
			'translationAvailable' => false,
			'TrackerPlugin' => true,
		);
	}
	
	public function getListHooksRegistered()
	{
		return array(
			'Tracker.Visit.isExcluded' => 'checkHeader',
		);
	}

	function checkHeader($notification)
	{
		$setting = @Piwik_Tracker_Config::getInstance()->Tracker['do_not_track'];
		if($setting === '1' &&
			((isset($_SERVER['HTTP_X_DO_NOT_TRACK']) && $_SERVER['HTTP_X_DO_NOT_TRACK'] === '1') ||
			(isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] === '1')))
		{
			$exclude =& $notification->getNotificationObject();
			$exclude = true;

			$trackingCookie = Piwik_Tracker_IgnoreCookie::getTrackingCookie();
			$trackingCookie->delete();			
		}
	}
}
