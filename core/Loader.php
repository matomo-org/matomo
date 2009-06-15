<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: index.php 1221 2009-06-15 02:17:33Z vipsoft $
 * 
 * @package Piwik
 */

class Piwik_Loader
{
	public static function autoload($class)
	{
		$class = str_replace('_', '/', $class) . '.php';
		if(substr($class, 0, 6) === 'Piwik/')
		{
			$class = substr($class, 6);
			if(file_exists(PIWIK_INCLUDE_PATH . "/core/" . $class))
			{
				include_once PIWIK_INCLUDE_PATH . "/core/" . $class;
			}
			else
			{
				include_once PIWIK_INCLUDE_PATH . "/plugins/" . $class;
			}
		}
		else
		{
			include_once PIWIK_INCLUDE_PATH . "/libs/" . $class;
		}
	}
}

// Note: only one __autoload per PHP instance
if(function_exists('spl_autoload_register'))
{
	// use the SPL autoload stack
	spl_autoload_register(array('Piwik_Loader', 'autoload'));

	// preserve any existing __autoload
	if(function_exists('__autoload'))
	{
		spl_auto_register('__autoload');
	}
}
else
{
	function __autoload($class)
	{
		Piwik_Loader::autoload($class);
	}
}
