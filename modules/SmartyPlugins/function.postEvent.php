<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: function.url.php 168 2008-01-14 05:26:43Z matt $
 * 
 * @package Piwik_Visualization
 */

require_once "Url.php";

/**
 */
function smarty_function_postEvent($params, &$smarty)
{
	if(!isset($params['name']))
	{
		throw new Exception("The smarty function postEvent needs a 'name' parameter.");
	}
	$eventName = $params['name'];
	
	$str = '';
	Piwik_PostEvent($eventName, $str);
	return $str;
}
