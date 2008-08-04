<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: modifier.sumtime.php 168 2008-01-14 05:26:43Z matt $
 * 
 * @package SmartyPlugins
 */

/**
 * Read the translation string from the given index (read form the selected language in Piwik).
 * The translations strings are located either in /lang/xx.php or within the plugin lang directory.
 * 
 * Example:
 *  {'General_Unknown'|translate} will be translated as 'Unknown' (see the entry in /lang/en.php)
 * 
 * @return string The translated string
 */
function smarty_modifier_translate($string)
{
	if(func_num_args() <= 1)
	{
		$aValues = array();
	}
	else
	{
		$aValues = func_get_args();
		array_shift($aValues);
	}
	return vsprintf(Piwik_Translate($string), $aValues);
}
 