<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package SmartyPlugins
 */

/**
 * Smarty cachebuster outputfilter plugin
 *
 * File:     outputfilter.cachebuster.php<br>
 * Type:     outputfilter<br>
 * Name:     cachebuster<br>
 * Date:     May 27, 2009<br>
 * Purpose:  add cache busting string to URLs
 *           of external CSS stylesheets and
 *           JavaScript scripts<br>
 * Install:  Drop into the plugin directory, call
 *           <code>$smarty->assign('tag','XXXX');</code>
 *           <code>$smarty->load_filter('output','cachebuster');</code>
 *           from application.
 * @author   Anthon Pang <apang at softwaredevelopment dot ca>
 * @version  1.0
 * @param string
 * @param Smarty
 */
function smarty_outputfilter_cachebuster($source, &$smarty)
{
	$tag = $smarty->get_template_vars('tag');

	$pattern = array(
		'~<script type="text/javascript" src="([^"]+)">~',
		'~<script src="([^"]+)" type="text/javascript">~',
		'~<link rel="stylesheet" type="text/css" href="([^"]+)" ?/?>~',
	);

	$replace = array(
		'<script type="text/javascript" src="$1?'. $tag .'">',
		'<script src="$1?'. $tag .'" type="text/javascript">',
		'<link rel="stylesheet" type="text/css" href="$1?'. $tag .'" />',
	);

	return preg_replace($pattern, $replace, $source);
}
