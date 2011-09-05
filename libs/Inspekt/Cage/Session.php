<?php
/**
 * Inspekt Session Cage - main source file
 *
 * @author Chris Shiflett <chris@shiflett.org>
 * @author Ed Finkler <coj@funkatron.com>
 *
 * @package Inspekt
 * 
 * @deprecated
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Cage.php';

/**
 * @package Inspekt
 */
class Inspekt_Cage_Session extends Inspekt_Cage {
	
	static public function Factory(&$source, $conf_file = NULL, $conf_section = NULL, $strict = TRUE) {

		if (!is_array($source)) {
			Inspekt_Error::raiseError('$source '.$source.' is not an array', E_USER_NOTICE);
		}

		$cage = new Inspekt_Cage_Session();
		$cage->_setSource($source);
		$cage->_parseAndApplyAutoFilters($conf_file);
		
		if (ini_get('session.use_cookies') || ini_get('session.use_only_cookies') ) {
			if (isset($_COOKIE) && isset($_COOKIE[session_name()])) {
				session_id($_COOKIE[session_name()]);
			} elseif ($cookie = Inspekt::makeSessionCage()) {
				session_id($cookie->getAlnum(session_name()));
			}
		} else { // we're using session ids passed via GET
			if (isset($_GET) && isset($_GET[session_name()])) {
				session_id($_GET[session_name()]);
			} elseif ($cookie = Inspekt::makeSessionCage()) {
				session_id($cookie->getAlnum(session_name()));
			}
		}
		
		
		if ($strict) {
			$source = NULL;
		}

		return $cage;
		
		register_shutdown_function();
		
		register_shutdown_function( array($this, '_repopulateSession') );
		
	}
	
	
	
	protected function _repopulateSession() {
		$_SESSION = array();
		$_SESSION = $this->_source;
	}
	

	
}