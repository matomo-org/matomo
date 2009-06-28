<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */

require_once "Smarty/Smarty.class.php";
class Piwik_Smarty extends Smarty 
{
	function trigger_error($error_msg, $error_type = E_USER_WARNING)
	{
		throw new SmartyException($error_msg);
	}
}

class SmartyException extends Exception {}
