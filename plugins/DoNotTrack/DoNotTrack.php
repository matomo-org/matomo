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
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
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

	/**
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	function checkHeader($notification)
	{
		if((isset($_SERVER['HTTP_X_DO_NOT_TRACK']) && $_SERVER['HTTP_X_DO_NOT_TRACK'] === '1')
			|| (isset($_SERVER['HTTP_DNT']) && substr($_SERVER['HTTP_DNT'], 0, 1) === '1'))
		{
			$exclude =& $notification->getNotificationObject();
			$exclude = true;
			printDebug("DoNotTrack found.");

			$trackingCookie = Piwik_Tracker_IgnoreCookie::getTrackingCookie();
			$trackingCookie->delete();

			// this is an optional supplement to the site's tracking status resource at:
			//     /.well-known/dnt
			// per Tracking Preference Expression (draft)
			header('Tk: 1');
		}
	}
}
