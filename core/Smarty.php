<?php
require_once "Smarty/Smarty.class.php";
class Piwik_Smarty extends Smarty 
{
	function trigger_error($error_msg, $error_type = E_USER_WARNING)
	{
		throw new SmartyException($error_msg);
	}
}

class SmartyException extends Exception {}
