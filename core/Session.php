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
 */
class Piwik_Session extends Zend_Session
{
	public static function start($options = false)
	{
		if(Piwik_Common::isPhpCliMode())
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

		// we consider these to be misconfigurations, in that
		//  - user - Piwik doesn't implement user-defined session handler functions
		// -  mm - is not recommended, not supported, not available for Windows, and has a potential concurrency issue
		$currentSaveHandler = ini_get('session.save_handler');
		if($currentSaveHandler == 'user'
			|| $currentSaveHandler == 'mm')
		{
			@ini_set('session.save_handler', 'files');
			@ini_set('session.save_path', '');
		}

		// for "files", we want a writeable folder;
		// for shared hosting, we assume the web server has been securely configured to prevent local session file hijacking
		if(ini_get('session.save_handler') == 'files')
		{
			$sessionPath = ini_get('session.save_path');
			if(preg_match('/^[0-9]+;(.*)/', $sessionPath, $matches))
			{
				$sessionPath = $matches[1];
			}
			if(ini_get('safe_mode') || ini_get('open_basedir') || empty($sessionPath) || !@is_readable($sessionPath) || !@is_writable($sessionPath))
			{
				$sessionPath = PIWIK_USER_PATH . '/tmp/sessions';
				$ok = true;

				if(!is_dir($sessionPath))
				{
					@mkdir($sessionPath, 0755, true);
					if(!is_dir($sessionPath))
					{
						// Unable to mkdir $sessionPath
						$ok = false;
					}
				}
				else if(!@is_writable($sessionPath))
				{
					// $sessionPath is not writable
					$ok = false;
				}

				if($ok)
				{
					@ini_set('session.save_path', $sessionPath);

					// garbage collection may disabled by default (e.g., Debian)
					if(ini_get('session.gc_probability') == 0) {
						@ini_set('session.gc_probability', 1);
					}
				}
				// else rely on default setting (assuming it is configured to a writeable folder)
			}
		}

		try {
			Zend_Session::start();
		} catch(Exception $e) {
			// This message is not translateable because translations haven't been loaded yet.
			Piwik_ExitWithMessage('Unable to start session.  Check that session.save_path or tmp/sessions is writeable, and session.auto_start = 0.');
		}
	}
}
