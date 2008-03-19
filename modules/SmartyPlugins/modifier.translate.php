<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: modifier.sumtime.php 168 2008-01-14 05:26:43Z matt $
 * 
 * @package Piwik_Visualization
 */

/**
 * Translates a string in the currently selected language in Piwik.
 * The translations strings are located either in /lang/xx.php or within the plugin lang directory.
 * 
 * Example:
 * 
 * {'General_Unknown'|translate} will be translated as 'Unknown' (see the entry in /lang/en.php)
 * 
 */
function smarty_modifier_translate($string, $aValues = null)
{
	if(is_null($aValues))
	{
		$aValues = array();
	}
	if(!is_array($aValues))
	{
		$aValues = array( $aValues);
	}
	return vsprintf(Piwik_Translate($string), $aValues);
}