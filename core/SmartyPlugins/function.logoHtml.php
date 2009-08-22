<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package SmartyPlugins
 */

function smarty_function_logoHtml($params, &$smarty)
{
	if(!isset($params['metadata']['logo']))
	{
		return;
	}
	$width = $height = $alt = '';
	if(isset($params['metadata']['logoWidth'])) 
	{
		$width = "width=".$params['metadata']['logoWidth'];
	}
	if(isset($params['metadata']['logoHeight'])) 
	{
		$height = "height=".$params['metadata']['logoHeight'];
	}
	if(isset($params['alt'])) 
	{
		$alt = "title='".$params['alt']."' alt='".$params['alt']."'";
	}
	return " <img $alt $width $height src=".$params['metadata']['logo']." />";
}
