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

/**
 * Smarty AJAX Libraries CDN outputfilter plugin
 *
 * File:     outputfilter.ajaxcdn.php<br>
 * Type:     outputfilter<br>
 * Name:     ajaxcdn<br>
 * Date:     Oct 13, 2009<br>
 * Purpose:  use AJAX Libraries Content Distribution Network<br>
 * Install:  Drop into the plugin directory, call
 *           <code>$smarty->load_filter('output','ajaxcdn');</code>
 *           from application.
 * @author   Anthon Pang <apang at softwaredevelopment dot ca>
 * @version  1.0
 * @param string
 * @param Smarty
 */
function smarty_outputfilter_ajaxcdn($source, &$smarty)
{
	$use_ajax_cdn = Zend_Registry::get('config')->General->use_ajax_cdn;
	if(!$use_ajax_cdn)
	{
		return $source;
	}

	$jquery_version = Zend_Registry::get('config')->General->jquery_version;
	$jqueryui_version = Zend_Registry::get('config')->General->jqueryui_version;
	$swfobject_version = Zend_Registry::get('config')->General->swfobject_version;

	$pattern = array(
		'~<script type="text/javascript" src="libs/jquery/jquery\.js([^"]*)">~',
		'~<script type="text/javascript" src="libs/jquery/jquery-ui\.js([^"]*)">~',
		'~<script type="text/javascript" src="libs/swfobject/swfobject\.js([^"]*)">~',
	);

	$replace = array(
		'<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/'.$jquery_version.'/jquery.min.js">',
		'<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/'.$jqueryui_version.'/jquery-ui.min.js">',
		'<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/'.$swfobject_version.'/swfobject.js">',
	);

	return preg_replace($pattern, $replace, $source);
}
