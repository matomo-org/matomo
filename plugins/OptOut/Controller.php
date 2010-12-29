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
        $view = Piwik_View::factory('CheckBox');
        $view->trackVisits = !Piwik_Tracker_Cookie::isIgnoreCookieFound();
        $view->nonce = Piwik_Nonce::getNonce('Piwik_OptOut', 3600);
        echo $view->render();
    }

    /**
     * Public interface of the controller to change
     * the status. Checks the nonce for correctness.
     */
    public function changeStatus()
    {
        $trackVisits = Piwik_Common::getRequestVar('trackVisits', false);
        $nonce = Piwik_Common::getRequestVar('nonce');

        if (Piwik_Nonce::verifyNonce('Piwik_OptOut', $nonce)) {
			Piwik_Tracker_Cookie::setIgnoreCookie();
        } else {
            throw new Exception(Piwik_Translate('OptOut_WrongNonce'));
        }
        Piwik::redirectToModule('OptOut', 'index');
    }
}
