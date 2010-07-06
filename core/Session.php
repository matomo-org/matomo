<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
		// don't use the default: PHPSESSID
		$sessionName = defined('PIWIK_SESSION_NAME') ? PIWIK_SESSION_NAME : 'PIWIK_SESSID';
		@ini_set('session.name', $sessionName);

		// we consider this a misconfiguration (i.e., Piwik doesn't implement user-defined session handler functions)
		if(ini_get('session.save_handler') == 'user')
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
				}
				// else rely on default setting (assuming it is configured to a writeable folder)
			}
		}

		try {
			Zend_Session::start();
		} catch(Exception $e) {
			// This message is not translateable because translations haven't been loaded yet.
			Piwik_ExitWithMessage('Unable to start session.  Check that session.save_path or tmp/sessions is writeable.');
		}
	}
}
