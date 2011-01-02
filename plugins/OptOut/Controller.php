<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_OptOut
 */

/**
 * OptOut plugin
 *
 * @package Piwik_OptOut
 */
class Piwik_OptOut_Controller extends Piwik_Controller
{
	/**
	 * Shows the "Track Visits" checkbox.
	 */
	public function index()
	{
		$trackVisits = !Piwik_Tracker_Cookie::isIgnoreCookieFound();

		$nonce = Piwik_Common::getRequestVar('nonce', false);
		if($nonce !== false && Piwik_Nonce::verifyNonce('Piwik_OptOut', $nonce))
		{
			// toggle setting
			Piwik_Tracker_Cookie::setIgnoreCookie();
			$trackVisits = !$trackVisits;
		}

		$view = Piwik_View::factory('index');
		$view->control = Piwik_Common::getRequestVar('control', 'checkbox');
		$view->css = Piwik_Common::getRequestVar('css', false);
		$view->trackVisits = $trackVisits;
		$view->nonce = Piwik_Nonce::getNonce('Piwik_OptOut', 3600);
		echo $view->render();
	}
}
