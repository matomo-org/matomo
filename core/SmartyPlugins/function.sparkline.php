<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: function.url.php 168 2008-01-14 05:26:43Z matt $
 * 
 * @package SmartyPlugins
 */

require_once "Visualization/Sparkline.php";

/**
 * @param string $url
 * @return string IMG HTML tag 
 */
function smarty_function_sparkline($params, &$smarty)
{
	$src = $params['src'];
	$width = Piwik_Visualization_Sparkline::getWidth();
	$height = Piwik_Visualization_Sparkline::getHeight();
	return "<img class=\"sparkline\" alt=\"\" src=\"$src\" width=\"$width\" height=\"$height\" />";
}
