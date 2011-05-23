<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Session initialization.
 * 
 * @package Piwik
 * @subpackage Piwik_Session
 */
class Piwik_Session extends Zend_Session
{
	public static function start($options = false)
	{
		if(Piwik_Common::isPhpCliMode() || version_compare(Piwik_GetOption('version_core'), '1.5-b5') < 0)
		{
			return;
		}

		// use cookies to store session id on the client side
		@ini_set('session.use_cookies', '1');

		// prevent attacks involving session ids passed in URLs
		@ini_set('session.use_only_cookies', '1');

		// advise browser that session cookie should only be sent over secure connection
		if(Piwik_Url::getCurrentScheme() === 'https')
		{
			@ini_set('session.cookie_secure', '1');
		}

		// advise browser that session cookie should only be accessible through the HTTP protocol (i.e., not JavaScript)
		@ini_set('session.cookie_httponly', '1');

		// don't use the default: PHPSESSID
		$sessionName = defined('PIWIK_SESSION_NAME') ? PIWIK_SESSION_NAME : 'PIWIK_SESSID';
		@ini_set('session.name', $sessionName);

		// proxies may cause the referer check to fail and
		// incorrectly invalidate the session
		@ini_set('session.referer_check', '');

		// we consider these to be misconfigurations, in that
		// - user  - we can't verify that user-defined session handler functions have been set via session_set_save_handler()
		// - mm    - this handler is not recommended, unsupported, not available for Windows, and has a potential concurrency issue
		// - files - this handler doesn't work well in load-balanced environments and may have a concurrency issue with locked session files
		$currentSaveHandler = ini_get('session.save_handler');
		if(in_array($currentSaveHandler, array('user', 'mm', 'files')))
		{
			$config = array(
				'name' => Piwik_Common::prefixTable('session'),
				'primary' => 'id',
				'modifiedColumn' => 'modified',
				'dataColumn' => 'data',
				'lifetimeColumn' => 'lifetime',
				'db' => Zend_Registry::get('db'),
			);
			self::setSaveHandler(new Zend_Session_SaveHandler_DbTable($config));
		}

		// garbage collection may disabled by default (e.g., Debian)
		if(ini_get('session.gc_probability') == 0)
		{
			@ini_set('session.gc_probability', 1);
		}

		try {
			Zend_Session::start();
		} catch(Exception $e) {
			Piwik::log('Unable to start session: ' . $e->getMessage());
			Piwik_ExitWithMessage(Piwik_Translate('General_ExceptionUnableToStartSession'));
		}
	}
}
