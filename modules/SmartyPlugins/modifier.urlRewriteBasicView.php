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
 * Rewrites the given URL so that it looks like a URL that can be loaded directly.
 * Useful for users who don't handle javascript / ajax, they can still use piwik with these rewritten URLs.
 * 
 * @return string
 */
function smarty_modifier_urlRewriteBasicView($parameters)
{
	// replace module=X by moduleToLoad=X
	// replace action=Y by actionToLoad=Y
	
	$parameters['moduleToLoad'] = $parameters['module'];
	unset($parameters['module']);
	
	if(isset( $parameters['action']))
	{
		$parameters['actionToLoad'] = $parameters['action'];
		unset($parameters['action']);
	}
	else
	{
		$parameters['actionToLoad'] = null;
	}
	$url = Piwik_Url::getCurrentQueryStringWithParametersModified($parameters);
	
	// add module=Home&action=showInContext
	$url = $url . '&amp;module=Home&amp;action=showInContext';
	return htmlspecialchars($url);
}

