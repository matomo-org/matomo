<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik
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
 *           <code>$smarty->assign('tag', 'some_unique_tag');</code>
 *           <code>$smarty->load_filter('output','cachebuster');</code>
 *           from application.
 * @author   Anthon Pang <apang at softwaredevelopment dot ca>
 * @version  1.1
 * @param string
 * @param Smarty
 */
function smarty_outputfilter_cachebuster($source, &$smarty)
{
	static $cachebuster = null;
	if(is_null($cachebuster))
	{
		$cachebuster = md5(Piwik_Common::getSalt() . PHP_VERSION . $smarty->get_template_vars('piwik_version'));
	}
	$tag = 'cb=' . $cachebuster;

	$pattern = array(
		'~<script type=[\'"]text/javascript[\'"] src=[\'"]([^\'"]+)[\'"]>~',
		'~<script src=[\'"]([^\'"]+)[\'"] type=[\'"]text/javascript[\'"]>~',
		'~<link rel=[\'"]stylesheet[\'"] type=[\'"]text/css[\'"] href=[\'"]([^\'"]+)[\'"] ?/?>~',
		'~(src|href)=\"index.php\?module=([A-Za-z0-9_]+)&action=([A-Za-z0-9_]+)\?cb=~',
	);

	$replace = array(
		'<script type="text/javascript" src="$1?'. $tag .'">',
		'<script type="text/javascript" src="$1?'. $tag .'">',
		'<link rel="stylesheet" type="text/css" href="$1?'. $tag .'" />',
		'$1="index.php?module=$2&action=$3&cb=',
	);

	return preg_replace($pattern, $replace, $source);
}
